<?php
/*
Plugin Name: Beacon Wordpress Plugin
Plugin URI: http://beacon-api.com/
Description: Provides real-time notifications of comments through the real-time cloud service Beacon (http://beacon-api.com/).
Author: Jonatan Heyman
Version: 0.9
Author URI: http://heyman.info/
*/

session_start();

require_once('beacon-options.php');

function beacon_is_configured() {
    if (get_option('beacon_api_key') == null)
	return false;
    if (get_option('beacon_secret_key') == null)
	return false;
    
    return true;
}

function beacon_generate_token($user, $secret_key, $expire_time=null) {
    if ($expire_time == null)
	$expire_time = time() + (3600 * 24 * 30);

    $payload = $user . "," . $expire_time;
    $signature = hash_hmac("sha1", $payload, $secret_key);
    return $payload . "," . $signature;
}

function beacon_send_message($channel, $data) {
    $url = 'http://beacon-api.com/api/v1/' . get_option('beacon_api_key') . '/channels/' . $channel;
    $req = new HttpRequest($url, HttpRequest::METH_POST);
    $req->setRawPostData(json_encode($data));
    $req->addHeaders(array('X-Beacon-Secret-Key' => get_option('beacon_secret_key')));
    $result = $req->send();
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
    global $post;
    if ($post->post_type != 'post')
	return;
    
    // session id based username
    $user = "user_" . session_id();
    
    echo '
        <script id="orbitClientScript" type="text/javascript" src="http://beacon-api.com/client/client.js?embed=false"></script>
        <script type="text/javascript">
            ESN.Beacon.credentials.user = "' . $user . '";
            ESN.Beacon.credentials.sphere = "' . get_option('beacon_api_key') . '";
            ESN.Beacon.credentials.token = "' . beacon_generate_token($user, get_option('beacon_secret_key')) . '";
            ESN.Beacon.credentials.channels = ["post_' . $post->ID . '"];
        
            ESN.Beacon.callbacks.onMessage = function (data) {
                BeaconWordpressPlugin.onNewPost(data);
            }
            ESN.Beacon.embed();
        </script>
    ';
}

if (beacon_is_configured()) {
    add_action('init', 'beacon_init');
    add_action('comment_post', 'beacon_submit_comment');
    add_action('comment_unapproved_to_approved', 'beacon_broadcast_comment');
    add_action('wp_footer', 'beacon_inject_js');
}

?>