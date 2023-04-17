<?php
define('PLUGIN_PATH', __DIR__);
define('PLUGIN_URL', site_url());
// includes function returning main pdf page


function build_main_page($client_name, $update_date, $update_year, $successful_updates, $unsuccessful_updates)
{

    include_once PLUGIN_PATH . '/pdf/pages/pdf_main_page.php';

    $html = build_page_one( $client_name, $update_date, $update_year);

    // includes function returning successful updates page

    include_once PLUGIN_PATH . '/pdf/pages/pdf_successful_page.php';

    $html .= build_page_two($successful_updates);

    // includes function returning Unsuccessful updates page

    include_once PLUGIN_PATH . '/pdf/pages/pdf_unsuccessful_page.php';

    $html .= build_page_three($unsuccessful_updates);

    // includes function returning footer page

    include_once PLUGIN_PATH . '/pdf/pages/pdf_footer_page.php';

    $html .= build_page_four(PLUGIN_URL);

    return $html;
}
