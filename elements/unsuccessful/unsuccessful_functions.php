<?php 

if (isset($_POST['submitbtn'])) {
    $update_status = 'unsuccessful';
    $type = 'plugin';
    $now = new DateTime("now", $timezone);
    $mysqldate = $now->format('Y-m-d');
    $plugin_slugs = 'unsuccessful plugin update!';
    
    $data = array(
        'name' => $_POST['plugin_name'],
        'slug' => $plugin_slugs,
        'description' => $_POST['description'],
        'reason' => $_POST['reason'],
        'version_before' =>' 0.0.0',
        'version_after' => '0.0.0',
        'type' => $type,
        'date' => $mysqldate,
        'update_status' => $update_status,
        'active' => '1',
    );

    global $wpdb;
    $plugin_update_report_table_name = $wpdb->prefix . 'Plugin_Update_Report_DB';

    $result = $wpdb->insert($plugin_update_report_table_name, $data, $format=null);

    if ($result==1)
     {
        echo "<script>alert('Unsuccessful Update information Saved!');</script>";
    }else{
        echo "<script>alert('Unable to Save!');</script>";
    }

}
  
if(isset($_POST['delete'])) {
    global $wpdb;
    $wp_table_name = $wpdb->prefix . 'Plugin_Update_Report_DB';
    $id = $_POST['plugin_id'];
    $wpdb->delete( $wp_table_name, array( 'id' => $id ) );
    if($_POST['delete']) {
        echo "<script>alert('The record has been removed!');</script>";
    }else {
        echo "<script>alert('The record couldn't be removed!');</script>";
    }
}