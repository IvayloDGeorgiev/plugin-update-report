<?php
define('PLUGIN_PATH', __DIR__);
define('PLUGIN_URL', site_url());

if (PLUGIN_PATH) {
  
    // Load MPDF library with composer
    require_once PLUGIN_PATH . '/vendor/autoload.php';

    // includes function returning variables
    $client_name = get_bloginfo('name');
    $update_date = date('F');
    $update_year = date('Y');

    $successful_updates = '';
    $unsuccessful_updates = '';

    /* MAIN PDF PAGE
    * Includes function compiling the following files:
    * pdf_main_page.php
    * pdf_successful_page.php
    * pdf_unsuccesful_page.php
    * pdf_footer_page.php
    */
    include_once PLUGIN_PATH . '/pdf/pages/main-page.php';

    $html = build_main_page($client_name, $update_date, $update_year, $successful_updates, $unsuccessful_updates, $notes);

    // Includes Stylesheet
    $stylesheet = file_get_contents(PLUGIN_PATH . '/pdf/css/pdf.css');

    // Set File name
    $file = $client_name . ' | Plugin Update Report';

    $file_title = $client_name . '_' . $update_date;
    $file_name = $client_name . '_Plugin' . ' Update' . ' Report_' . $update_date . ' ' . $update_year;

    // Prepare Mpdf font directory for font enqueuing
    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    // Instantiate Mpdf class

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'margin_header' => 0,
        'margin_footer' => 0,
        'fontDir' => array_merge($fontDirs, [
            PLUGIN_PATH . '/pdf/font',
        ]),
        'fontdata' => $fontData + [
            'proxima-nova' => [
                'R' => 'ProximaNova-Regular.ttf',
                'B' => 'Proxima-Nova-Bold.ttf',
            ],
            'nunito' => [
                'R' => 'Nunito-Regular.ttf',
                'B' => 'Nunito-Bold.ttf',
            ],
        ],
        'default_font' => 'proxima-nova',
        'debug' => false
    ]);

    // Write Html and Output.
    $mpdf->WriteHTML($stylesheet, 1);
    $mpdf->WriteHTML($html, 2);
    $mpdf->SetTitle($file);
    $mpdf->Output($file_name . '.pdf','I');

} else {
    print "Access Denied";
}
