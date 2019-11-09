<?php

// Theses classes were adapted from qqUploader

    /**
     * Class AjaxUploader
     */
class AjaxUploader
{
    private $_allowedExtensions;
    private $_sizeLimit;
    private $_file;

    /**
     * AjaxUploader constructor.
     *
     * @param array|null $allowedExtensions
     * @param null       $sizeLimit
     */
    public function __construct(array $allowedExtensions = null, $sizeLimit = null)
    {
        if ($allowedExtensions === null) {
            $allowedExtensions = osc_allowed_extension();
        }
        if ($sizeLimit === null) {
            $sizeLimit = 1024 * osc_max_size_kb();
        }
        $this->_allowedExtensions = $allowedExtensions;
        $this->_sizeLimit         = $sizeLimit;

        if (!Params::existServerParam('CONTENT_TYPE')) {
            $this->_file = false;
        } elseif (strpos(strtolower(Params::getServerParam('CONTENT_TYPE')), 'multipart/') === 0) {
            $this->_file = new AjaxUploadedFileForm();
        } else {
            $this->_file = new AjaxUploadedFileXhr();
        }
    }

    /**
     * @return mixed
     */
    public function getOriginalName()
    {
        return $this->_file->getOriginalName();
    }

    /**
     * @param      $uploadFilename
     * @param bool $replace
     *
     * @return array
     * @throws \Exception
     */
    public function handleUpload($uploadFilename, $replace = false)
    {
        if (!is_writable(dirname($uploadFilename))) {
            return array('error' => __("Server error. Upload directory isn't writable."));
        }
        if (!$this->_file) {
            return array('error' => __('No files were uploaded.'));
        }
        $size = $this->_file->getSize();
        if ($size == 0) {
            return array('error' => __('File is empty'));
        }
        if ($size > $this->_sizeLimit) {
            return array('error' => __('File is too large'));
        }

        $pathinfo = pathinfo($this->_file->getOriginalName());
        $ext      = @$pathinfo['extension'];
        $uuid     = pathinfo($uploadFilename);

        if ($this->_allowedExtensions && stripos($this->_allowedExtensions, strtolower($ext)) === false) {
            @unlink($uploadFilename); // Wrong extension, remove it for security reasons

            return array('error' => sprintf(__('File has an invalid extension, it should be one of %s.'), $this->_allowedExtensions));
        }

        if (!$replace && file_exists($uploadFilename)) {
            return array('error' => 'Could not save uploaded file. File already exists');
        }

        if ($this->_file->save($uploadFilename)) {
            $result = $this->checkAllowedExt($uploadFilename);
            if (!$result) {
                @unlink($uploadFilename); // Wrong extension, remove it for security reasons

                return array('error' => sprintf(__('File has an invalid extension, it should be one of %s.'), $this->_allowedExtensions));
            }
            $files = Session::newInstance()->_get('ajax_files');
            if (!is_array($files)) {
                $files = array();
            }
            $files[Params::getParam('qquuid')] = $uuid['basename'];
            Session::newInstance()->_set('ajax_files', $files);

            return array('success' => true);
        }

        return array('error' => 'Could not save uploaded file. The upload was cancelled, or server error encountered');
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public function checkAllowedExt($file)
    {
        require LIB_PATH . 'osclass/mimes.php';
        if ($file != '') {
            $aMimesAllowed = array();
            $aExt          = explode(',', osc_allowed_extension());
            foreach ($aExt as $ext) {
                if (isset($mimes[$ext])) {
                    $mime = $mimes[$ext];
                    if (is_array($mime)) {
                        foreach ($mime as $aux) {
                            if (!in_array($aux, $aMimesAllowed, false)) {
                                $aMimesAllowed[] = $aux;
                            }
                        }
                    } elseif (!in_array($mime, $aMimesAllowed, false)) {
                        $aMimesAllowed[] = $mime;
                    }
                }
            }

            if (function_exists('finfo_file') && function_exists('finfo_open')) {
                $finfo    = finfo_open(FILEINFO_MIME_TYPE);
                $fileMime = finfo_file($finfo, $file);
            } elseif (function_exists('mime_content_type')) {
                $fileMime = mime_content_type($file);
            } else {
                // *WARNING* There's no way check the mime type of the file, you should not blindly trust on your users' input!
                $ftmp     = Params::getFiles('qqfile');
                $fileMime = @$ftmp['type'];
            }

            if (stripos($fileMime, 'image/') !== false) {
                if (function_exists('getimagesize')) {
                    $info = getimagesize($file);
                    if (isset($info['mime'])) {
                        $fileMime = $info['mime'];
                    } else {
                        $fileMime = '';
                    }
                };
            };

            if (in_array($fileMime, $aMimesAllowed, false)) {
                return true;
            }
        }

        return false;
    }
}

    /**
     * Class AjaxUploadedFileXhr
     */
class AjaxUploadedFileXhr
{
    public function __construct()
    {
    }

    /**
     * @param $path
     *
     * @return bool
     * @throws \Exception
     */
    public function save($path)
    {
        $input    = fopen('php://input', 'rb');
        $temp     = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        if ($realSize !== $this->getSize()) {
            return false;
        }
        $target = fopen($path, 'wb');
        fseek($temp, 0);
        stream_copy_to_stream($temp, $target);
        fclose($target);

        return true;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getSize()
    {
        if (Params::existServerParam('CONTENT_LENGTH')) {
            return (int)Params::getServerParam('CONTENT_LENGTH');
        }

        throw new RuntimeException(__('Getting content length is not supported.'));
    }

    /**
     * @return mixed
     */
    public function getOriginalName()
    {
        return Params::getParam('qqfile');
    }
}

    /**
     * Class AjaxUploadedFileForm
     */
class AjaxUploadedFileForm
{
    private $_file;

    public function __construct()
    {
        $this->_file = Params::getFiles('qqfile');
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function save($path)
    {
        return move_uploaded_file($this->_file['tmp_name'], $path);
    }

    /**
     * @return mixed
     */
    public function getOriginalName()
    {
        return $this->_file['name'];
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->_file['size'];
    }
}
