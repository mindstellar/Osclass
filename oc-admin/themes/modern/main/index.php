<?php if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
}
/*
 *  Osclass
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

$numItemsPerCategory = __get('numItemsPerCategory');
$numItems            = __get('numItems');
$numUsers            = __get('numUsers');
$aFeatured           = __get('aFeatured');
osc_add_filter('render-wrapper', 'render_offset');
/**
 * @return string
 */
function render_offset()
{
    return 'row-offset';
}


osc_add_filter('admin_body_class', 'addBodyClass');
if (!function_exists('addBodyClass')) {
    /**
     * @param $array
     *
     * @return array
     */
    function addBodyClass($array)
    {
        $array[] = 'dashboard';

        return $array;
    }
}

osc_add_hook('admin_page_header', 'customPageHeader');
function customPageHeader()
{
    ?>
    <h1><?php _e('Dashboard'); ?></h1>
    <?php
}


/**
 * @param $string
 *
 * @return string
 */
function customPageTitle($string)
{
    return sprintf(__('Dashboard &raquo; %s'), $string);
}


osc_add_filter('admin_title', 'customPageTitle');

function chartJs()
{
    $items = __get('item_stats');
    $users = __get('user_stats');
    ?>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('1', {
            'packages': ['corechart']
        });
        google.charts.setOnLoadCallback(drawChartListing);
        //google.charts.setOnLoadCallback(drawChartUser);
        function drawChartListing() {
            var data = google.visualization.arrayToDataTable([
                ['<?php _e('Date'); ?>', '<?php _e('Listings'); ?>', '<?php _e('Users'); ?>'],
                <?php foreach ($items as $k => $v) {
                    echo "['" . $k . "', $v , $users[$k]],";
                } ?>
            ]);
            var options = {
                title: '<?php _e('New listings/users'); ?>',
                titleTextStyle: {
                    color: '#444444',
                    fontSize: 16,
                    bold : false
                },
                hAxis: {
                    titleTextStyle: {
                        color: '#333'
                    },
                    slantedText: false,
                },
                vAxis: {
                    minValue: 0
                },
                legend: {
                    position: 'bottom',
                    alignment: 'center',
                },
                colors: ['#03dffc', '#035afc'],
                chartArea: {
                    top:20,
                    width: '95%',
                    height: '80%'
                },
                animation: {
                    "startup": true,
                    duration: 250,
                    easing: 'out',
                           
                }
            };

            var chart = new google.visualization.AreaChart(document.getElementById('placeholder-listing'));
            chart.draw(data, options);
            var windowResizeTimer;
            window.addEventListener('resize', function(e) {
                clearTimeout(windowResizeTimer);
                windowResizeTimer = setTimeout(function() {
                    chart.draw(data, options);
                }, 750);
            });
        }
    </script>
    <?php
}


osc_add_hook('admin_footer', 'chartJs', 10);

osc_current_admin_theme_path('parts/header.php'); ?>
<div id="dashboard">
    <div class="row g-1">
        <div class="col-lg-4 col-md-6">
            <div class="widget-box h-100">
                <div class="widget-box-title">
                    <h3><?php _e('Listings by category'); ?></h3>
                </div>
                <div class="widget-box-content">
                    <?php
                    $countEvent = 1;
                    if (!empty($numItemsPerCategory)) { ?>
                        <table class="table" cellpadding="0" cellspacing="0">
                            <tbody>
                                <?php
                                $even = false;
                                foreach ($numItemsPerCategory as $c) { ?>
                                    <tr<?php if ($even == true) {
                                            $even = false;
                                            echo ' class="even"';
                                       } else {
                                           $even = true;
                                       }
                                       if ($countEvent == 1) {
                                           echo ' class="table-first-row"';
                                       } ?>>
                                        <td><a href="<?php echo osc_admin_base_url(true); ?>?page=items&amp;catId=<?php echo
                                                                                                                    $c['pk_i_id']; ?>"><?php echo $c['s_name']; ?></a>
                                        </td>
                                        <td><?php echo $c['i_num_items'] . '&nbsp;' . (($c['i_num_items'] == 1)
                                                ? __('Listing') : __('Listings')); ?></td>
                                        </tr>
                                        <?php foreach ($c['categories'] as $subc) { ?>
                                            <tr<?php if ($even == true) {
                                                    $even = false;
                                                    echo ' class="even"';
                                               } else {
                                                   $even = true;
                                               } ?>>
                                                <td class="children-cat"><a href="<?php echo osc_admin_base_url(true); ?>?page=items&amp;
                                            catId=<?php echo $subc['pk_i_id']; ?>"><?php echo $subc['s_name']; ?></a>
                                                </td>
                                                <td><?php echo $subc['i_num_items'] . ' ' . (($subc['i_num_items'] == 1)
                                                        ? __('Listing') : __('Listings')); ?></td>
                                                </tr>
                                            <?php
                                            $countEvent++;
                                        }
                                        ?>
                                        <?php
                                        $countEvent++;
                                }
                                ?>
                            </tbody>
                        </table>
                    <?php } else { ?>
                        <?php _e("There aren't any uploaded listing yet"); ?>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="widget-box">
                <div class="widget-box-title">
                    <h3><?php _e('Statistics'); ?></h3>
                </div>
                <div class="widget-box-content widget-box-content-stats">
                    <div id="widget-box-stats-listings" class="widget-box-stats">
                        <div class="stats-detail"><?php printf(
                                                        __('Total number of listings: %s'),
                                                        $numItems
                                                    ); ?></div>
                        <div class="stats-detail"><?php printf(
                                                        __('Total number of users: %s'),
                                                        $numUsers
                                                    ); ?></div>
                        <div id="placeholder-listing" class="graph-placeholder" height="120"></div>
                        <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=items" class="btn btn-sm btn-dim"><?php _e('Listing statistics'); ?></a>
                        <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=users" class="btn btn-sm btn-dim"><?php _e('User statistics'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php osc_current_admin_theme_path('parts/footer.php'); ?>