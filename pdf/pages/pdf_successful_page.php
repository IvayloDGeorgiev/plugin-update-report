<?php

// Successfull updates logic

function build_page_two($successful_updates)
{

    if ($result == null) {
        $opensuccessfultable = <<<EOT
        <table class="plugin-generator">
        <thead>
        <tr>
        <td class="tdthead border-lefttd">Plugin</td>
        <td class="tdthead">Description</td>
        <td class="tdthead">Date Updated</td>
        <td class="tdthead">Current Version</td>
        <td class="tdthead">New Version</td>
        </tr>
        </thead>
        <tbody>
        EOT;

        $successfultablerows = '';
        $successfultable = '';

        global $wpdb;
        $plugin_update_report_table_name = $wpdb->prefix . 'Plugin_Update_Report_DB';
        //GET THE DATA by DATES
        if (isset($_GET['dateFrom']) && isset($_GET['dateTo'])){
            $dateFrom = $_GET['dateFrom'];
            $dateTo = $_GET['dateTo'];
            
            $query = 
                "SELECT * 
                FROM $plugin_update_report_table_name 
                WHERE `date` >= '" . $dateFrom . "'
                AND `date` <= '" . $dateTo . "'
                AND `update_status` = 'successful'
                ORDER BY `name` ASC";
        } else {
            $query = "SELECT * 
                FROM $plugin_update_report_table_name 
                WHERE `update_status` = 'successful'
                ORDER BY `name` ASC";
        }

        $result =  $wpdb->get_results($wpdb->prepare($query));

        foreach ($result as $content) {
            $name = $content->name;
            $description = $content->description;
            $date = $content->date;
            $version_before = $content->version_before;
            $version_after = $content->version_after;
            $date = str_replace('-','/', $date);
            $dateFormat = date('d/m/Y', strtotime($date));
            $successfultablerow = <<<EOT
            <tr>
            <td class="name border-lefttd">$name</td>
            <td class="description">$description</td>
            <td class= "text_center">$dateFormat</td>
            <td class= "text_center">Version: $version_before</td>
            <td class= "text_center">Version: $version_after</td>
            </tr>
EOT;

            $successfultablerows .= $successfultablerow;
        }

        $closesuccessfultable = <<<EOT
        </tbody>
        </table>
EOT;

        $successfultable .= $opensuccessfultable . $successfultablerows . $closesuccessfultable;
    } else {
        $successfultable = <<<EOT
        <table class="plugin-generator">
        <thead>
        <tr>
        <td class="tdthead border-lefttd">Plugin</td>
        <td class="tdthead">Description</td>
        <td class="tdthead">Date Updated</td>
        <td class="tdthead">Current Version</td>
        <td class="tdthead">New Version</td>
        </tr>
        </thead>
        <tbody>
        <tr>
        <td class="border-lefttd">n/a</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        </tr>
        </tbody>
        </table>
EOT;
    }

    $html_page_two = <<<EOT
    <div class="successful page-pdf">
        <div class = "pdf_company_logo"></div>
        <p class ="successfulp">Successful Updates</p>
        <div class= "successful_title_underline"> </div>
        $successfultable
    </div>
EOT;

    return $html_page_two;
}
