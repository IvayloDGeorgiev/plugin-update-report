<?php 
define('PLUGIN_PATH', __DIR__);
/**
 * Main Plugin Update Reports page
 */
function plugin_update_report_stats_page() {
    $default_title = get_option( 'plugin_update_report_default_title' );
    if (!$default_title) {
        $default_title = get_bloginfo('name') . ' ' . __('Site Report','plugin-update-report');
    }
	?>
        <div class="plugin-update-report-stats-screen">
            <div class="plugin-update-report-header">
                <h1><?php _e('Plugin Update Reports','plugin-update-report'); ?></h1>
                <div class="plugin-update-report-date-chooser-area d-flex">

                    <form target="_blank" action="/wp-admin/index.php" method="get" data-generate-pdf-form>
                        <input type="text" name="page" value="plugin_update_report" hidden>
                        <input type="text" name="action" value="generatePDF" hidden>
                        <input type="text" name="dateFrom" value="" hidden>
                        <input type="text" name="dateTo" value="" hidden>
                        <input id="generatePDF" type="submit" name="submit" class="button button-primary" value="Generate PDF">
                    </form>

                    <button id="plugin-update-report-date-chooser-button" class="button button-primary plugin-update-report-date-chooser-button"><span id="plugin-update-report-button-label"><?php _e('Today','plugin-update-report'); ?></span> <span class="dashicons dashicons-arrow-down"></span></button><!-- #plugin-update-report-date-chooser-menu -->

                    <div id="plugin-update-report-date-chooser" style="display:none;">
                        <div class="date-chooser-presets">
                            <ul>
                                <li><a href="#" id="plugin-update-report-quick-today"><?php _e('Today','plugin-update-report'); ?></a></li>
                                <li><a href="#" id="plugin-update-report-quick-thismonth"><?php _e('This Month','plugin-update-report'); ?></a></li>
                            </ul>
                        </div>
                        <div id="date-range"></div>
                        <div class="date-chooser-footer">
                            <span class="plugin-update-report-dates"><span id="plugin-update-report-start-date"></span> - <span id="plugin-update-report-end-date"></span></span> <button class="button" id="plugin-update-report-cancel"><?php _e('Cancel','plugin-update-report'); ?></button> <button class="button button-primary" id="plugin-update-report-apply"><?php _e('Apply','plugin-update-report'); ?></button>
                        </div><!-- .date-chooser-footer -->
                        <input type="hidden" id="from_value" class="from_value" name="from_value"/>
                        <input type="hidden" id="to_value"  class="to_value" name="to_value"/>
                    </div><!-- #plugin-update-report-date-chooser -->
                </div><!-- .plugin-update-report-date-chooser-area -->
            </div><!-- .plugin-update-report-header -->

            <?php do_action('plugin_update_report_stats'); ?>
        </div><!-- .plugin-update-report-stats-screen -->
	<?php

} plugin_update_report_stats_page();

/**
 * Plugin Updates section
 */
function plugin_update_report_stats_page_updates() {
    ?>
    <div id="pdf-content" class="metabox-holder">
        <div class="postbox plugin-update-report-postbox loading" id="plugin-update-report-updates">
            <div class="postbox-header">
                <h2 class="hndle"><?php _e('Plugin Updates','plugin-update-report'); ?></h2>
            </div>
            <div class="inside">
                <div class="main">
                    <!-- Successful Updates -->
                    <?php include PLUGIN_PATH . "/elements/successful/successful.php"; ?>
                    
                    <!-- Unsuccesful Updates -->
                    <?php include  PLUGIN_PATH . "/elements/unsuccessful/unsuccessful.php"; ?>
                </div><!-- .inside -->
            </div><!-- .main -->
        </div><!-- .postbox -->
    </div><!-- .metabox-holder -->
    <?php
}
