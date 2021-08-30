<?php
/*
 * Osclass - software for creating and publishing online classified advertising platforms
 * Maintained and supported by Mindstellar Community
 * https://github.com/mindstellar/Osclass
 * Copyright (c) 2021.  Mindstellar
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *                     GNU GENERAL PUBLIC LICENSE
 *                        Version 3, 29 June 2007
 *
 *  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 *  Everyone is permitted to copy and distribute verbatim copies
 *  of this license document, but changing it is not allowed.
 *
 *  You should have received a copy of the GNU Affero General Public
 *  License along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Created by Mindstellar Community.
 * User: navjottomer
 * Date: 07/05/20
 * Time: 4:49 PM
 * License is provided in root directory.
 */

namespace mindstellar\upgrade;

use mindstellar\utility\FileSystem;
use mindstellar\utility\Zip;
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
     * @var \mindstellar\utility\Zip
     */
    private $Zip;

    /**
     * @var \mindstellar\utility\FileSystem
     */
    private $FileSystem;

    /**
     * @var \mindstellar\upgrade\UpgradePackage
     */
    private $objPackage;


    /**
     * Upgrade constructor.
     *
     * @param \mindstellar\upgrade\UpgradePackage $packageObj
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
            throw new RuntimeException(__('Unable to follow upgrade, invalid package info'));
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

            if (file_exists($extracted_package_path . '/index.php')) {
                //make this the origin directory
                $originDir = $extracted_package_path;
            } elseif (file_exists($extracted_package_path . '/' . $this->objPackage->getShortName())
                      && file_exists($extracted_package_path . '/' . $this->objPackage->getShortName().'/index.php')) {
                $originDir = $extracted_package_path . '/' . $this->objPackage->getShortName();
            } else {
                throw new RuntimeException(
                    __("Invalid Zip package, it's not in valid format.")
                );
            }

            if ($this->FileSystem->exists($originDir)) {
                $this->FileSystem->sync(
                    $originDir . '/',
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
            $this->FileSystem->remove($download_path . $unique_filename);
            if ($resultCode === 1) {
                return $download_path . $unique_id;
            }
            throw new RuntimeException(__('Unable to unzip package file.'));
        }

        return false;
    }
}
