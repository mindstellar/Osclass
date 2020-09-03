<?php
/**
 * Created by Mindstellar Community.
 * User: navjottomer
 * Date: 07/05/20
 * Time: 4:49 PM
 * License is provided in root directory.
 */

namespace mindstellar\osclass\classes\upgrade;

use mindstellar\osclass\classes\utility\FileSystem;
use mindstellar\osclass\classes\utility\Zip;
use RuntimeException;

/**
 * Class upgrade
 *
 * @package mindstellar\osclass\classes
 */
class Upgrade
{
    /**
     * @var bool
     */
    private $packageInfoValid;

    /**
     * @var \mindstellar\osclass\classes\utility\Zip
     */
    private $Zip;

    /**
     * @var \mindstellar\osclass\classes\utility\FileSystem
     */
    private $FileSystem;

    /**
     * @var \mindstellar\osclass\classes\upgrade\UpgradePackage
     */
    private $objPackage;


    /**
     * Upgrade constructor.
     *
     * @param \mindstellar\osclass\classes\upgrade\UpgradePackage $packageObj
     */
    public function __construct(UpgradePackage $packageObj)
    {
        $this->objPackage = $packageObj;
        $this->validatePackageInfo();
        $this->Zip        = new Zip();
        $this->FileSystem = new FileSystem();
    }

    private function validatePackageInfo()
    {
        $this->packageInfoValid = false;
        if (is_array($this->objPackage->getFilteredFiles())
            && filter_var($this->objPackage->getSourceUrl(), FILTER_VALIDATE_URL)
        ) {
            $this->packageInfoValid = true;
        }
    }

    /**
     * Do an actual upgrade
     *
     * @throws \Exception
     */
    public function doUpgrade()
    {
        if ($this->packageInfoValid !== true) {
            throw new RuntimeException(sprintf(
                __('Unable to follow upgrade, invalid package info')
            ));
        }
        if ($this->objPackage->isCompatible() && $this->objPackage->isUpgradable()) {
            $this->processUpgrade();
        } else {
            throw new RuntimeException($this->objPackage->getTitle() . ' ' . 'is not compatible/upgradable.');
        }
    }

    /**
     * process package upgrade
     *
     * @throws \Exception
     */
    private function processUpgrade()
    {
        if ($extracted_package_path = $this->downloadPackageAndExtract()
        ) {
            // Enable maintenance mode
            $this->FileSystem->touch(ABS_PATH . '.maintenance');

            $originDir = $extracted_package_path . '/' . $this->objPackage->getShortName();
            if ($this->FileSystem->exists($originDir)) {
                $this->FileSystem->sync(
                    $extracted_package_path . '/' . $this->objPackage->getShortName(),
                    $this->objPackage->getTargetDirectory(),
                    null,
                    $this->objPackage->getFilteredFiles() //Don't overwrite these files or directory while upgrading
                );
            } else {
                throw new RuntimeException(
                    $originDir . ' '
                    . __("doesn't exists, unknown error occurred while downloading and extracting package.")
                );
            }

            $this->FileSystem->remove($extracted_package_path);

            $this->objPackage->afterProcessUpgrade();

            // Disable maintenance mode
            $this->FileSystem->remove(ABS_PATH . '.maintenance');
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
        $download_path   = CONTENT_PATH . 'downloads/';
        if ($downloaded = $this->FileSystem->downloadFile(
            $this->objPackage->getSourceUrl(),
            $download_path . $unique_filename
        )
        ) {
            $resultCode = $this->Zip->unzipFile($downloaded, $download_path . $unique_id);
            if ($resultCode === 1) {
                $this->FileSystem->remove($download_path . $unique_filename);

                return $download_path . $unique_id;
            }
            $this->FileSystem->remove($download_path . $unique_filename);
            throw new RuntimeException(__('Unable to unzip package file.'));
        }

        return false;
    }
}
