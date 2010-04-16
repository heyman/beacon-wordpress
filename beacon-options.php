<?php
// create custom plugin settings menu
add_action('admin_menu', 'beacon_create_admin_menu');

function beacon_create_admin_menu() {

	//create new top-level menu
	add_menu_page('Beacon Plugin Settings', 'Beacon Settings', 'administrator', __FILE__, 'beacon_settings_page', plugins_url('/icon.png', __FILE__));

	//call register settings function
	add_action( 'admin_init', 'register_beacon_settings' );
}


function register_beacon_settings() {
	//register our settings
	register_setting( 'beacon-settings', 'beacon_api_key' );
	register_setting( 'beacon-settings', 'beacon_secret_key' );
}

function beacon_settings_page() {
?>
<div class="wrap">
<h2>Beacon Wordpress Plugin</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'beacon-settings' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Beacon API Key</th>
        <td><input type="text" name="beacon_api_key" value="<?php echo get_option('beacon_api_key'); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Beacon Secret Key</th>
        <td><input type="text" name="beacon_secret_key" value="<?php echo get_option('beacon_secret_key'); ?>" /></td>
        </tr>
    </table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
<?php } ?>
