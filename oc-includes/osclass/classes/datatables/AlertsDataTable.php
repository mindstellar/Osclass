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
 * AlertsDataTable class
 *
 * @since      3.1
 * @package    Osclass
 * @subpackage classes
 * @author     Osclass
 */
class AlertsDataTable extends DataTable
{

    private $search;
    private $order_by;
    private $total_filtered;

    /**
     * @param $params
     *
     * @return array
     */
    public function table($params)
    {

        $this->addTableHeader();
        $this->getDBParams($params);

        $alerts = Alerts::newInstance()
            ->search(
                $this->start,
                $this->limit,
                $this->order_by['column_name'],
                $this->order_by['type'],
                $this->search
            );
        $this->processData($alerts);
        $this->total          = $alerts['rows'];
        $this->total_filtered = $alerts['total_results'];
        $this->totalFiltered  = $alerts['total_results'];

        return $this->getData();
    }

    private function addTableHeader()
    {

        $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
        $this->addColumn('email', __('E-mail'));
        $this->addColumn('alert', __('Alert'));
        $this->addColumn('date', __('Date'));

        $dummy = &$this;
        osc_run_hook('admin_alerts_table', $dummy);
    }

    /**
     * @param $_get
     */
    private function getDBParams($_get)
    {


        $column_names = array(
            0 => 'dt_date',
            1 => 's_email',
            2 => 's_search',
            3 => 'dt_date'
        );

        $this->order_by['column_name'] = 'c.dt_pub_date';
        $this->order_by['type']        = 'desc';

        if (!isset($_get['iDisplayStart'])) {
            $_get['iDisplayStart'] = 0;
        }
        $p_iPage = 1;
        if (!is_numeric(Params::getParam('iPage')) || Params::getParam('iPage') < 1) {
            Params::setParam('iPage', $p_iPage);
            $this->iPage = $p_iPage;
        } else {
            $this->iPage = Params::getParam('iPage');
        }

        $this->order_by['column_name'] = 'dt_date';
        $this->order_by['type']        = 'DESC';
        foreach ($_get as $k => $v) {
            if ($k === 'sSearch') {
                $this->search = $v;
            }

            /* for sorting */
            if ($k === 'iSortCol_0') {
                $this->order_by['column_name'] = $column_names[$v];
            }
            if ($k === 'sSortDir_0') {
                $this->order_by['type'] = $v;
            }
        }
        // set start and limit using iPage param
        $start = ($this->iPage - 1) * $_get['iDisplayLength'];

        $this->start = (int)$start;
        $this->limit = (int)$_get['iDisplayLength'];
    }

    /**
     * @param $alerts
     */
    private function processData($alerts)
    {
        if (!empty($alerts) && !empty($alerts['alerts'])) {
            $csrf_token_url = osc_csrf_token_url();
            foreach ($alerts['alerts'] as $aRow) {
                $row     = array();
                $options = array();
                // first column
                $row['bulkactions'] =
                    '<input type="checkbox" name="alert_id[]" value="' . $aRow['pk_i_id'] . '" /></div>';

                $options[] =
                    '<a onclick="return delete_alert(\'' . $aRow['pk_i_id'] . '\');" href="#">' . __('Delete') . '</a>';


                if ($aRow['b_active'] == 1) {
                    $options[] =
                        '<a href="' . osc_admin_base_url(true) . '?page=users&action=status_alerts&amp;alert_id[]='
                        . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;status=0" >' . __('Deactivate') . '</a>';
                } else {
                    $options[] =
                        '<a href="' . osc_admin_base_url(true) . '?page=users&action=status_alerts&amp;alert_id[]='
                        . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;status=1" >' . __('Activate') . '</a>';
                }


                $options = osc_apply_filter('actions_manage_alerts', $options, $aRow);
                // create list of actions
                $auxOptions = '<ul>' . PHP_EOL;
                foreach ($options as $actual) {
                    $auxOptions .= '<li>' . $actual . '</li>' . PHP_EOL;
                }
                $auxOptions .= '</ul>' . PHP_EOL;

                $actions = '<div class="actions">' . $auxOptions . '</div>' . PHP_EOL;
                // second column
                $row['email'] =
                    '<a href="' . osc_admin_base_url(true) . '?page=items&userId=">' . $aRow['s_email'] . '</a>'
                    . $actions;

                // third row

                $pieces     = array();
                $conditions = osc_get_raw_search((array)json_decode($aRow['s_search'], true));
                if (isset($conditions['sPattern']) && $conditions['sPattern'] != '') {
                    $pieces[] = sprintf(__('<b>Pattern:</b> %s'), $conditions['sPattern']);
                }
                if (isset($conditions['aCategories']) && !empty($conditions['aCategories'])) {
                    $l         = min(count($conditions['aCategories']), 4);
                    $cat_array = array();
                    for ($c = 0; $c < $l; $c++) {
                        $cat_array[] = $conditions['aCategories'][$c];
                    }
                    if (count($conditions['aCategories']) > $l) {
                        $cat_array[] = '<a href="#" class="more-tooltip" categories="' . osc_esc_html(implode(
                                ', ',
                                $conditions['aCategories']
                            )) . '" >' . __('...More') . '</a>';
                    }

                    $pieces[] = sprintf(__('<b>Categories:</b> %s'), implode(', ', $cat_array));
                }

                $row['alert'] = implode(', ', $pieces);
                // fourth row
                $row['date'] = osc_format_date($aRow['dt_date']);

                $row = osc_apply_filter('alerts_processing_row', $row, $aRow);

                $this->addRow($row);
                $this->rawRows[] = $aRow;
            }
        }
    }
}
