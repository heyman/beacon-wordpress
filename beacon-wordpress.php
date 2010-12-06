<?php
/*
Plugin Name: Beacon Wordpress Plugin
Plugin URI: http://github.com/heyman/beacon-wordpress
Description: Provides real-time notifications of comments through the real-time cloud service Beacon (http://beacon-api.com/).
Author: Jonatan Heyman
Version: 0.92
Author URI: http://heyman.info/
*/

session_start();

require_once('HttpClient.class.php');
require_once('beacon-options.php');

function beacon_is_configured() {
    if (get_option('beacon_api_key') == null)
	return false;
    if (get_option('beacon_secret_key') == null)
	return false;
    
    return true;
}

function beacon_send_message($channel, $data) {
    $url = '/1.0.0/' . get_option('beacon_api_key') . '/channels/' . $channel;
    $client = new HttpClient('api.beaconpush.com');
    $client->extra_request_headers = array('X-Beacon-Secret-Key: ' . get_option('beacon_secret_key'));
    $client->post($url, json_encode($data));
}

function beacon_init() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('beacon-wordpress-plugin', plugins_url('/beacon.js', __FILE__));
    wp_enqueue_style('beacon-wordpress-plugin', plugins_url('/beacon.css', __FILE__));
}

function beacon_broadcast_comment($comment) {
    global $current_user, $current_site;
    
    // get beacon username
    $user = "user_" . session_id();
    
    beacon_send_message("post_" . $comment->comment_post_ID, array("content" => $comment->comment_content, "beacon_user" => $user));
}
function beacon_submit_comment($comment_id) {
    global $wpdb;
    
    $comment_id = (int) $comment_id;
    $comment = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_ID = '$comment_id'");
    
    if ($comment->comment_approved == 1)
	beacon_broadcast_comment($comment);
}

function beacon_inject_js() {
    // session id based username
    $user = "user_" . session_id();
    
    print('
        <script type="text/javascript" src="http://beaconpush.com/1/client.js"></script>
        <script type="text/javascript">
            Beacon.connect("' . get_option('beacon_api_key') . '", ["post_'); the_ID(); print('"], {log: true, user: "' . $user . '"});
            Beacon.listen(function(data){
                BeaconWordpressPlugin.onNewPost(data);
            });
        </script>
    ');
}

if (beacon_is_configured()) {
    add_action('init', 'beacon_init');
    add_action('comment_post', 'beacon_submit_comment');
    add_action('comment_unapproved_to_approved', 'beacon_broadcast_comment');
    add_action('wp_footer', 'beacon_inject_js');
}

?>