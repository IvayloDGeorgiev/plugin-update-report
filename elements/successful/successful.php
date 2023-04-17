<div class="plugin-update-report-big-numbers">
    <?php 
         
         plugin_update_report_render_big_number(
            sprintf( __( 'Successful %s Updates', 'plugin-update-report' ), '<br>' ), 
            'plugin-update-report-plugin-update-count');
    ?>
</div><!-- .plugin-update-report-big-numbers -->
<div class="plugin-update-report-section plugin-update-report-border-top">
    <table  id="PluginTable" class="plugin-update-report-list">
        <thead style="border-bottom: 1px solid black;">
            <tr>
                <th class="plugin_name">Plugin Name</th>
                <th class="plugin_description">Description</th>
                <th class="plugin_date">Date</th>
                <th class="plugin_oldversion">Old Version</th>
                <th class="plugin_newversion">New Version</th>
            </tr>
        </thead> 
        <tbody id="plugin-update-report-plugin-updates-list"></tbody>
    </table>
</div>
