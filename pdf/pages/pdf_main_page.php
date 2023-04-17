<?php
$plugin_path = PLUGIN_PATH;
$plugin_url = PLUGIN_URL;
// $c2['image'] = str_replace(site_url() . '/', '', wp_get_attachment_image($c2['image'], 'medium'));
// First page
function build_page_one($client_name, $update_date, $update_year)
{
    $html_page_one = <<<EOT
    <div class="pdf-header page-pdf">
        <h1 class="title"> $client_name Plugin Update Report $update_date $update_year</h1>
        <p class="subtitle">Plugin Update Report</p>
        <div class= "title_underline"> </div>
        <div >
    </div>
    </div>
    EOT;

    return $html_page_one;
}
// <img src="/wp-content/plugins/plugin-update-report/pdf/assets/logo_header.jpg"/>