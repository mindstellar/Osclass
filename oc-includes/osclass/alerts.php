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
 * @param null $type
 * @param null $last_exec
 */
function osc_runAlert($type = null, $last_exec = null)
{
    $mUser = User::newInstance();
    if (!in_array($type, array('HOURLY', 'DAILY', 'WEEKLY', 'INSTANT'))) {
        return;
    }

    if ($last_exec == null) {
        $cron      = Cron::newInstance()->getCronByType($type);
        $last_exec = '1000-01-01 00:00:00';
        if (is_array($cron)) {
            $last_exec = $cron['d_last_exec'];
        }
    }

    $internal_name = 'alert_email_hourly';
    switch ($type) {
        case 'HOURLY':
            $internal_name = 'alert_email_hourly';
            break;
        case 'DAILY':
            $internal_name = 'alert_email_daily';
            break;
        case 'WEEKLY':
            $internal_name = 'alert_email_weekly';
            break;
        case 'INSTANT':
            $internal_name = 'alert_email_instant';
            break;
    }

    $active   = true;
    $searches = Alerts::newInstance()->findByTypeGroup($type, $active);


    foreach ($searches as $s_search) {
        // Get if there're new ads on this search
        $json             = $s_search['s_search'];
        $array_conditions = json_decode($json, true);

        $new_search = Search::newInstance();
        $new_search->setJsonAlert($array_conditions);

        $new_search->addConditions(sprintf(" %st_item.dt_pub_date > '%s' ", DB_TABLE_PREFIX, $last_exec));

        $items      = $new_search->doSearch();
        $totalItems = $new_search->count();

        if (count($items) > 0) {
            // If we have new items from last check
            // Catch the user subscribed to this search
            $alerts = Alerts::newInstance()->findUsersBySearchAndType($s_search['s_search'], $type, $active);

            if (count($alerts) > 0) {
                $ads = '';
                foreach ($items as $item) {
                    $ads .= '<a href="' . osc_item_url_ns($item['pk_i_id']) . '">' . $item['s_title'] . '</a><br/>';
                }

                foreach ($alerts as $alert) {
                    $user = array();
                    if ($alert['fk_i_user_id'] != 0) {
                        $user = $mUser->findByPrimaryKey($alert['fk_i_user_id']);
                    }
                    if (!isset($user['s_name'])) {
                        $user = array(
                            's_name'  => $alert['s_email'],
                            's_email' => $alert['s_email']
                        );
                    }
                    if (count($alert) > 0) {
                        osc_run_hook('hook_' . $internal_name, $user, $ads, $alert, $items, $totalItems);
                        AlertsStats::newInstance()->increase(date('Y-m-d'));
                    }
                }
            }
        }
    }
}
