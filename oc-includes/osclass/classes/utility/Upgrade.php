<?php
/**
 * Created by Mindstellar Community.
 * User: navjottomer
 * Date: 07/05/20
 * Time: 4:49 PM
 * License is provided in root directory.
 */

namespace mindstellar\osclass\classes\utility;

use RuntimeException;
use Session;

/**
 * Class upgrade
 *
 * @package mindstellar\osclass\classes
 */
class Upgrade
{
    private $package_info_valid = false;
    /**
     * @var string package download url.
     */
    private $package_download_url;
    /**
     * @var string package is theme or plugin, default is self for osclass
     */
    private $package_type;
    /**
     * @var string new version of theme, plugin or osclass
     */
    private $package_new_version;
    /**
     * @var string last compatible version of theme, plugin or osclass
     */
    private $package_compatible_version;
    /**
     * @var string last current version of theme, plugin or osclass
     */
    private $package_current_version;
    /**
     * @var string name of theme or plugin
     */
    private $package_name;
    /**
     * @var string package author name
     */
    private $package_author;
    /**
     * @var string directory name of theme or plugin
     */
    private $package_directory_name;
    /**
     * @var \mindstellar\osclass\classes\utility\FileSystem
     */
    private $FileSystem;
    /**
     * @var \mindstellar\osclass\classes\utility\Zip
     */
    private $Zip;

    /**
     * upgrade constructor.
     *
     * @param string $package_type accepted values are 'theme', 'plugin' default is 'self'
     * @param array  $package_info required package info array
     *                             $package_info['name'],
     *                             $package_info['author'],
     *                             $package_info['directory_name'],
     *                             $package_info['compatible_version'],
     *                             $package_info['download_url'],
     *                             $package_info['new_version'];
     *                             $package_info['current_version'];
     *
     */
    public function __construct($package_type = 'self', $package_info = null)
    {
        $this->package_type = $package_type;
        $this->Zip          = new Zip();
        $this->FileSystem   = new FileSystem();
        if ($package_type !== 'self' && $package_info !== null) {
            if (isset($package_info['name'])) {
                $this->package_name = $package_info['name'];
            }
            if (isset($package_info['author'])) {
                $this->package_author = $package_info['author'];
            }
            if (isset($package_info['directory_name'])) {
                $this->package_directory_name = $package_info['directory_name'];
            }
            if (isset($package_info['compatible_version'])) {
                $this->package_compatible_version = $package_info['compatible_version'];
            }
            if (isset($package_info['download_url'])) {
                $this->package_download_url = $package_info['download_url'];
            }
            if (isset($package_info['new_version'])) {
                $this->package_new_version = $package_info['new_version'];
            }
            if (isset($package_info['current_version'])) {
                $this->package_current_version = $package_info['current_version'];
            }

            if ($this->package_name !== null
                || $this->package_author !== null
                || $this->package_directory_name !== null
                || $this->package_compatible_version !== null
                || $this->package_download_url !== null
                || $this->package_new_version !== null
                || $this->package_current_version !== null
            ) {
                $this->package_info_valid = true;
            }
        } elseif ($package_type === 'self') {
            $this->prepareSelfPackageInfo();
        }
    }

    /**
     * Prepare package info data for Osclass.
     */
    private function prepareSelfPackageInfo()
    {
        $json_url               = 'https://api.github.com/repos/mindstellar/osclass/releases/latest';
        $self_package_info_json = $this->FileSystem->getContents($json_url);
        $aSelf_package          = json_decode($self_package_info_json, true);
        if (!$aSelf_package['draft'] && !$aSelf_package['prerelease']) {
            if (isset($aSelf_package['name'])) {
                $this->package_name = $aSelf_package['name'];
            }
            $this->package_author = 'mindstellar';

            $this->package_compatible_version = '3.8.0';

            if (isset($aSelf_package['assets'])) {
                $download_url               = $aSelf_package['assets'][0]['browser_download_url'];
                $this->package_download_url = $download_url;
            }
            if (isset($aSelf_package['version'])) {
                $this->package_new_version = str_replace('v', '', $aSelf_package['tag_name']);
            }
            $this->package_current_version = OSCLASS_VERSION;
            $this->package_directory_name  = 'osclass';
            $this->package_info_valid      = true;
        }
    }

    /**
     * @throws \Exception
     */
    public function doUpgrade()
    {
        if ($this->package_info_valid === false) {
            throw new RuntimeException(sprintf(
                __('Unable to follow %s upgrade, invalid package info'),
                $this->package_type
            ));
        }
        switch ($this->package_type) {
            case ('theme'):
                $this->upgradeTheme();
                break;
            case ('plugin'):
                $this->upgradePlugin();
                break;
            default:
                $this->upgradeSelf();
                break;
        }
    }

    private function upgradeTheme()
    {
        //TODO//
    }

    private function upgradePlugin()
    {
        //TODO//
    }

    /**
     * Upgrade osclass
     * @throws \Exception
     */
    private function upgradeSelf()
    {
        //Check current version is not lower than compatible version
        $is_compatible = Utils::versionCompare($this->package_compatible_version, $this->package_current_version);
        //Check current version is not higher than new version
        $is_upgradable = Utils::versionCompare($this->package_current_version, $this->package_new_version);
        // echo $is_compatible.' '.$is_upgradable;
        if (($is_compatible !== -1) && $is_upgradable !== -1
            && $extracted_package_path = $this->downloadPackageAndExtract()
        ) {
            $this->FileSystem->sync(
                $extracted_package_path . '/' . $this->package_directory_name,
                ABS_PATH,
                null,
                ['oc-content'] //Don't overwrite 'oc-content' directory while upgrading Osclass
            );
            $this->FileSystem->remove($extracted_package_path);
        }
    }

    /**
     * Download and extract upgrade package
     *
     * @return bool|string return extracted package path on success or false on failure.
     * @throws \Exception
     */
    private function downloadPackageAndExtract()
    {
        $unique_id       = $this->FileSystem->generateUniqueId('package_');
        $unique_filename = $unique_id . '.zip';
        $download_path   = osc_content_path() . 'downloads' . DIRECTORY_SEPARATOR;
        if ($downloaded = $this->FileSystem->downloadFile(
            $this->package_download_url,
            $download_path . $unique_filename
        )
        ) {
            $resultCode = $this->Zip->unzipFile($downloaded, $download_path . $unique_id);
            if ($resultCode === 1) {
                $this->FileSystem->remove($download_path . $unique_filename);

                return $download_path . $unique_id;
            }
            $this->FileSystem->remove($download_path . $unique_filename);
        }

        return false;
    }

    /**
     * Check if upgrade is available, by default it check Osclass upgrade availability.
     *
     * @return bool
     */
    public function isUpgradeAvailable()
    {
        if ($this->package_info_valid === true) {
            return Utils::versionCompare($this->package_current_version, $this->package_new_version, 'lt');
        }

        return false;
    }
}
