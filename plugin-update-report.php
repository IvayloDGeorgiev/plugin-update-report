<?php

/**
* Plugin Name: Plugin Update Report Generator
* Plugin URI: ivo-georigev.com
* Description: This plugin generates a pdf of all the plugins that are updated
* Version: 1.0.0
* Author: Ivaylo Georgiev
* Author URI: https://www.linkedin.com/in/ivogeorgiev404
*/

define('PLUGIN_PATH', __DIR__);
define('PLUGIN_URL', site_url());
define( 'PLUGIN_UPDATE_REPORT_VERSION', '1.0' );

if (! class_exists('Plugin_Update_Report_Generator')) {
    class Plugin_Update_Report_Generator {
        //CONSTRUCTOR
        public function __construct() {
            add_action('admin_menu', array( &$this, 'list_generator_menu'));
            add_action('init', array( &$this, 'register_session'));
            add_action('init', [$this, 'generate_pdf_action']);

            $plugin = plugin_basename(__FILE__);
            add_filter("plugin_action_links_$plugin", array( &$this, 'settings_link' ));

            $this->create_plugin_database_table();
        }
        //Function to generate PDF when Button is pressed
        public function generate_pdf_action()
        {
            if (
                $_GET['action'] == 'generatePDF' && 
                $_GET['page'] == 'plugin_update_report'
            ) {
                include (__DIR__ . '/pdf/pdf.php');
                exit;
            }
        }
        //Functin to Generate the Scripts
        public function load_list_generator_scripts() {

            add_action('admin_enqueue_scripts', array(&$this, 'enqueue_list_generator_scripts' ));
        }

        public function get_menu_slug() {
            $menu_slug = 'plugin-update-report-generator';
            return $menu_slug;
        }

        public function settings_link($links) {
            $menu_slug = $this->get_menu_slug();
            $settings_link = '<a href="admin.php?page='
            . $menu_slug . '">' . __( 'Settings' ) . '</a>';

            array_push($links, $settings_link);
            return $links;
        }
        //SCRIPTS AND STYLES
        public function enqueue_list_generator_scripts() {
             //Styles
             wp_enqueue_style( 'css', plugin_dir_url( __FILE__ ) . '/css/plugin-update-report.css', array(), '1' );

             //Scripts
             wp_enqueue_script( 'script-js', plugin_dir_url( __FILE__ ) . 'js/script.js', array(), '1', true );
             wp_enqueue_script( 'plugin-update-report-js', plugin_dir_url( __FILE__ ) . 'js/plugin-update-report.js', array(), '1', true );
 
             wp_enqueue_script( 'jquery-ui-datepicker' );
             wp_enqueue_script( 'moment-js', plugin_dir_url( __FILE__ ) . 'js/moment.js', array(), '2.29.2', true );
             wp_enqueue_script('thickbox');
             wp_enqueue_style( 'thickbox' );
     
             wp_register_script( 'plugin-update-report1-js', plugin_dir_url( __FILE__ ) . 'js/plugin-update-report.js', array('jquery','jquery-ui-datepicker'), PLUGIN_UPDATE_REPORT_VERSION, true );
             $date_format = 'd/m/Y';
             $js_data = array(
                 'moment_date_format' => plugin_update_report_convert_date_format($date_format),
                 'nopluginupdates' => __('No Plugin Updates','plugin-update-report')
             );
             wp_localize_script( 'plugin-update-report-js', 'plugin_update_report_data', $js_data );
             wp_enqueue_script( 'plugin-update-report-js' );
        }

        public function list_generator_menu() {

            $page_title = 'Plugin Update Report Generator';
            $menu_title = 'Plugin Update Report';
            $capability = 'manage_options';
            $menu_slug = $this->get_menu_slug();
            $function = 'welcome_page';
            $icon_url = 'dashicons-media-spreadsheet';
            $position = 5;

            $menu = add_menu_page($page_title, $menu_title, $capability, $menu_slug, array(&$this, $function), $icon_url, $position);

            // Enqueue scripts and styles conditionally
            add_action("load-$menu", array(&$this , 'load_list_generator_scripts'));
        }

        public function welcome_page() {

            if (!current_user_can('manage_options')) {
                wp_die('You do not have sufficient permissions to access this page.');
            }

            include PLUGIN_PATH . "/main.php";

        }

        public function create_plugin_database_table() {
            /**
             * On plugin activation create the database tables needed to store updates
             */
            register_activation_hook( __FILE__, 'plugin_update_report_data_install' );
            function plugin_update_report_data_install() {
                global $wpdb;
                global $plugin_update_report_version;

                $plugin_update_report_table_name = $wpdb->prefix . 'Plugin_Update_Report_DB';

                $charset_collate = $wpdb->get_charset_collate();

                $plugin_update_report_sql = "CREATE TABLE $plugin_update_report_table_name (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    date DATE DEFAULT '0000-00-00' NOT NULL,
                    type VARCHAR(191),
                    name VARCHAR(191),
                    slug VARCHAR(191),
                    description VARCHAR(191) DEFAULT 'WordPress plugin' NOT NULL,
                    version_before VARCHAR(191),
                    version_after VARCHAR(191),
                    reason VARCHAR(191),
                    update_status VARCHAR(30) NOT NULL,
                    active tinyint(1),
                    UNIQUE KEY id (id)
                ) $charset_collate;";

                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta( $plugin_update_report_sql );

                add_option( 'plugin_update_report_version', PLUGIN_UPDATE_REPORT_VERSION );
                add_option( 'plugin_update_report_enable_updates', 'on' );

                plugin_update_report_check_for_updates();
            }

            /**
             * Load actions if options are enabled
             */
            add_action( 'init', 'plugin_update_report_load_actions', 985 );
            function plugin_update_report_load_actions(){

                if (is_admin() || wp_doing_cron()) {
                
                    $updates_enabled = get_option( 'plugin_update_report_enable_updates' );
                    if ($updates_enabled == 'on') {
                        add_action('plugin_update_report_stats', 'plugin_update_report_stats_page_updates', 10);
                        add_action('wp_ajax_plugin_update_report_updates_data', 'plugin_update_report_updates_data');
                    }
                }

            }

            /**
             * On plugin activation schedule our daily check for updates
             */
            register_activation_hook( __FILE__, 'plugin_update_report_check_for_updates_daily_schedule' );
            function plugin_update_report_check_for_updates_daily_schedule(){
                //Use wp_next_scheduled to check if the event is already scheduled
                $timestamp = wp_next_scheduled( 'plugin_update_report_check_for_updates_daily' );
                //If $timestamp == false schedule daily backups since it hasn't been done previously
                if( $timestamp == false ){
                    $timezone = wp_timezone();
                    $midnight = new DateTime("00:00:00", $timezone);
                    //Schedule the event for right now, then to repeat daily
                    wp_schedule_event( $midnight->format('U'), 'daily', 'plugin_update_report_check_for_updates_daily' );
                }
            }

            /**
             * On plugin deactivation remove the scheduled events
             */
            register_deactivation_hook( __FILE__, 'plugin_update_report_check_for_updates_daily_schedule_clear' );
            function plugin_update_report_check_for_updates_daily_schedule_clear() {
                wp_clear_scheduled_hook( 'plugin_update_report_check_for_updates_daily' );
            }

            /**
             * After an update has run, check and log in database
             */
            add_action( 'upgrader_process_complete', 'plugin_update_report_after_update',10, 2);
            function plugin_update_report_after_update( $upgrader_object, $options ) {
                if ($options['action'] == 'update' ){
                    plugin_update_report_check_for_updates();
                }
            }

            /**
             * Loop through each type of update and determine if there is now a newer version
             */
            add_action( 'plugin_update_report_check_for_updates_daily', 'plugin_update_report_check_for_updates' );
            function plugin_update_report_check_for_updates() {

                global $wpdb;
                $plugin_update_report_table_name = $wpdb->prefix . 'Plugin_Update_Report_DB';
                
                if ( ! function_exists( 'get_plugins' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }

                $timezone = wp_timezone();
                $now = new DateTime("now", $timezone);
                $mysqldate = $now->format('Y-m-d');

                $plugins = get_plugins();
                
                foreach($plugins as $plugin_slug => $plugin) {
                
                    $plugin_active = false;
                    if ( is_plugin_active( $plugin_slug ) ) {
                        $plugin_active = true;
                    } 

                    $last_plugin_update = $wpdb->get_row( $wpdb->prepare(
                        "SELECT *
                         FROM $plugin_update_report_table_name 
                         WHERE `type` = 'plugin' 
                         AND `slug` = %s 
                         ORDER BY `date` DESC", 
                         array($plugin_slug) ) );

                    $today_plugin_update = $wpdb->get_row( $wpdb->prepare(
                        "SELECT * 
                        FROM $plugin_update_report_table_name 
                        WHERE `type` = 'plugin' 
                        AND `slug` = %s  
                        AND `date` = %s", 
                        array($plugin_slug, $mysqldate) ) );

                    if (!$last_plugin_update || version_compare($plugin['Version'], $last_plugin_update->version_after, '>')) {

                        $last_version = null;
                        if ($last_plugin_update) {
                            $last_version = $last_plugin_update->version_after;
                        }

                        $update_id = null;
                        if ($today_plugin_update) {
                            $update_id = $today_plugin_update->id;
                        }

                        $update_status = '';
                        if ($today_plugin_update) {
                            $update_status = 'successful';
                        }
                        
                        $plugin_update = array(
                            'id' => $update_id,
                            'date' => $mysqldate,
                            'type' => 'plugin',
                            'name' => $plugin['Name'],
                            'slug' => $plugin_slug,
                            'description' => $plugin['Description'],
                            'version_before' => $last_version,
                            'version_after' => $plugin['Version'],
                            'reason' => $reason,
                            'update_status' => $update_status,
                            'active' => $plugin_active,
                        );

                        plugin_update_report_track_update($plugin_update);
                    }
                }
                do_action('plugin_update_report_check');
            }

            /**
             * Track a single update and add it to the database
             */
            function plugin_update_report_track_update( $thing_to_track ) {

                global $wpdb;
                $plugin_update_report_table_name = $wpdb->prefix . 'Plugin_Update_Report_DB';

                $new_entry = $wpdb->replace(
                    $plugin_update_report_table_name,
                    $thing_to_track,
                    array(
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%d',
                    )
                );

                return $new_entry;
            }

            /**
             * Create the function to output the contents of our Dashboard Widget.
             */
            function plugin_update_report_widget_function() {

                $timezone = wp_timezone();
                $start_date_object = new DateTime("00:00:00", $timezone);
                $start_date = $start_date_object->format('Y-m-d');
                $end_date_object = new DateTime("now", $timezone);
                $end_date = $end_date_object->format('Y-m-d');
                
                $updates_data = plugin_update_report_get_updates_data($start_date, $end_date);
                ?>
                <div class="plugin-update-report-big-numbers plugin-update-report-postbox plugin-update-report-widget">
                    <div class="plugin-update-report-big-number">
                        <?php if('#plugin-update-report-plugin-update-count') { ?>
                            <h2 id="plugin-update-report-plugin-update-count"><?php echo esc_html($updates_data->total_plugins_updated); ?></h2>
                        <?php }
                        else if ('#plugin-update-report-plugin-update-count2') {?>
                            <h2 id="plugin-update-report-plugin-update-count2"><?php echo esc_html($updates_data->total_unsuccessful_plugins_updated); ?></h2>
                        <?php } ?>
                        <h3><?php printf( __( 'Plugin %s Updates', 'plugin-update-report' ), '<br>' ); ?></h3>
                    </div><!-- .plugin-update-report-big-number -->
                </div><!-- .plugin-update-report-widget -->
            <?php
            }

            /**
             * Ajax call for  updates stats data
             */
            function plugin_update_report_updates_data() {

                $start = null;
                $end = null;
                if (isset($_GET['start'])) {
                    $start = sanitize_text_field($_GET['start']);
                }
                if (isset($_GET['end'])) {
                    $end = sanitize_text_field($_GET['end']);
                }

                $dates = plugin_update_report_validate_dates($start, $end);

                $data = plugin_update_report_get_updates_data($dates->start_date, $dates->end_date);

                print json_encode($data);
                wp_die();

            }

            /**
             * Validate dates anytime you get an request for data
             */
            function plugin_update_report_validate_dates($start, $end) {
                $dates = new \stdClass;
                $timezone = wp_timezone();

                if (isset($start) && isset($end)) {
                    $start_date_object = DateTime::createFromFormat('Y-m-d', $start, $timezone);
                    $dates->start_date = $start_date_object->format('Y-m-d');
                    $end_date_object = DateTime::createFromFormat('Y-m-d', $end, $timezone);
                    $dates->end_date = $end_date_object->format('Y-m-d');
                } else {
                    $dates->start_date = date('Y-m-d', strtotime('days'));
                    $dates->end_date = date('Y-m-d');
                }
                return $dates;
            }


            /**
             * Get the plugin updates data from the database
             */
            function plugin_update_report_get_updates_data($start_date, $end_date) {

                global $wpdb;
                $plugin_update_report_table_name = $wpdb->prefix . 'Plugin_Update_Report_DB';

                $data = new \stdClass;

                $update_results = $wpdb->get_results( $wpdb->prepare( 
                    "SELECT * 
                    FROM $plugin_update_report_table_name 
                    WHERE `version_before` IS NOT NULL 
                    AND `date` >= %s 
                    AND `date` <= %s 
                    ORDER BY `date` ASC", 
                    array($start_date, $end_date) ) );

                $data = new \stdClass;
                $data->total_plugins_updated = 0;
                $data->total_unsuccessful_plugins_updated = 0;
                $data->updates = [];

                if (isset($update_results) && is_array($update_results)) {
                    $data->updates = $update_results;
                    foreach($data->updates as $update) {
                        if ($update->type == 'plugin' && $update->update_status == 'successful') {
                            $data->total_plugins_updated++;
                        }
                        else if ($update->type == 'plugin' && $update->update_status == 'unsuccessful') {
                            $data->total_unsuccessful_plugins_updated++;
                        }
                    }
                }

                $data = apply_filters( 'plugin_update_report_updates_data', $data, $start_date, $end_date );

                return $data;
            }

            /**
             * Force an update to the software update statistics
             */
            add_action('wp_ajax_plugin_update_report_force_refresh', 'plugin_update_report_force_refresh');
            function plugin_update_report_force_refresh() {

                plugin_update_report_check_for_updates();

                do_action('plugin_update_report_force_update');

                print json_encode(['status'=>'success']);
                wp_die();
            }

            /**
             * Ajax call for content stats data
             */
            function plugin_update_report_content_stats_data() {

                $start = null;
                $end = null;

                if (isset($_GET['start'])) {
                    $start = sanitize_text_field($_GET['start']);
                }

                if (isset($_GET['end'])) {
                    $end = sanitize_text_field($_GET['end']);
                }

                $dates = plugin_update_report_validate_dates($start, $end);

                $data = plugin_update_report_get_content_stats_data($dates->start_date, $dates->end_date);

                print json_encode($data);
                wp_die();
            }

            /**
             * Render a big number in the HTML report page
             */
            function plugin_update_report_render_big_number($title, $id) {
                $allowed_html = ['br' => [] ];
                ?>
                <div class="plugin-update-report-big-number">
                    <h2 id="<?php echo esc_attr($id); ?>">0</h2>
                    <h3><?php echo wp_kses($title, $allowed_html); ?></h3>
                </div><!-- .plugin-update-report-big-number -->
                <?php
            }

            /**
             * Convert PHP date format to Moment.js date format
             */
            function plugin_update_report_convert_date_format($format) {
                $replacements = [
                    'd' => 'DD',
                    'D' => 'ddd',
                    'j' => 'D',
                    'l' => 'dddd',
                    'N' => 'E',
                    'S' => 'o',
                    'w' => 'e',
                    'z' => 'DDD',
                    'W' => 'W',
                    'F' => 'MMMM',
                    'm' => 'MM',
                    'M' => 'MMM',
                    'n' => 'M',
                    't' => '', // no equivalent
                    'L' => '', // no equivalent
                    'o' => 'YYYY',
                    'Y' => 'YYYY',
                    'y' => 'YY',
                    'a' => 'a',
                    'A' => 'A',
                    'B' => '', // no equivalent
                    'g' => 'h',
                    'G' => 'H',
                    'h' => 'hh',
                    'H' => 'HH',
                    'i' => 'mm',
                    's' => 'ss',
                    'u' => 'SSS',
                    'e' => 'zz', // deprecated since version 1.6.0 of moment.js
                    'I' => '', // no equivalent
                    'O' => '', // no equivalent
                    'P' => '', // no equivalent
                    'T' => '', // no equivalent
                    'Z' => '', // no equivalent
                    'c' => '', // no equivalent
                    'r' => '', // no equivalent
                    'U' => 'X',
                ];
                $moment_js_format = strtr($format, $replacements);
                return $moment_js_format;
            }

            /**
             * Remove dashes from dates and other places you want them cleared
             */
            function plugin_update_report_nodash($text) {
                return str_replace('-', '_', $text);
            }
        }

        public function register_session() {
            
            if (!session_id()) {
                session_start();
            }
        }
    }
    new Plugin_Update_Report_Generator();
}
