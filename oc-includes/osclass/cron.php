<?php if (!defined('ABS_PATH')) {
    exit('ABS_PATH is not loaded. Direct access is not allowed.');
}

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

$shift_seconds   = 60;
$d_now           = date('Y-m-d H:i:s');
$i_now           = strtotime($d_now);
$i_now_truncated = strtotime(date('Y-m-d H:i:00'));
if (!defined('CLI')) {
    define('CLI', PHP_SAPI === 'cli');
}

// Hourly crons
$cron = Cron::newInstance()->getCronByType('HOURLY');
if (is_array($cron)) {
    $i_next = strtotime($cron['d_next_exec']);

    if ((CLI && (Params::getParam('cron-type') === 'hourly')) || ((($i_now - $i_next + $shift_seconds) >= 0) && !CLI)) {
        // update the next execution time in t_cron
        $d_next = date('Y-m-d H:i:s', $i_now_truncated + 3600);
        Cron::newInstance()->update(
            array('d_last_exec' => $d_now, 'd_next_exec' => $d_next),
            array('e_type' => 'HOURLY')
        );

        osc_runAlert('HOURLY', $cron['d_last_exec']);

        // Run cron AFTER updating the next execution time to avoid double run of cron
        $purge = osc_purge_latest_searches();
        if ($purge === 'hour') {
            LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', time() - 3600));
        } elseif (!in_array($purge, array('forever', 'day', 'week'))) {
            LatestSearches::newInstance()->purgeNumber($purge);
        }
        osc_update_location_stats(true, 'auto');

        // WARN EXPIRATION EACH HOUR (COMMENT TO DISABLE)
        // NOTE: IF THIS IS ENABLE, SAME CODE SHOULD BE DISABLE ON CRON DAILY
        if (is_numeric(osc_warn_expiration()) && osc_warn_expiration() > 0) {
            $items = Item::newInstance()->findByHourExpiration(24 * osc_warn_expiration());
            foreach ($items as $item) {
                osc_run_hook('hook_email_warn_expiration', $item);
            }
        }

        $qqprefixes = array('qqfile_*', 'auto_qqfile_*');
        foreach ($qqprefixes as $qqprefix) {
            $qqfiles = glob(osc_content_path() . 'uploads/temp/' . $qqprefix);
            if (is_array($qqfiles)) {
                foreach ($qqfiles as $qqfile) {
                    if ((time() - filemtime($qqfile)) > (2 * 3600)) {
                        @unlink($qqfile);
                    }
                }
            }
        }

        osc_run_hook('cron_hourly');
    }
}

// Daily cron
$cron = Cron::newInstance()->getCronByType('DAILY');
if (is_array($cron)) {
    $i_next = strtotime($cron['d_next_exec']);

    if ((CLI && (Params::getParam('cron-type') === 'daily')) || ((($i_now - $i_next + $shift_seconds) >= 0) && !CLI)) {
        // update the next execution time in t_cron
        $d_next = date('Y-m-d H:i:s', $i_now_truncated + (24 * 3600));
        Cron::newInstance()->update(
            array('d_last_exec' => $d_now, 'd_next_exec' => $d_next),
            array('e_type' => 'DAILY')
        );


        //osc_do_auto_upgrade();

        osc_runAlert('DAILY', $cron['d_last_exec']);

        // Run cron AFTER updating the next execution time to avoid double run of cron
        $purge = osc_purge_latest_searches();
        if ($purge === 'day') {
            LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', time() - (24 * 3600)));
        }
        osc_update_cat_stats();

        // WARN EXPIRATION EACH DAY (UNCOMMENT TO ENABLE)
        // NOTE: IF THIS IS ENABLE, SAME CODE SHOULD BE DISABLE ON CRON HOURLY
        /*if(is_numeric(osc_warn_expiration()) && osc_warn_expiration()>0) {
            $items = Item::newInstance()->findByDayExpiration(osc_warn_expiration());
            foreach($items as $item) {
                osc_run_hook('hook_email_warn_expiration', $item);
            }
        }*/

        osc_run_hook('cron_daily');
    }
}

// Weekly cron
$cron = Cron::newInstance()->getCronByType('WEEKLY');
if (is_array($cron)) {
    $i_next = strtotime($cron['d_next_exec']);

    if ((CLI && (Params::getParam('cron-type') === 'weekly')) || ((($i_now - $i_next + $shift_seconds) >= 0) && !CLI)) {
        // update the next execution time in t_cron
        $d_next = date('Y-m-d H:i:s', $i_now_truncated + (7 * 24 * 3600));
        Cron::newInstance()->update(
            array('d_last_exec' => $d_now, 'd_next_exec' => $d_next),
            array('e_type' => 'WEEKLY')
        );

        osc_runAlert('WEEKLY', $cron['d_last_exec']);

        // Run cron AFTER updating the next execution time to avoid double run of cron
        $purge = osc_purge_latest_searches();
        if ($purge === 'week') {
            LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', time() - (7 * 24 * 3600)));
        }
        osc_run_hook('cron_weekly');
    }
}

osc_run_hook('cron');
