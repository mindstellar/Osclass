<?php

    /*
 * Copyright 2014 Osclass
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

    use ReCaptcha\ReCaptcha;

    /**
     * check if the item is expired
     *
     * @param $dt_expiration
     *
     * @return bool
     */
function osc_isExpired($dt_expiration)
{
    $now = date('YmdHis');

    $dt_expiration = str_replace(array(' ', '-', ':'), '', $dt_expiration);

    return !($dt_expiration > $now);
}


    /**
     * Remove resources from disk
     *
     * @param int|array $id
     * @param boolean   $admin
     *
     * @return bool|void
     */
function osc_deleteResource($id, $admin)
{
    if (defined('DEMO')) {
        return false;
    }
    if (is_array($id)) {
        $id = $id[0];
    }
    $resource = ItemResource::newInstance()->findByPrimaryKey($id);
    if ($resource !== null) {
        Log::newInstance()->insertLog(
            'item',
            'delete resource',
            $resource['pk_i_id'],
            $id,
            $admin ? 'admin' : 'user',
            $admin ? osc_logged_admin_id() : osc_logged_user_id()
        );

        $backtracel = '';
        foreach (debug_backtrace() as $k => $v) {
            if ($v['function'] === 'include' || $v['function'] === 'include_once' || $v['function'] === 'require_once' || $v['function'] === 'require') {
                $backtracel .= '#' . $k . ' ' . $v['function'] . '(' . $v['args'][0] . ') called@ [' . $v['file'] . ':' . $v['line'] . '] / ';
            } else {
                $backtracel .= '#' . $k . ' ' . $v['function'] . ' called@ [' . $v['file'] . ':' . $v['line'] . '] / ';
            }
        }

        Log::newInstance()->insertLog(
            'item',
            'delete resource backtrace',
            $resource['pk_i_id'],
            $backtracel,
            $admin ? 'admin' : 'user',
            $admin ? osc_logged_admin_id() : osc_logged_user_id()
        );

        @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '.' . $resource['s_extension']);
        @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '_original.' . $resource['s_extension']);
        @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '_thumbnail.' . $resource['s_extension']);
        @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '_preview.' . $resource['s_extension']);
        osc_run_hook('delete_resource', $resource);
    }
}


    /**
     * Tries to delete the directory recursively.
     *
     * @param $path
     *
     * @return true on success.
     */
function osc_deleteDir($path)
{
    if (strpos($path, '../') !== false || strpos($path, "..\\") !== false) {
        return false;
    }

    if (!is_dir($path)) {
        return false;
    }

    $fd = @opendir($path);
    if (!$fd) {
        return false;
    }

    while ($file = @readdir($fd)) {
        if ($file !== '.' && $file !== '..') {
            if (!is_dir($path . '/' . $file)) {
                @chmod($path . '/' . $file, 0755);
                if (!@unlink($path . '/' . $file)) {
                    closedir($fd);

                    return false;
                }

                osc_deleteDir($path . '/' . $file);
            } else {
                osc_deleteDir($path . '/' . $file);
            }
        }
    }
    closedir($fd);

    return @rmdir($path);
}


    /**
     * Unpack a ZIP file into the specific path in the second parameter.
     *
     * @DEPRECATED : TO BE REMOVED IN 3.3
     *
     * @param $zipPath
     * @param $path
     *
     * @return true on success.
     */
function osc_packageExtract($zipPath, $path)
{
    if (strpos($path, '../') !== false || strpos($path, "..\\") !== false) {
        return false;
    }

    if (!file_exists($path)) {
        if (!mkdir($path, 0666) && !is_dir($path)) {
            return false;
        }
    }

    @chmod($path, 0755);

    $zip = new ZipArchive;
    if ($zip->open($zipPath) === true) {
        $zip->extractTo($path);
        $zip->close();

        return true;
    }

    return false;
}


    /**
     * Fix the problem of symbolics links in the path of the file
     *
     * @param string $file The filename of plugin.
     *
     * @return string The fixed path of a plugin.
     */
function osc_plugin_path($file)
{
    // Sanitize windows paths and duplicated slashes
    $file        = preg_replace('|/+|', '/', str_replace('\\', '/', $file));
    $plugin_path = preg_replace('|/+|', '/', str_replace('\\', '/', osc_plugins_path()));
    $file        = $plugin_path . preg_replace('#^.*oc-content\/plugins\/#', '', $file);

    return $file;
}


    /**
     * Fix the problem of symbolics links in the path of the file
     *
     * @param string $file The filename of plugin.
     *
     * @return string The fixed path of a plugin.
     */
function osc_plugin_url($file)
{
    // Sanitize windows paths and duplicated slashes
    $dir = preg_replace('|/+|', '/', str_replace('\\', '/', dirname($file)));
    $dir = osc_base_url() . 'oc-content/plugins/' . preg_replace('#^.*oc-content\/plugins\/#', '', $dir) . '/';

    return $dir;
}


    /**
     * Fix the problem of symbolics links in the path of the file
     *
     * @param string $file The filename of plugin.
     *
     * @return string The fixed path of a plugin.
     */
function osc_plugin_folder($file)
{
    // Sanitize windows paths and duplicated slashes
    $dir = preg_replace('|/+|', '/', str_replace('\\', '/', dirname($file)));
    $dir = preg_replace('#^.*oc-content\/plugins\/#', '', $dir) . '/';

    return $dir;
}


    /**
     * Serialize the data (usefull at plugins activation)
     *
     * @param $data
     *
     * @return string the data serialized
     */
function osc_serialize($data)
{
    if (!is_serialized($data)) {
        if (is_array($data) || is_object($data)) {
            return serialize($data);
        }
    }

    return $data;
}


    /**
     * Unserialize the data (usefull at plugins activation)
     *
     * @param $data
     *
     * @return mixed data unserialized
     */
function osc_unserialize($data)
{
    if (is_serialized($data)) { // don't attempt to unserialize data that wasn't serialized going in
        return @unserialize($data);
    }

    return $data;
}


    /**
     * Checks is $data is serialized or not
     *
     * @param $data
     *
     * @return bool False if not serialized and true if it was.
     */
function is_serialized($data)
{
    // if it isn't a string, it isn't serialized
    if (!is_string($data)) {
        return false;
    }
    $data = trim($data);
    if ('N;' === $data) {
        return true;
    }
    if (!preg_match('/^([adObis]):/', $data, $badions)) {
        return false;
    }
    switch ($badions[1]) {
        case 'a':
        case 'O':
        case 's':
            if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
                return true;
            }
            break;
        case 'b':
        case 'i':
        case 'd':
            if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)) {
                return true;
            }
            break;
    }

    return false;
}


    /**
     * VERY BASIC
     * Perform a POST request, so we could launch fake-cron calls and other core-system calls without annoying the user
     *
     * @param $url   string
     * @param $_data array
     *
     * @return bool false on error or number of bytes sent.
     */
function osc_doRequest($url, $_data)
{
    if (ini_get('allow_url_fopen') === false) {
        error_log('enable allow_url_fopen in php.ini' . PHP_EOL);

        return false;
    }
    // parse the given URL
    $url = parse_url($url);

    if (!isset($url['host'], $url['path']) || $url === false) {
        return false;
    }
    // extract host, path, port:
    $host = $url['host'];
    $path = $url['path'];
    $port = 80;
    if (isset($url['port'])) {
        $port = $url['port'];
    }

    if (isset($url['scheme']) && $url['scheme'] === 'https') {
        $host = 'ssl://' . $host;
        $port = 443;
    }
    $fp = fsockopen($host, $port);

    if ($fp === false) {
        return false;
    }
    $data              = http_build_query($_data);
    $out               = 'POST ' . $path . ' HTTP/1.1' . PHP_EOL;
    $out               .= 'Host: ' . $url['host'] . PHP_EOL;
    $out               .= 'Referer: Osclass ' . osc_version() . PHP_EOL;
    $out               .= 'Content-type: application/x-www-form-urlencoded' . PHP_EOL;
    $out               .= 'Content-Length: ' . strlen($data) . PHP_EOL;
    $out               .= 'Connection: close' . PHP_EOL . PHP_EOL;
    $out               .= $data;
    $number_bytes_sent = fwrite($fp, $out);
    fclose($fp);

    return $number_bytes_sent; // or false on fwrite() error
}


    /**
     * @param $params
     *
     * @return bool
     */
function osc_sendMail($params)
{
    // DO NOT send mail if it's a demo
    if (defined('DEMO')) {
        return false;
    }

    $mail = new PHPMailer(true);
    $mail->clearAddresses();
    $mail->clearAllRecipients();
    $mail->clearAttachments();
    $mail->clearBCCs();
    $mail->clearCCs();
    $mail->clearCustomHeaders();
    $mail->clearReplyTos();

    /** @var \PHPMailer $mail */
    $mail = osc_apply_filter('init_send_mail', $mail, $params);

    if (osc_mailserver_pop()) {
        $pop = new POP3();

        $pop3_host = osc_mailserver_host();
        if (array_key_exists('host', $params)) {
            $pop3_host = $params['host'];
        }

        $pop3_port = osc_mailserver_port();
        if (array_key_exists('port', $params)) {
            $pop3_port = $params['port'];
        }

        $pop3_username = osc_mailserver_username();
        if (array_key_exists('username', $params)) {
            $pop3_username = $params['username'];
        }

        $pop3_password = osc_mailserver_password();
        if (array_key_exists('password', $params)) {
            $pop3_password = $params['password'];
        }

        $pop->authorise($pop3_host, $pop3_port, 30, $pop3_username, $pop3_password);
    }

    if (osc_mailserver_auth()) {
        $mail->isSMTP();
        $mail->SMTPAuth = true;
    } elseif (osc_mailserver_pop()) {
        $mail->isSMTP();
    }

    $smtpSecure = osc_mailserver_ssl();
    if (array_key_exists('password', $params)) {
        $smtpSecure = $params['ssl'];
    }
    if ($smtpSecure != '') {
        $mail->SMTPSecure = $smtpSecure;
    }

    $stmpUsername = osc_mailserver_username();
    if (array_key_exists('username', $params)) {
        $stmpUsername = $params['username'];
    }
    if ($stmpUsername != '') {
        $mail->Username = $stmpUsername;
    }

    $smtpPassword = osc_mailserver_password();
    if (array_key_exists('password', $params)) {
        $smtpPassword = $params['password'];
    }
    if ($smtpPassword != '') {
        $mail->Password = $smtpPassword;
    }

    $smtpHost = osc_mailserver_host();
    if (array_key_exists('host', $params)) {
        $smtpHost = $params['host'];
    }
    if ($smtpHost != '') {
        $mail->Host = $smtpHost;
    }

    $smtpPort = osc_mailserver_port();
    if (array_key_exists('port', $params)) {
        $smtpPort = $params['port'];
    }
    if ($smtpPort != '') {
        $mail->Port = $smtpPort;
    }

    $from = osc_mailserver_mail_from();
    if (empty($from)) {
        $from = 'osclass@' . osc_get_domain();
        if (array_key_exists('from', $params)) {
            $from = $params['from'];
        }
    }

    $from_name = osc_mailserver_name_from();
    if (empty($from_name)) {
        $from_name = osc_page_title();
        if (array_key_exists('from_name', $params)) {
            $from_name = $params['from_name'];
        }
    }

    $mail->From     = osc_apply_filter('mail_from', $from, $params);
    $mail->FromName = osc_apply_filter('mail_from_name', $from_name, $params);

    $to      = $params['to'];
    $to_name = '';
    if (array_key_exists('to_name', $params)) {
        $to_name = $params['to_name'];
    }

    if (!is_array($to)) {
        $to = array($to => $to_name);
    }

    foreach ($to as $to_email => $to_name) {
        $mail->addAddress($to_email, $to_name);
    }

    if (array_key_exists('add_bcc', $params)) {
        if (!is_array($params['add_bcc']) && $params['add_bcc'] != '') {
            $params['add_bcc'] = array($params['add_bcc']);
        }

        foreach ($params['add_bcc'] as $bcc) {
            $mail->addBCC($bcc);
        }
    }

    if (array_key_exists('reply_to', $params)) {
        $mail->addReplyTo($params['reply_to']);
    }

    $mail->Subject = $params['subject'];
    $mail->Body    = $params['body'];

    if (array_key_exists('attachment', $params)) {
        if (!is_array($params['attachment']) || isset($params['attachment']['path'])) {
            $params['attachment'] = array($params['attachment']);
        }

        foreach ($params['attachment'] as $attachment) {
            if (is_array($attachment)) {
                if (isset($attachment['path']) && isset($attachment['name'])) {
                    try {
                        $mail->addAttachment($attachment['path'], $attachment['name']);
                    } catch (phpmailerException $e) {
                        continue;
                    }
                }
            } else {
                try {
                    $mail->addAttachment($attachment);
                } catch (phpmailerException $e) {
                    continue;
                }
            }
        }
    }

    $mail->CharSet = 'utf-8';
    $mail->isHTML();

    $mail = osc_apply_filter('pre_send_mail', $mail, $params);

    // send email!
    try {
        $mail->send();
    } catch (phpmailerException $e) {
        return false;
    }

    return true;
}


    /**
     * @param $text
     * @param $params
     *
     * @return mixed
     */
function osc_mailBeauty($text, $params)
{
    $text   = str_ireplace($params[0], $params[1], $text);
    $kwords = array(
        '{WEB_URL}',
        '{WEB_TITLE}',
        '{WEB_LINK}',
        '{CURRENT_DATE}',
        '{HOUR}',
        '{IP_ADDRESS}'
    );
    $rwords = array(
        osc_base_url(),
        osc_page_title(),
        '<a href="' . osc_base_url() . '">' . osc_page_title() . '</a>',
        date(osc_date_format() ?: 'Y-m-d') . ' ' . date(osc_time_format() ?: 'H:i:s'),
        date(osc_time_format() ?: 'H:i'),
        Params::getServerParam('REMOTE_ADDR')
    );
    $text   = str_ireplace($kwords, $rwords, $text);

    return $text;
}


    /**
     * @param      $dir
     * @param int  $mode
     * @param bool $recursive
     *
     * @return bool
     */
function osc_mkdir($dir, $mode = 0755, $recursive = true)
{
    if ($dir === null || $dir === '') {
        return false;
    }
    if (is_dir($dir) || $dir === '/') {
        return true;
    }
    if (osc_mkdir(dirname($dir), $mode, $recursive)) {
        return mkdir($dir, $mode);
    }

    return false;
}


    /**
     * @param       $source
     * @param       $dest
     * @param array $options
     *
     * @return bool
     */
function osc_copy($source, $dest, $options = array('folderPermission' => 0755, 'filePermission' => 0755))
{
    $result = true;
    if (is_file($source)) {
        if ($dest[strlen($dest) - 1] === '/') {
            if (!file_exists($dest)) {
                osc_mkdir($dest, $options['folderPermission']);
            }
            $__dest = $dest . '/' . basename($source);
        } else {
            $__dest = $dest;
        }
        if (function_exists('copy')) {
            $result = @copy($source, $__dest);
        } else {
            $result = osc_copyemz($source, $__dest);
        }
        @chmod($__dest, $options['filePermission']);
    } elseif (is_dir($source)) {
        if ($dest[strlen($dest) - 1] === '/') {
            if ($source[strlen($source) - 1] === '/') {
                //Copy only contents
            } else {
                //Change parent itself and its contents
                $dest = $dest . basename($source);
                if (!mkdir($dest) && !is_dir($dest)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $dest));
                }
                @chmod($dest, $options['filePermission']);
            }
        } elseif ($source[strlen($source) - 1] === '/') {
            //Copy parent directory with new name and all its content
            if (!mkdir($dest, $options['folderPermission']) && !is_dir($dest)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $dest));
            }
            @chmod($dest, $options['filePermission']);
        } else {
            //Copy parent directory with new name and all its content
            if (!mkdir($dest, $options['folderPermission']) && !is_dir($dest)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $dest));
            }
            @chmod($dest, $options['filePermission']);
        }

        $dirHandle = opendir($source);
        $result    = true;
        while ($file = readdir($dirHandle)) {
            if ($file !== '.' && $file !== '..') {
                if (!is_dir($source . '/' . $file)) {
                    $__dest = $dest . '/' . $file;
                } else {
                    $__dest = $dest . '/' . $file;
                }
                //echo "$source/$file ||| $__dest<br />";
                $data = osc_copy($source . '/' . $file, $__dest, $options);
                if ($data == false) {
                    $result = false;
                }
            }
        }
        closedir($dirHandle);
    } else {
        $result = true;
    }

    return $result;
}


    /**
     * @param $file1
     * @param $file2
     *
     * @return bool
     */
function osc_copyemz($file1, $file2)
{
    $contentx   = @file_get_contents($file1);
    $openedfile = fopen($file2, 'wb');
    fwrite($openedfile, $contentx);
    fclose($openedfile);
    if ($contentx === false) {
        $status = false;
    } else {
        $status = true;
    }

    return $status;
}


    /**
     * Dump osclass database into path file
     *
     * @param string $path
     * @param string $file
     *
     * @return int
     */
function osc_dbdump($path, $file)
{
    require_once LIB_PATH . 'osclass/model/Dump.php';
    if (!is_writable($path)) {
        return -4;
    }
    if ($path == '') {
        return -1;
    }

    //checking connection
    $dump = Dump::newInstance();
    if (!$dump) {
        return -2;
    }

    $path   .= $file;
    $result = $dump->showTables();

    if (!$result) {
        $_str = '';
        $_str .= '/* no tables in ' . DB_NAME . ' */';
        $_str .= "\n";

        $f = fopen($path, 'ab');
        fwrite($f, $_str);
        fclose($f);

        return -3;
    }

    $_str = '/* OSCLASS MYSQL Autobackup (' . date(osc_date_format() ?: 'Y-m-d') . ' ' . date(osc_time_format() ?: 'H:i:s') . ') */' . "\n";

    $f = fopen($path, 'ab');
    fwrite($f, $_str);
    fclose($f);

    $tables = array();
    foreach ($result as $_table) {
        $tableName          = current($_table);
        $tables[$tableName] = $tableName;
    }

    $tables_order = array(
        't_locale',
        't_country',
        't_currency',
        't_region',
        't_city',
        't_city_area',
        't_widget',
        't_admin',
        't_user',
        't_user_description',
        't_category',
        't_category_description',
        't_category_stats',
        't_item',
        't_item_description',
        't_item_location',
        't_item_stats',
        't_item_resource',
        't_item_comment',
        't_preference',
        't_user_preferences',
        't_pages',
        't_pages_description',
        't_plugin_category',
        't_cron',
        't_alerts',
        't_keywords',
        't_meta_fields',
        't_meta_categories',
        't_item_meta'
    );
    // Backup default Osclass tables in order, so no problem when importing them back
    foreach ($tables_order as $table) {
        if (array_key_exists(DB_TABLE_PREFIX . $table, $tables)) {
            $dump->table_structure($path, DB_TABLE_PREFIX . $table);
            $dump->table_data($path, DB_TABLE_PREFIX . $table);
            unset($tables[DB_TABLE_PREFIX . $table]);
        }
    }

    // Backup the rest of tables
    foreach ($tables as $table) {
        $dump->table_structure($path, $table);
        $dump->table_data($path, $table);
    }

    return 1;
}


    /**
     * Returns true if there is curl on system environment
     *
     * @return bool
     */
function testCurl()
{
    return !(!function_exists('curl_init') || !function_exists('curl_exec'));
}


    /**
     * Returns true if there is fsockopen on system environment
     *
     * @return bool|\type
     */
function testFsockopen()
{
    if (!function_exists('fsockopen')) {
        return false;
    }

    return true;
}


    /**
     * IF http-chunked-decode not exist implement here
     *
     * @since 3.0
     */
if (!function_exists('http_chunked_decode')) {
    /**
     * dechunk an http 'transfer-encoding: chunked' message
     *
     * @param string $chunk the encoded message
     *
     * @return string the decoded message.  If $chunk wasn't encoded properly it will be returned unmodified.
     */
    function http_chunked_decode($chunk)
    {
        $pos     = 0;
        $len     = strlen($chunk);
        $dechunk = null;
        while (($pos < $len)
            && ($chunkLenHex = substr($chunk, $pos, ($newlineAt = strpos($chunk, "\n", $pos + 1)) - $pos))) {
            if (!is_hex($chunkLenHex)) {
                trigger_error('Value is not properly chunk encoded', E_USER_WARNING);

                return $chunk;
            }

            $pos      = $newlineAt + 1;
            $chunkLen = hexdec(rtrim($chunkLenHex, "\r\n"));
            $dechunk  .= substr($chunk, $pos, $chunkLen);
            $pos      = strpos($chunk, "\n", $pos + $chunkLen) + 1;
        }

        return $dechunk;
    }
}

    /**
     * determine if a string can represent a number in hexadecimal
     *
     * @param string $hex
     *
     * @return boolean true if the string is a hex, otherwise false
     * @since 3.0
     *
     */
function is_hex($hex)
{
    // regex is for weenies
    $hex = strtolower(trim(ltrim($hex, '0')));
    if (empty($hex)) {
        $hex = 0;
    }
    $dec = hexdec($hex);

    return ($hex == dechex($dec));
}


    /**
     * Process response and return headers and body
     *
     * @param type $content
     *
     * @return array|\type
     * @since 3.0
     */
function processResponse($content)
{
    $res     = explode("\r\n\r\n", $content);
    $headers = $res[0];
    $body    = isset($res[1]) ? $res[1] : '';

    if (!is_string($headers)) {
        return array();
    }

    return array('headers' => $headers, 'body' => $body);
}


    /**
     * Parse headers and return into array format
     *
     * @param string $headers
     *
     * @return array|\type
     */
function processHeaders($headers)
{
    $headers    = str_replace("\r\n", "\n", $headers);
    $headers    = preg_replace('/\n[ \t]/', ' ', $headers);
    $headers    = explode("\n", $headers);
    $tmpHeaders = $headers;
    $headers    = array();

    foreach ($tmpHeaders as $aux) {
        if (preg_match('/^(.*):\s(.*)$/', $aux, $matches)) {
            $headers[strtolower($matches[1])] = $matches[2];
        }
    }

    return $headers;
}


    /**
     * Download file using fsockopen
     *
     * @param string $sourceFile
     * @param mixed  $fileout
     *
     * @param null   $post_data
     *
     * @return bool|string
     * @since 3.0
     */
function download_fsockopen($sourceFile, $fileout = null, $post_data = null)
{
    // parse URL
    $aUrl = parse_url($sourceFile);
    $host = $aUrl['host'];
    if ('localhost' === strtolower($host)) {
        $host = '127.0.0.1';
    }

    $link = $aUrl['path'] . (isset($aUrl['query']) ? '?' . $aUrl['query'] : '');

    if (empty($link)) {
        $link .= '/';
    }

    $fp = @fsockopen($host, 80, $errno, $errstr, 30);
    if (!$fp) {
        return false;
    }

    $ua  = Params::getServerParam('HTTP_USER_AGENT') . ' Osclass (v.' . osc_version() . ')';
    $out = ($post_data != null && is_array($post_data) ? 'POST' : 'GET') . " $link HTTP/1.1\r\n";
    $out .= "Host: $host\r\n";
    $out .= "User-Agent: $ua\r\n";
    $out .= "Connection: Close\r\n\r\n";
    $out .= "\r\n";
    if ($post_data != null && is_array($post_data)) {
        $out .= http_build_query($post_data);
    }
    fwrite($fp, $out);

    $contents = '';
    while (!feof($fp)) {
        $contents .= fgets($fp, 1024);
    }

    fclose($fp);

    // check redirections ?
    // if (redirections) then do request again
    $aResult = processResponse($contents);
    $headers = processHeaders($aResult['headers']);

    $location = @$headers['location'];
    if (isset($location) && $location != '') {
        $aUrl = parse_url($headers['location']);

        $host = $aUrl['host'];
        if ('localhost' === strtolower($host)) {
            $host = '127.0.0.1';
        }

        $requestPath = $aUrl['path'] . (isset($aUrl['query']) ? '?' . $aUrl['query'] : '');

        if (empty($requestPath)) {
            $requestPath .= '/';
        }

        download_fsockopen($host, $requestPath, $fileout);
    } else {
        $body             = $aResult['body'];
        $transferEncoding = @$headers['transfer-encoding'];
        if ($transferEncoding === 'chunked') {
            $body = http_chunked_decode($aResult['body']);
        }
        if ($fileout != null) {
            $ff = @fopen($fileout, 'wb+');
            if ($ff !== false) {
                fwrite($ff, $body);
                fclose($ff);

                return true;
            }

            return false;
        }

        return $body;
    }
}


    /**
     * @param      $sourceFile
     * @param      $downloadedFile
     * @param null $post_data
     *
     * @return bool
     */
function osc_downloadFile($sourceFile, $downloadedFile, $post_data = null)
{
    if (strpos($downloadedFile, '../') !== false || strpos($downloadedFile, "..\\") !== false) {
        return false;
    }

    if (testCurl()) {
        @set_time_limit(0);
        $fp = @fopen(osc_content_path() . 'downloads/' . $downloadedFile, 'wb+');
        if ($fp) {
            $ch = curl_init($sourceFile);
            @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, Params::getServerParam('HTTP_USER_AGENT') . ' Osclass (v.' . osc_version() . ')');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_REFERER, osc_base_url());

            if (stripos($sourceFile, 'https') !== false) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            }
            if ($post_data != null) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
            }

            curl_exec($ch);
            curl_close($ch);
            fclose($fp);

            return true;
        }

        return false;
    }

    if (testFsockopen()) { // test curl/fsockopen
        $downloadedFile = osc_content_path() . 'downloads/' . $downloadedFile;
        download_fsockopen($sourceFile, $downloadedFile);

        return true;
    }

    return false;
}


    /**
     * @param      $url
     * @param null $post_data
     *
     * @return bool|string|null
     */
function osc_file_get_contents($url, $post_data = null)
{
    $data = null;
    if (testCurl()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, Params::getServerParam('HTTP_USER_AGENT') . ' Osclass (v.' . osc_version() . ')');
        if (!defined('CURLOPT_RETURNTRANSFER')) {
            define('CURLOPT_RETURNTRANSFER', 1);
        }
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_REFERER, osc_base_url());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (stripos($url, 'https') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        if ($post_data != null) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        }

        $data = curl_exec($ch);
        curl_close($ch);
    } elseif (testFsockopen()) {
        $data = download_fsockopen($url, null, $post_data);
    }

    return $data;
}


    /**
     * Check if we loaded some specific module of apache
     *
     * @param string $mod
     *
     * @return bool
     */
function apache_mod_loaded($mod)
{
    if (function_exists('apache_get_modules')) {
        $modules = apache_get_modules();
        if (in_array($mod, $modules)) {
            return true;
        }
    } elseif (function_exists('phpinfo')) {
        ob_start();
        phpinfo(INFO_MODULES);
        $content = ob_get_clean();
        if (stripos($content, $mod) !== false) {
            return true;
        }
    }

    return false;
}


    /**
     * Change version to param number
     *
     * @param mixed version
     */
function osc_changeVersionTo($version = null)
{
    if ($version != null) {
        osc_set_preference('version', $version);
        osc_reset_preferences();
    }
}


    /**
     * @param $array
     *
     * @return string
     */
function strip_slashes_extended($array)
{
    if (is_array($array)) {
        foreach ($array as $k => &$v) {
            $v = strip_slashes_extended($v);
        }
    } else {
        $array = stripslashes($array);
    }

    return $array;
}


    /**
     * Unzip's a specified ZIP file to a location
     *
     * @param string $file Full path of the zip file
     * @param string $to   Full path where it is going to be unzipped
     *
     * @return int
     *  0 - destination folder not writable (or not exist and cannot be created)
     *  1 - everything was OK
     *  2 - zip is empty
     *  -1 : file could not be created (or error reading the file from the zip)
     */
function osc_unzip_file($file, $to)
{
    if (strpos($to, '../') !== false || strpos($to, "..\\") !== false) {
        return 0;
    }

    if (!file_exists($to) && !mkdir($to, 0766) && !is_dir($to)) {
        return 0;
    }

    @chmod($to, 0755);

    if (!is_writable($to)) {
        return 0;
    }

    if (class_exists('ZipArchive')) {
        return _unzip_file_ziparchive($file, $to);
    }

    // if ZipArchive class doesn't exist, we use PclZip
    return _unzip_file_pclzip($file, $to);
}


    /**
     * We assume that the $to path is correct and can be written. It unzips an archive using the PclZip library.
     *
     * @param string $file Full path of the zip file
     * @param string $to   Full path where it is going to be unzipped
     *
     * @return int
     */
function _unzip_file_ziparchive($file, $to)
{
    if (strpos($to, '../') !== false || strpos($to, "..\\") !== false) {
        return 0;
    }

    $zip     = new ZipArchive();
    $zipopen = $zip->open($file, 4);

    if ($zipopen !== true) {
        return 2;
    }
    // The zip is empty
    if ($zip->numFiles == 0) {
        return 2;
    }


    for ($i = 0; $i < $zip->numFiles; $i++) {
        $file = $zip->statIndex($i);

        if (!$file) {
            return -1;
        }

        if (strpos($file['name'], '__MACOSX/') === 0) {
            continue;
        }
        if (strpos($file['name'], '../') !== false) {
            continue;
        }

        if (substr($file['name'], -1) === '/') {
            if (!mkdir($concurrentDirectory = $to . $file['name'], 0755) && !is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            continue;
        }

        $content = $zip->getFromIndex($i);
        if ($content === false) {
            return -1;
        }

        $fp = @fopen($to . $file['name'], 'wb');
        if (!$fp) {
            return -1;
        }

        @fwrite($fp, $content);
        @fclose($fp);
    }

    $zip->close();

    return 1;
}


    /**
     * We assume that the $to path is correct and can be written. It unzips an archive using the PclZip library.
     *
     * @param string $zip_file Full path of the zip file
     * @param string $to       Full path where it is going to be unzipped
     *
     * @return int
     */
function _unzip_file_pclzip($zip_file, $to)
{
    if (strpos($to, '../') !== false || strpos($to, "..\\") !== false) {
        return false;
    }

    $archive = new PclZip($zip_file);
    $files   = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING);
    if (($files) == false) {
        return 2;
    }

    // check if the zip is not empty
    if (count($files) === 0) {
        return 2;
    }

    // Extract the files from the zip
    foreach ($files as $file) {
        if (strpos($file['filename'], '__MACOSX/') === 0) {
            continue;
        }
        if (strpos($file['filename'], '../') !== false) {
            continue;
        }

        if ($file['folder']) {
            if (!mkdir($concurrentDirectory = $to . $file['filename'], 0755) && !is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            continue;
        }


        $fp = @fopen($to . $file['filename'], 'wb');
        if (!$fp) {
            return -1;
        }

        @fwrite($fp, $file['content']);
        @fclose($fp);
    }

    return 1;
}


    /**
     * Common interface to zip a specified folder to a file using ziparchive or pclzip
     *
     * @param string $archive_folder full path of the folder
     * @param string $archive_name   full path of the destination zip file
     *
     * @return int
     */
function osc_zip_folder($archive_folder, $archive_name)
{
    if (strpos($archive_folder, '../') !== false || strpos($archive_name, '../') !== false || strpos($archive_folder, "..\\") !== false
        || strpos($archive_name, "..\\") !== false
    ) {
        return false;
    }

    if (class_exists('ZipArchive')) {
        return _zip_folder_ziparchive($archive_folder, $archive_name);
    }

    // if ZipArchive class doesn't exist, we use PclZip
    return _zip_folder_pclzip($archive_folder, $archive_name);
}


    /**
     * Zips a specified folder to a file
     *
     * @param string $archive_folder full path of the folder
     * @param string $archive_name   full path of the destination zip file
     *
     * @return int
     */
function _zip_folder_ziparchive($archive_folder, $archive_name)
{
    if (strpos($archive_folder, '../') !== false || strpos($archive_name, '../') !== false || strpos($archive_folder, "..\\") !== false
        || strpos($archive_name, "..\\") !== false
    ) {
        return false;
    }

    $zip = new ZipArchive;
    if ($zip->open($archive_name, ZipArchive::CREATE) === true) {
        $dir = preg_replace('/[\/]{2,}/', '/', $archive_folder . '/');

        $dirs = array($dir);
        while (count($dirs)) {
            $dir = current($dirs);
            $zip->addEmptyDir(str_replace(ABS_PATH, '', $dir));

            $dh = opendir($dir);
            while (false !== ($_file = readdir($dh))) {
                if ($_file !== '.' && $_file !== '..' && stripos($_file, 'Osclass_backup.') === false) {
                    if (is_file($dir . $_file)) {
                        $zip->addFile($dir . $_file, str_replace(ABS_PATH, '', $dir . $_file));
                    } elseif (is_dir($dir . $_file)) {
                        $dirs[] = $dir . $_file . '/';
                    }
                }
            }
            closedir($dh);
            array_shift($dirs);
        }
        $zip->close();

        return true;
    }

    return false;
}


    /**
     * Zips a specified folder to a file
     *
     * @param string $archive_folder full path of the folder
     * @param string $archive_name   full path of the destination zip file
     *
     * @return int
     */
function _zip_folder_pclzip($archive_folder, $archive_name)
{
    if (strpos($archive_folder, '../') !== false || strpos($archive_name, '../') !== false || strpos($archive_folder, "..\\") !== false
        || strpos($archive_name, "..\\") !== false
    ) {
        return false;
    }

    $zip = new PclZip($archive_name);
    if ($zip) {
        $dir = preg_replace('/[\/]{2,}/', '/', $archive_folder . '/');

        $v_dir    = osc_base_path();
        $v_remove = $v_dir;

        // To support windows and the C: root you need to add the
        // following 3 lines, should be ignored on linux
        if ($v_dir[1] === ':') {
            $v_remove = substr($v_dir, 2);
        }
        $v_list = $zip->create($dir, PCLZIP_OPT_REMOVE_PATH, $v_remove);

        return !($v_list == 0);
    }

    return false;
}


    /**
     * @return bool
     */
function osc_check_recaptcha()
{
    $gReCaptchaResponse = Params::getParam('g-recaptcha-response');
    if ($gReCaptchaResponse !== '' || $gReCaptchaResponse !== false || $gReCaptchaResponse !== 0) {
        $recaptcha = new ReCaptcha(osc_recaptcha_private_key());
        $resp      = $recaptcha->verify($gReCaptchaResponse, Params::getServerParam('REMOTE_ADDR'));
        if ($resp->isSuccess()) {
            return true;
        }
    }

    return false;
}


    /**
     * replace double slash with single slash
     *
     * @param $path
     *
     * @return string
     */
function osc_replace_double_slash($path)
{
    return str_replace('//', '/', $path);
}


    /**
     * @param mixed|string $dir
     *
     * @return bool
     */
function osc_check_dir_writable($dir = ABS_PATH)
{
    if (strpos($dir, '../') !== false || strpos($dir, "..\\") !== false) {
        return false;
    }

    clearstatcache();
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir(osc_replace_double_slash($dir . '/' . $file))) {
                    if (osc_replace_double_slash($dir) === (ABS_PATH . 'oc-content/themes')) {
                        if ($file === 'bender' || $file === 'index.php') {
                            $res = osc_check_dir_writable(osc_replace_double_slash($dir . '/' . $file));
                            if (!$res) {
                                return false;
                            }
                        }
                    } elseif (osc_replace_double_slash($dir) === (ABS_PATH . 'oc-content/plugins')) {
                        if ($file === 'google_maps' || $file === 'google_analytics' || $file === 'index.php') {
                            $res = osc_check_dir_writable(osc_replace_double_slash($dir . '/' . $file));
                            if (!$res) {
                                return false;
                            }
                        }
                    } elseif (osc_replace_double_slash($dir) === (ABS_PATH . 'oc-content/languages')) {
                        if ($file === 'en_US' || $file === 'index.php') {
                            $res = osc_check_dir_writable(osc_replace_double_slash($dir . '/' . $file));
                            if (!$res) {
                                return false;
                            }
                        }
                    } elseif (osc_replace_double_slash($dir) === (ABS_PATH . 'oc-content/downloads')) {
                        continue;
                    } elseif (osc_replace_double_slash($dir) === osc_uploads_path()) {
                        continue;
                    } else {
                        $res = osc_check_dir_writable(osc_replace_double_slash($dir . '/' . $file));
                        if (!$res) {
                            return false;
                        }
                    }
                } else {
                    return is_writable(osc_replace_double_slash($dir . '/' . $file));
                }
            }
        }
        closedir($dh);
    }

    return true;
}


    /**
     * @param mixed|string $dir
     *
     * @return bool
     */
function osc_change_permissions($dir = ABS_PATH)
{
    if (strpos($dir, '../') !== false || strpos($dir, "..\\") !== false) {
        return false;
    }

    clearstatcache();
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if ($file !== '.' && $file !== '..' && $file[0] !== '.') {
                if (!is_writable(osc_replace_double_slash($dir . '/' . $file))) {
                    $result = chmod(str_replace('//', '/', $dir . '/' . $file), 0755);
                }

                if (is_dir(osc_replace_double_slash($dir . '/' . $file))) {
                    if (osc_replace_double_slash($dir) === (ABS_PATH . 'oc-content/themes')) {
                        if ($file === 'modern' || $file === 'index.php') {
                            $res = osc_change_permissions(str_replace('//', '/', $dir . '/' . $file));
                            if (!$res) {
                                return false;
                            }
                        }
                    } elseif (osc_replace_double_slash($dir) === (ABS_PATH . 'oc-content/plugins')) {
                        if ($file === 'google_maps' || $file === 'google_analytics' || $file === 'index.php') {
                            $res = osc_change_permissions(osc_replace_double_slash($dir . '/' . $file));
                            if (!$res) {
                                return false;
                            }
                        }
                    } elseif (osc_replace_double_slash($dir) === (ABS_PATH . 'oc-content/languages')) {
                        if ($file === 'en_US' || $file === 'index.php') {
                            $res = osc_change_permissions(osc_replace_double_slash($dir . '/' . $file));
                            if (!$res) {
                                return false;
                            }
                        }
                    } elseif (osc_replace_double_slash($dir) === (ABS_PATH . 'oc-content/downloads')) {
                        continue;
                    } elseif (osc_replace_double_slash($dir) === osc_uploads_path()) {
                        continue;
                    } else {
                        $res = osc_change_permissions(osc_replace_double_slash($dir . '/' . $file));
                        if (!$res) {
                            return false;
                        }
                    }
                    return true;
                }

                if (isset($result)) {
                    return $result;
                }

                return false;
            }
        }
        closedir($dh);
    }
    return true;
}


    /**
     * @param mixed|string $dir
     *
     * @return array|bool
     */
function osc_save_permissions($dir = ABS_PATH)
{
    if (strpos($dir, '../') !== false || strpos($dir, "..\\") !== false) {
        return false;
    }

    $perms       = array();
    $perms[$dir] = fileperms($dir);
    clearstatcache();
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir(str_replace('//', '/', $dir . '/' . $file))) {
                    $res = osc_save_permissions(str_replace('//', '/', $dir . '/' . $file));
                    foreach ($res as $k => $v) {
                        $perms[$k] = $v;
                    }
                } else {
                    $perms[str_replace('//', '/', $dir . '/' . $file)] = fileperms(str_replace('//', '/', $dir . '/' . $file));
                }
            }
        }
        closedir($dh);
    }

    return $perms;
}


    /**
     * @param $price
     *
     * @return string
     */
function osc_prepare_price($price)
{
    return number_format($price / 1000000, osc_locale_num_dec(), osc_locale_dec_point(), osc_locale_thousands_sep());
}


    /**
     * Recursive glob function
     *
     * @param string $pattern
     * @param int    $flags
     * @param string $path
     *
     * @return array of files
     */
function rglob($pattern, $flags = 0, $path = '')
{
    if (!$path && ($dir = dirname($pattern)) !== '.') {
        if ($dir === '\\' || $dir === '/') {
            $dir = '';
        }

        return rglob(basename($pattern), $flags, $dir . '/');
    }
    /**
     *
     * $paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
     * $files = glob($path . $pattern, $flags);
     * foreach ($paths as $p) {
     * $files = array_merge($files, rglob($pattern, $flags, $p . '/'));
     * }
     */
    //TODO
    //Better Maybe ?

    $paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
    $files = glob($path . $pattern, $flags);
    foreach ($paths as $p) {
        $files[] = rglob($pattern, $flags, $p . '/');
    }
    $files = array_merge([], ...$files);

    return $files;
}


    /**
     * Market util functions
     *
     * @param      $update_uri
     * @param null $version
     *
     * @return bool
     */
function osc_check_plugin_update($update_uri, $version = null)
{
    $uri = _get_market_url('plugins', $update_uri);
    if ($uri != false) {
        return _need_update($uri, $version);
    }

    return false;
}


    /**
     * @param string $update_uri
     * @param null   $version
     *
     * @return bool
     */
function osc_check_theme_update($update_uri, $version = null)
{
    $uri = _get_market_url('themes', $update_uri);
    if ($uri != false) {
        return _need_update($uri, $version);
    }

    return false;
}


    /**
     * @param string $update_uri
     * @param null   $version
     *
     * @return bool
     */
function osc_check_language_update($update_uri, $version = null)
{
    $uri = _get_market_url('languages', $update_uri);
    if ($uri != false) {
        if (false === ($json = @osc_file_get_contents($uri))) {
            return false;
        }

        $data = json_decode($json, true);
        if (isset($data['s_version'])) {
            $result = version_compare2($version, $data['s_version']);
            if ($result == -1) {
                // market have a newer version of this language
                $result = version_compare2($data['s_version'], OSCLASS_VERSION);
                if ($result == 0 || $result == -1) {
                    // market version is compatible with current osclass version
                    return true;
                }
            }
        }
    }

    return false;
}


    /**
     * @param $type
     * @param $update_uri
     *
     * @return bool|string
     */
function _get_market_url($type, $update_uri)
{
    if ($update_uri == null) {
        return false;
    }

    if (in_array($type, array('plugins', 'themes', 'languages'))) {
        if (stripos($update_uri, 'http://') === false && stripos($update_uri, 'https://') === false) {
            // OSCLASS OFFICIAL REPOSITORY
            // $uri = osc_market_url( $type , $update_uri );
            return false;
        }


        /** @var string $uri */
        $uri = $update_uri;

        return $uri;
    }

    return false;
}


    /**
     * @param $uri
     * @param $version
     *
     * @return bool
     */
function _need_update($uri, $version)
{
    if (false === ($json = osc_file_get_contents($uri))) {
        return false;
    }

    $data = json_decode($json, true);
    if (isset($data['s_version'])) {
        $result = version_compare2($data['s_version'], $version);
        if ($result === 1) {
            return true;
        }
    }

    return false;
}


    /**
     * Returns
     *      0  if both are equal,
     *      1  if A > B, and
     *      -1 if B < A.
     *
     * @param string $a -> from market
     * @param string $b -> installed version
     *
     * @return int
     */
function version_compare2($a, $b)
{
    $aA = explode('.', rtrim($a, '.0')); //Split version into pieces and remove trailing .0
    $aB = explode('.', rtrim($b, '.0')); //Split version into pieces and remove trailing .0
    foreach ($aA as $depth => $aVal) { //Iterate over each piece of A
        if (isset($b[$depth])) { //If B matches A to this depth, compare the values
            if ($aVal > $aB[$depth]) {
                return 1;
            }

            if ($aVal < $aB[$depth]) {
                return -1;
            } //Return A > B //Return B > A
            //An equal result is inconclusive at this point
        } else { //If B does not match A to this depth, then A comes after B in sort order
            return 1; //so return A > B
        }
    }
    //At this point, we know that to the depth that A and B extend to, they are equivalent.
    //Either the loop ended because A is shorter than B, or both are equal.
    return (count($aA) < count($aB)) ? -1 : 0;
}


    /**
     * @param $aux
     * @param $categoryTotal
     *
     * @return int
     */
function _recursive_category_stats(&$aux, &$categoryTotal)
{
    $count_items = Item::newInstance()->numItems($aux);
    if (is_array($aux['categories'])) {
        foreach ($aux['categories'] as &$cat) {
            $count_items += _recursive_category_stats($cat, $categoryTotal);
        }
        unset($cat);
    }
    $categoryTotal[$aux['pk_i_id']] = $count_items;

    return $count_items;
}


    /**
     * Update category stats
     *
     * @return void
     */
function osc_update_cat_stats()
{
    $categoryTotal = array();
    $aCategories   = Category::newInstance()->toTreeAll();

    foreach ($aCategories as &$category) {
        if ($category['fk_i_parent_id'] === null) {
            _recursive_category_stats($category, $categoryTotal);
        }
    }
    unset($category);

    $sql     = 'REPLACE INTO ' . DB_TABLE_PREFIX . 't_category_stats (fk_i_category_id, i_num_items) VALUES ';
    $aValues = array();
    foreach ($categoryTotal as $k => $v) {
        $aValues[] = "($k, $v)";
    }
    $sql .= implode(',', $aValues);

    CategoryStats::newInstance()->dao->query($sql);
}


    /**
     * Recount items for a given a category id
     *
     * @param int $id
     */
function osc_update_cat_stats_id($id)
{
    // get sub categorias
    $aCategories   = Category::newInstance()->findSubcategories($id);
    $categoryTotal = 0;
    $category      = Category::newInstance()->findByPrimaryKey($id);

    if (count($aCategories) > 0) {
        // sumar items de la categoría
        foreach ($aCategories as $subcategory) {
            $total         = Item::newInstance()->numItems($subcategory);
            $categoryTotal += $total;
        }
        $categoryTotal += Item::newInstance()->numItems($category);
    } else {
        $total         = Item::newInstance()->numItems($category);
        $categoryTotal += $total;
    }

    $sql = 'REPLACE INTO ' . DB_TABLE_PREFIX . 't_category_stats (fk_i_category_id, i_num_items) VALUES ';
    $sql .= ' (' . $id . ', ' . $categoryTotal . ')';

    CategoryStats::newInstance()->dao->query($sql);

    if ($category['fk_i_parent_id'] != 0) {
        osc_update_cat_stats_id($category['fk_i_parent_id']);
    }
}


    /**
     * Update locations stats. I moved this function from cron.daily.php:update_location_stats
     *
     * @param bool $force
     * @param int  $limit
     *
     * @return int
     * @since 3.1
     */
function osc_update_location_stats($force = false, $limit = 1000)
{
    $loctmp   = LocationsTmp::newInstance();
    $workToDo = $loctmp->count();

    if ($workToDo > 0) {
        // there is work to do
        if ($limit === 'auto') {
            $total_cities = City::newInstance()->count();
            $limit        = max(1000, ceil($total_cities / 22));
        }
        $aLocations = $loctmp->getLocations($limit);
        foreach ($aLocations as $location) {
            $id   = $location['id_location'];
            $type = $location['e_type'];
            $data = 0;
            // update locations stats
            switch ($type) {
                case 'COUNTRY':
                    $numItems = CountryStats::newInstance()->calculateNumItems($id);
                    $data     = CountryStats::newInstance()->setNumItems($id, $numItems);
                    unset($numItems);
                    break;
                case 'REGION':
                    $numItems = RegionStats::newInstance()->calculateNumItems($id);
                    $data     = RegionStats::newInstance()->setNumItems($id, $numItems);
                    unset($numItems);
                    break;
                case 'CITY':
                    $numItems = CityStats::newInstance()->calculateNumItems($id);
                    $data     = CityStats::newInstance()->setNumItems($id, $numItems);
                    unset($numItems);
                    break;
                default:
                    break;
            }
            if ($data >= 0) {
                $loctmp->delete(array(
                    'e_type'      => $location['e_type'],
                    'id_location' => $location['id_location']
                ));
            }
        }
    } elseif ($force) {
        // we need to populate location tmp table
        $aCountry = Country::newInstance()->listAll();

        foreach ($aCountry as $country) {
            $aRegionsCountry = Region::newInstance()->findByCountry($country['pk_c_code']);
            $loctmp->insert(array('id_location' => $country['pk_c_code'], 'e_type' => 'COUNTRY'));
            foreach ($aRegionsCountry as $region) {
                $aCitiesRegion = City::newInstance()->findByRegion($region['pk_i_id']);
                $loctmp->insert(array('id_location' => $region['pk_i_id'], 'e_type' => 'REGION'));
                $batchCities = array();
                foreach ($aCitiesRegion as $city) {
                    $batchCities[] = $city['pk_i_id'];
                }
                unset($aCitiesRegion);
                $loctmp->batchInsert($batchCities, 'CITY');
                unset($batchCities);
            }
            unset($aRegionsCountry);
        }
        unset($aCountry);
        osc_set_preference('location_todo', LocationsTmp::newInstance()->count());
    }

    return LocationsTmp::newInstance()->count();
}


    /** Translate current categories to new locale
     *
     * @param $locale
     *
     * @since 3.2.1
     */
function osc_translate_categories($locale)
{
    $old_locale = Session::newInstance()->_get('adminLocale');
    Session::newInstance()->_set('adminLocale', $locale);
    Translation::newInstance()->_load(osc_translations_path() . $locale . '/core.mo', 'cat_' . $locale);
    $catManager     = Category::newInstance();
    $old_categories = $catManager->_findNameIDByLocale($old_locale);
    $tmp_categories = $catManager->_findNameIDByLocale($locale);
    foreach ($tmp_categories as $category) {
        $new_categories[$category['pk_i_id']] = $category['s_name'];
    }
    unset($tmp_categories);
    foreach ($old_categories as $category) {
        if (!isset($new_categories[$category['pk_i_id']])) {
            $fieldsDescription['s_name']           = __($category['s_name'], 'cat_' . $locale);
            $fieldsDescription['s_description']    = '';
            $fieldsDescription['fk_i_category_id'] = $category['pk_i_id'];
            $fieldsDescription['fk_c_locale_code'] = $locale;
            $slug_tmp                              = $slug = osc_sanitizeString(osc_apply_filter('slug', $fieldsDescription['s_name']));
            $slug_unique                           = 1;
            while (true) {
                if (!$catManager->findBySlug($slug)) {
                    break;
                }

                $slug = $slug_tmp . '_' . $slug_unique;
                $slug_unique++;
            }
            $fieldsDescription['s_slug'] = $slug;
            $catManager->insertDescription($fieldsDescription);
        }
    }
    Session::newInstance()->_set('adminLocale', $old_locale);
}


    /**
     * @return string
     */
function get_ip()
{
    if (Params::getServerParam('HTTP_CLIENT_IP') != '') {
        return Params::getServerParam('HTTP_CLIENT_IP');
    }

    if (Params::getServerParam('HTTP_X_FORWARDED_FOR') != '') {
        $ip_array = explode(',', Params::getServerParam('HTTP_X_FORWARDED_FOR'));
        foreach ($ip_array as $ip) {
            return trim($ip);
        }
    }

    return Params::getServerParam('REMOTE_ADDR');
}


    /**
     * CSRFGUARD functions *
     *
     */
function osc_csrfguard_generate_token()
{
    $token_name = Session::newInstance()->_get('token_name');
    if ($token_name != '' && Session::newInstance()->_get($token_name) != '') {
        return array($token_name, Session::newInstance()->_get($token_name));
    }
    $unique_token_name = osc_csrf_name() . '_' . mt_rand(0, mt_getrandmax());
    if (function_exists('hash_algos') and in_array('sha512', hash_algos())) {
        $token = hash('sha512', mt_rand(0, mt_getrandmax()));
    } else {
        $token = '';
        for ($i = 0; $i < 128; ++$i) {
            $r = mt_rand(0, 35);
            if ($r < 26) {
                $c = chr(ord('a') + $r);
            } else {
                $c = chr(ord('0') + $r - 26);
            }
            $token .= $c;
        }
    }
    Session::newInstance()->_set('token_name', $unique_token_name);
    Session::newInstance()->_set($unique_token_name, $token);

    return array($unique_token_name, $token);
}


    /**
     * @param $unique_form_name
     * @param $token_value
     *
     * @return bool
     */
    /**
     * @param $unique_form_name
     * @param $token_value
     *
     * @return bool
     */
function osc_csrfguard_validate_token($unique_form_name, $token_value)
{
    $name  = Session::newInstance()->_get('token_name');
    $token = Session::newInstance()->_get($unique_form_name);

    return $name === $unique_form_name && $token === $token_value;
}


    /**
     * @param $form_data_html
     *
     * @return mixed
     */
    /**
     * @param $form_data_html
     *
     * @return mixed
     */
function osc_csrfguard_replace_forms($form_data_html)
{
    $count = preg_match_all('/<form(.*?)>/is', $form_data_html, $matches, PREG_SET_ORDER);
    if (is_array($matches)) {
        foreach ($matches as $m) {
            if (strpos($m[1], 'nocsrf') !== false) {
                continue;
            }
            $form_data_html = str_replace($m[0], "<form{$m[1]}>" . osc_csrf_token_form(), $form_data_html);
        }
    }

    return $form_data_html;
}


function osc_csrfguard_inject()
{
    $data = ob_get_clean();
    $data = osc_csrfguard_replace_forms($data);
    echo $data;
}


function osc_csrfguard_start()
{
    ob_start();
    $functions = osc_apply_filter('shutdown_functions', array('osc_csrfguard_inject'));
    foreach ($functions as $f) {
        register_shutdown_function($f);
    }
}


    /**
     * @param      $url
     * @param null $code
     */
    /**
     * @param      $url
     * @param null $code
     */
function osc_redirect_to($url, $code = null)
{
    if (ob_get_length() > 0) {
        ob_end_flush();
    }
    if ($code !== null) {
        header('Location: ' . $url, true, $code);
    } else {
        header('Location: ' . $url);
    }
    exit;
}


    /**
     * @param $type
     *
     * @return bool|int|mixed
     */
function osc_calculate_location_slug($type)
{
    $field = 'pk_i_id';
    switch ($type) {
        case 'country':
            $manager = Country::newInstance();
            $field   = 'pk_c_code';
            break;
        case 'region':
            $manager = Region::newInstance();
            break;
        case 'city':
            $manager = City::newInstance();
            break;
        default:
            return false;
            break;
    }
    $locations         = $manager->listByEmptySlug();
    $locations_changed = 0;
    foreach ($locations as $location) {
        $slug_tmp    = $slug = osc_sanitizeString($location['s_name']);
        $slug_unique = 1;
        while (true) {
            $location_slug = $manager->findBySlug($slug);
            if (!isset($location_slug[$field])) {
                break;
            }

            $slug = $slug_tmp . '-' . $slug_unique;
            $slug_unique++;
        }
        $locations_changed += $manager->update(array('s_slug' => $slug), array($field => $location[$field]));
    }

    return $locations_changed;
}


    /**
     * @param $input
     */
function osc_prune_array(&$input)
{
    foreach ($input as $key => &$value) {
        if (is_array($value)) {
            osc_prune_array($value);
            if (empty($input[$key])) {
                unset($input[$key]);
            }
        } elseif ($value === '' || $value === false || $value === null) {
            unset($input[$key]);
        }
    }
}


    /**
     * @return array
     */
function osc_do_upgrade()
{
    $message       = '';
    $error         = 0;
    $sql_error_msg = '';
    $rm_errors     = 0;
    $perms         = osc_save_permissions();
    osc_change_permissions();

    $maintenance_file = ABS_PATH . '.maintenance';
    $fileHandler      = @fopen($maintenance_file, 'wb');
    fclose($fileHandler);

    /***********************
     **** DOWNLOAD FILE ****
     ***********************/
    $data        = osc_file_get_contents('https://example.org/latest_version_v1.php');
    $data        = json_decode(substr($data, 1, -2), true);
    $source_file = $data['url'];
    if ($source_file != '') {
        $tmp      = explode('/', $source_file);
        $filename = end($tmp);
        $result   = osc_downloadFile($source_file, $filename);

        if ($result) { // Everything is OK, continue
            /**********************
             ***** UNZIP FILE *****
             **********************/
            $tmp_path = osc_content_path() . 'downloads/oc-temp/core-' . $data['version'] . '/';
            if (!mkdir($concurrentDirectory = osc_content_path() . 'downloads/oc-temp/', 0755) && !is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            if (!mkdir($tmp_path, 0755) && !is_dir($tmp_path)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $tmp_path));
            }
            $res = osc_unzip_file(osc_content_path() . 'downloads/' . $filename, $tmp_path);
            if ($res == 1) { // Everything is OK, continue
                /**********************
                 ***** COPY FILES *****
                 **********************/
                $fail = -1;
                if ($handle = opendir($tmp_path)) {
                    $fail = 0;
                    while (false !== ($_file = readdir($handle))) {
                        if ($_file !== '.' && $_file !== '..' && $_file !== 'oc-content') {
                            $data = osc_copy($tmp_path . $_file, ABS_PATH . $_file);
                            if ($data == false) {
                                $fail = 1;
                            }
                        }
                    }
                    closedir($handle);
                    //TRY TO REMOVE THE ZIP PACKAGE
                    @unlink(osc_content_path() . 'downloads/' . $filename);

                    if ($fail == 0) { // Everything is OK, continue
                        /************************
                         *** UPGRADE DATABASE ***
                         ************************/
                        $error_queries = array();
                        if (file_exists(osc_lib_path() . 'osclass/installer/struct.sql')) {
                            $sql = file_get_contents(osc_lib_path() . 'osclass/installer/struct.sql');

                            $conn          = DBConnectionClass::newInstance();
                            $c_db          = $conn->getOsclassDb();
                            $comm          = new DBCommandClass($c_db);
                            $error_queries = $comm->updateDB(str_replace('/*TABLE_PREFIX*/', DB_TABLE_PREFIX, $sql));
                        }
                        if ($error_queries[0]) { // Everything is OK, continue
                            /**********************************
                             ** EXECUTING ADDITIONAL ACTIONS **
                             **********************************/
                            osc_set_preference('update_core_json');
                            if (file_exists(osc_lib_path() . 'osclass/upgrade-funcs.php')) {
                                // There should be no errors here
                                define('AUTO_UPGRADE', true);
                                require_once osc_lib_path() . 'osclass/upgrade-funcs.php';
                            }
                            // Additional actions is not important for the rest of the proccess
                            // We will inform the user of the problems but the upgrade could continue
                            /****************************
                             ** REMOVE TEMPORARY FILES **
                             ****************************/
                            $rm_errors = 0;
                            $dir       = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp_path), RecursiveIteratorIterator::CHILD_FIRST);
                            for ($dir->rewind(); $dir->valid(); $dir->next()) {
                                if ($dir->isDir()) {
                                    if ($dir->getFilename() !== '.' && $dir->getFilename() !== '..' && !rmdir($dir->getPathname())) {
                                        $rm_errors++;
                                    }
                                } elseif (!unlink($dir->getPathname())) {
                                    $rm_errors++;
                                }
                            }
                            if (!rmdir($tmp_path)) {
                                $rm_errors++;
                            }
                            $deleted = @unlink(ABS_PATH . '.maintenance');
                            if ($rm_errors == 0) {
                                $message = __('Everything looks good! Your Osclass installation is up-to-date');
                                osc_add_flash_ok_message($message, 'admin');
                            } else {
                                $message =
                                    __('Nearly everything looks good! Your Osclass installation is up-to-date, but there were some errors removing temporary files. Please manually remove the "oc-content/downloads/oc-temp" folder');
                                osc_add_flash_warning_message($message, 'admin');
                                $error = 6; // Some errors removing files
                            }
                        } else {
                            $sql_error_msg = $error_queries[2];
                            $message       = __('Problems when upgrading the database');
                            $error         = 5; // Problems upgrading the database
                        }
                    } else {
                        $message = __('Problems when copying files. Please check your permissions. ');
                        $error   = 4; // Problems copying files. Maybe permissions are not correct
                    }
                } else {
                    $message = __('Nothing to copy');
                    $error   = 99; // Nothing to copy. THIS SHOULD NEVER HAPPEN, means we don't update any file!
                    $deleted = @unlink(ABS_PATH . '.maintenance');
                }
            } else {
                $message = __('Unzip failed');
                $error   = 3; // Unzip failed
                $deleted = @unlink(ABS_PATH . '.maintenance');
            }
        } else {
            $message = __('Download failed');
            $error   = 2; // Download failed
            unlink(ABS_PATH . '.maintenance');
        }
    } else {
        $message = __('Missing download URL');
        $error   = 1; // Missing download URL
        $deleted = @unlink(ABS_PATH . '.maintenance');
    }

    if ($error == 5) {
        $message .= '<br /><br />' . __('We had some errors upgrading your database. The follwing queries failed:') . implode('<br />', $sql_error_msg);
    }

    foreach ($perms as $k => $v) {
        @chmod($k, $v);
    }

    return array('error' => $error, 'message' => $message, 'version' => @$data['s_name']);
}


function osc_do_auto_upgrade()
{
    $data = osc_file_get_contents('https://example.org/latest_version_v1.php?callback=?');
    $data = preg_replace('|^\?\((.*?)\);$|', '$01', $data);
    /** @var object $json */
    $json            = json_decode($data);
    $result['error'] = 0;
    if (isset($json->version)) {
        if ($json->version > osc_version()) {
            osc_set_preference('update_core_json', $data);
            if (osc_check_dir_writable()) {
                if (strpos($json->version, substr(osc_version(), 0, 1)) !== 0) {
                    // NEW BRANCH
                    if (strpos(osc_auto_update(), 'branch') !== false) {
                        osc_run_hook('before_auto_upgrade');
                        $result = osc_do_upgrade();
                        osc_run_hook('after_auto_upgrade', $result);
                    }
                } elseif (substr($json->version, 1, 1) !== substr(osc_version(), 1, 1)) {
                    // MAJOR RELEASE
                    if (strpos(osc_auto_update(), 'branch') !== false || strpos(osc_auto_update(), 'major') !== false) {
                        osc_run_hook('before_auto_upgrade');
                        $result = osc_do_upgrade();
                        osc_run_hook('after_auto_upgrade', $result);
                    }
                } elseif (substr($json->version, 2, 1) !== substr(osc_version(), 2, 1)) {
                    // MINOR RELEASE
                    if (strpos(osc_auto_update(), 'branch') !== false || strpos(osc_auto_update(), 'major') !== false
                        || strpos(osc_auto_update(), 'minor') !== false
                    ) {
                        osc_run_hook('before_auto_upgrade');
                        $result = osc_do_upgrade();
                        osc_run_hook('after_auto_upgrade', $result);
                    }
                }
            }
        } else {
            osc_set_preference('update_core_json');
        }
        osc_set_preference('last_version_check', time());
    } else {
        osc_set_preference('update_core_json');
        osc_set_preference('last_version_check', time() - 23 * 3600);
    }

    if ($result['error'] == 0 || $result['error'] == 6) {
        if (strpos(osc_auto_update(), 'plugins') !== false) {
            $total = osc_check_plugins_update(true);
            if ($total > 0) {
                $elements = osc_get_preference('plugins_to_update');
                foreach ($elements as $element) {
                    if (osc_is_update_compatible('plugins', $element, $json->s_name)) {
                        osc_market('plugins', $element);
                    }
                }
            }
        }

        if (strpos(osc_auto_update(), 'themes') !== false) {
            $total = osc_check_themes_update(true);
            if ($total > 0) {
                $elements = osc_get_preference('themes_to_update');
                foreach ($elements as $element) {
                    if (osc_is_update_compatible('themes', $element, $json->s_name)) {
                        osc_market('themes', $element);
                    }
                }
            }
        }

        if (strpos(osc_auto_update(), 'languages') !== false) {
            $total = osc_check_languages_update(true);
            if ($total > 0) {
                $elements = osc_get_preference('languages_to_update');
                foreach ($elements as $element) {
                    if (osc_is_update_compatible('languages', $element, $json->s_name)) {
                        osc_market('languages', $element);
                    }
                }
            }
        }
    }
}


    /**
     * @param        $section
     * @param        $element
     * @param string $osclass_version
     *
     * @return bool
     */
function osc_is_update_compatible($section, $element, $osclass_version = OSCLASS_VERSION)
{
    if ($element != '') {
        $data = array();
        if (stripos($element, 'http://') === false && stripos($element, 'https://') === false) {
            // OSCLASS OFFICIAL REPOSITORY
            // $url  = osc_market_url( $section , $element );
            // $data = json_decode( osc_file_get_contents( $url , array ( 'api_key' => osc_market_api_connect() ) ) , true );
        } else {
            // THIRD PARTY REPOSITORY
            $data = json_decode(osc_file_get_contents($element), true);
        }
        if (isset($data['s_compatible'])) {
            $versions = explode(',', $data['s_compatible']);

            foreach ($versions as $_version) {
                $result = version_compare2($osclass_version, $_version);

                if ($result == 0 || $result == -1) {
                    return true;
                }
            }
        }
    }

    return false;
}


    /**
     * @param $section
     * @param $code
     *
     * @return array
     */
function osc_market($section, $code)
{
    $plugin             = false;
    $re_enable          = false;
    $message            = '';
    $data               = array();
    $download_post_data = array();
    /************************
     *** CHECK VALID CODE ***
     ************************/
    if ($code != '' && $section != '') {
        if (stripos($code, 'http://') === false && stripos($code, 'https://') === false) {
            // OSCLASS OFFICIAL REPOSITORY
            // $url  = osc_market_url( $section , $code );
            // $data = osc_file_get_contents( $url , array ( 'api_key' => osc_market_api_connect() ) );
            // data = json_decode( osc_file_get_contents( $url , array ( 'api_key' => osc_market_api_connect() ) ) , true );
        } else {
            // THIRD PARTY REPOSITORY
            $download_post_data = array();
            $data               = json_decode(osc_file_get_contents($code), true);
        }

        /***********************
         **** DOWNLOAD FILE ****
         ***********************/
        if (isset($data['s_update_url']) && isset($data['s_source_file']) && isset($data['e_type'])) {
            if ($data['e_type'] === 'THEME') {
                $folder = 'themes/';
            } elseif ($data['e_type'] === 'LANGUAGE') {
                $folder = 'languages/';
            } else { // PLUGINS
                $folder = 'plugins/';
                $plugin = Plugins::findByUpdateURI($data['s_update_url']);
                if ($plugin != false) {
                    if (Plugins::isEnabled($plugin)) {
                        Plugins::runHook($plugin . '_disable');
                        Plugins::deactivate($plugin);
                        $re_enable = true;
                    }
                }
            }

            $filename        = date('YmdHis') . '_' . osc_sanitize_string($data['s_title']) . '_' . $data['s_version'] . '.zip';
            $url_source_file = $data['s_source_file'];

            $result = osc_downloadFile($url_source_file, $filename, $download_post_data);

            if ($result) { // Everything is OK, continue
                /**********************
                 ***** UNZIP FILE *****
                 **********************/
                if (!mkdir($concurrentDirectory = osc_content_path() . 'downloads/oc-temp/') && !is_dir($concurrentDirectory)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }
                $res = osc_unzip_file(osc_content_path() . 'downloads/' . $filename, osc_content_path() . 'downloads/oc-temp/');
                if ($res == 1) { // Everything is OK, continue
                    /**********************
                     ***** COPY FILES *****
                     **********************/
                    $fail = -1;
                    if ($handle = opendir(osc_content_path() . 'downloads/oc-temp')) {
                        $folder_dest = ABS_PATH . 'oc-content/' . $folder;

                        if (function_exists('posix_getpwuid')) {
                            $current_user = posix_getpwuid(posix_geteuid());
                            $ownerFolder  = posix_getpwuid(fileowner($folder_dest));
                        }

                        $fail = 0;
                        while (false !== ($_file = readdir($handle))) {
                            if ($_file !== '.' && $_file !== '..') {
                                $copyprocess = osc_copy(osc_content_path() . 'downloads/oc-temp/' . $_file, $folder_dest . $_file);
                                if ($copyprocess == false) {
                                    $fail = 1;
                                }
                            }
                        }
                        closedir($handle);

                        // Additional actions is not important for the rest of the proccess
                        // We will inform the user of the problems but the upgrade could continue
                        // Also remove the zip package
                        /****************************
                         ** REMOVE TEMPORARY FILES **
                         ****************************/
                        @unlink(osc_content_path() . 'downloads/' . $filename);
                        $path      = osc_content_path() . 'downloads/oc-temp';
                        $rm_errors = 0;
                        $dir       = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);
                        for ($dir->rewind(); $dir->valid(); $dir->next()) {
                            if ($dir->isDir()) {
                                if ($dir->getFilename() !== '.' && $dir->getFilename() !== '..') {
                                    if (!rmdir($dir->getPathname())) {
                                        $rm_errors++;
                                    }
                                }
                            } else {
                                if (!unlink($dir->getPathname())) {
                                    $rm_errors++;
                                }
                            }
                        }

                        if (!rmdir($path)) {
                            $rm_errors++;
                        }

                        if ($fail == 0) { // Everything is OK, continue
                            if ($data['e_type'] !== 'THEME' && $data['e_type'] !== 'LANGUAGE') {
                                if ($plugin != false && $re_enable) {
                                    $enabled = Plugins::activate($plugin);
                                    if ($enabled) {
                                        Plugins::runHook($plugin . '_enable');
                                    }
                                }
                            } elseif ($data['e_type'] === 'LANGUAGE') {
                                osc_checkLocales();
                            }
                            // recount plugins&themes for update
                            if ($section === 'plugins') {
                                osc_check_plugins_update(true);
                            } elseif ($section === 'themes') {
                                osc_check_themes_update(true);
                            } elseif ($section === 'languages') {
                                osc_check_languages_update(true);
                            }

                            if ($rm_errors == 0) {
                                $message = __('Everything looks good!');
                                $error   = 0;
                            } else {
                                $message =
                                    __('Nearly everything looks good! but there were some errors removing temporary files. Please manually remove the \"oc-content/downloads/oc-temp\" folder');
                                $error   = 6; // Some errors removing files
                            }
                        } else {
                            $message = __('Problems when copying files. Please check your permissions. ');

                            if ($current_user['uid'] != $ownerFolder['uid']) {
                                if (function_exists('posix_getgrgid')) {
                                    $current_group = posix_getgrgid($current_user['gid']);
                                    $message       .= '<p><strong>' . sprintf(
                                        __('NOTE: Web user and destination folder user is not the same, you might have an issue there. <br/>Do this in your console:<br/>chown -R %s:%s %s'),
                                        $current_user['name'],
                                        $current_group['name'],
                                        $folder_dest
                                        ) . '</strong></p>';
                                }
                            }
                            $error = 4; // Problems copying files. Maybe permissions are not correct
                        }
                    } else {
                        $message = __('Nothing to copy');
                        $error   = 99; // Nothing to copy. THIS SHOULD NEVER HAPPEN, means we don't update any file!
                    }
                } else {
                    $message = __('Unzip failed');
                    $error   = 3; // Unzip failed
                }
            } else {
                $message = __('Download failed');
                $error   = 2; // Download failed
            }
        } else {
            if (isset($data['s_buy_url']) && isset($data['b_paid']) && $data['s_buy_url'] != '' && $data['b_paid'] == 0) {
                $message = __('This is a paid item, you need to buy it before you are able to download it');
                $error   = 8; // Item not paid
            } else {
                $message = __('Input code not valid');
                $error   = 7; // Input code not valid
            }
        }
    } else {
        $message = __('Missing download URL');
        $error   = 1; // Missing download URL
    }

    return array('error' => $error, 'message' => $message, 'data' => $data);
}


    /**
     * @return bool
     */
function osc_is_ssl()
{
    return ((isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        || (isset($_SERVER['HTTPS'])
            && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)));
}


if (!function_exists('hex2b64')) {
    /*
 * Used to encode a field for Amazon Auth
 * (taken from the Amazon S3 PHP example library)
 */
    /**
     * @param $str
     *
     * @return string
     */
    function hex2b64($str)
    {
        $raw = '';
        for ($i = 0, $iMax = strlen($str); $i < $iMax; $i += 2) {
            $raw .= chr(hexdec(substr($str, $i, 2)));
        }

        return base64_encode($raw);
    }
}

if (!function_exists('hmacsha1')) {
    /*
 * Calculate HMAC-SHA1 according to RFC2104
 * See http://www.faqs.org/rfcs/rfc2104.html
 */
    /**
     * @param $key
     * @param $data
     *
     * @return string
     */
    function hmacsha1($key, $data)
    {
        $blocksize = 64;
        $hashfunc  = 'sha1';
        if (strlen($key) > $blocksize) {
            $key = pack('H*', $hashfunc($key));
        }
        $key  = str_pad($key, $blocksize, chr(0x00));
        $ipad = str_repeat(chr(0x36), $blocksize);
        $opad = str_repeat(chr(0x5c), $blocksize);
        $hmac = pack(
            'H*',
            $hashfunc(
                ($key ^ $opad) . pack(
                    'H*',
                    $hashfunc(
                        ($key ^ $ipad) . $data
                    )
                )
            )
        );

        return bin2hex($hmac);
    }
}
