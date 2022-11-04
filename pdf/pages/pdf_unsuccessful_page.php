<?php

// Unseccessfull updates logic

function build_page_three($unsuccessful_updates)
{
    $unsuccessfultable = <<<EOT
    <table class="plugin-generator">
    <thead>
        <tr>
            <td class="tdthead border-lefttd">Plugin</td>
            <td class="tdthead">Description</td>
            <td class="tdthead">Reason</td>
        </tr>
    </thead>
    <tbody>
    EOT;

    global $wpdb;
    $plugin_update_report_table_name = $wpdb->prefix . 'Plugin_Update_Report_DB';
    //GET THE DATA by DATES
    if (isset($_GET['dateFrom']) && isset($_GET['dateTo'])){
        $dateFrom = $_GET['dateFrom'];
        $dateTo = $_GET['dateTo'];
        $query = "SELECT * 
            FROM $plugin_update_report_table_name 
            WHERE `date` >= '" . $dateFrom . "'
            AND `date` <= '" . $dateTo . "'
            AND `update_status` = 'unsuccessful'
            ORDER BY `name` ASC";
    } else {
        $query = "SELECT * 
            FROM $plugin_update_report_table_name 
            WHERE `update_status` = 'unsuccessful'
            ORDER BY `name` ASC";
    }

    $result1 =  $wpdb->get_results($wpdb->prepare($query));
    if ($result1 != null) {
        foreach ($result1 as $content_uns) {
            $name = $content_uns->name;
            $description = $content_uns->description;
            $reason = $content_uns->reason;
            
            
            $unsuccessfultable .= <<<EOT
                <tr>
                    <td class="name border-lefttd">$name</td>
                    <td class="description">$description</td>
                    <td>$reason</td>
                </tr>
            EOT;
        }
    } else {
        $unsuccessfultable .= <<<EOT
        <tr>
            <td class="name border-lefttd">N/A</td>
            <td class="description"></td>
            <td></td>
        </tr>
    EOT;
    } 

    $unsuccessfultable .= <<<EOT
        </tbody>
    </table>
    EOT;
    

    $html_page_three = <<<EOT
    <div class="unsuccessful page-pdf">
        <div class = "pdf_company_logo"></div>
        <p class="successfulp">Unsuccessful Updates</p>
        <div class= "unsuccessful_title_underline"> </div>
        $unsuccessfultable
    </div>
    EOT;

    return $html_page_three;
}
