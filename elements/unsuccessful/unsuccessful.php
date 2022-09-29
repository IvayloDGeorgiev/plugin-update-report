<?php 
define('PLUGIN_PATH2', __DIR__);
include PLUGIN_PATH2 . "/unsuccessful_functions.php";
?>

<div class="plugin-update-report-big-numbers">
    <?php 
        plugin_update_report_render_big_number(
            sprintf( __( 'Unsuccessful %s Updates', 'plugin-update-report' ), '<br>' ), 
            'plugin-update-report-plugin-update-count2'
        );
    ?>
</div><!-- .plugin-update-report-big-numbers -->

<div class="unsuccessful_container">
    <div class="container">
        <h2 class="unsuccessful-h2">Add Unsuccesful Plugin information</h2>

        <form  method="post" >

            <input id="plugin_name" type="text" name="plugin_name" placeholder="Plugin Name...">

            <input id="description" type="text" name="description" placeholder="Description...">

            <input id="reason" type="text" name="reason" placeholder="Reason...">
        
            <input type="submit" value="Submit" name="submitbtn" class="button button-primary">
        </form>
        <div class="plugin-update-report-section plugin-update-report-border-top">
            <table  id="PluginTable" class="plugin-update-report-list">
                <thead style="border-bottom: 1px solid black;">
                    <tr>
                        <th class="plugin_name">Plugin Name</th>
                        <th class="plugin_description">Description</th>
                        <th class="plugin_oldversion">Reason</th>
                        <th class="plugin_date">Date</th>
                    </tr>
                </thead> 
                <tbody id="plugin-update-report-plugin-updates-list2"></tbody>
            </table>
        </div>
    </div>
</div>

