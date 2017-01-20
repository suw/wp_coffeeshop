<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              suwdo.com
 * @since             1.0.0
 * @package           Wp_Coffeeshop
 *
 * @wordpress-plugin
 * Plugin Name:       WpCoffeeshop
 * Plugin URI:        wp-coffeeshop
 * Description:       Using your current location, show a banner in the admin area with a nearby coffee shop
 * Version:           1.0.0
 * Author:            Su Wang
 * Author URI:        suwdo.com
 * Text Domain:       wp-coffeeshop
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Set up the admin Options menu item
 */
function wp_coffeeshop_add_admin_menu()
{
    add_options_page('WPCoffeeShop', 'WPCoffeeShop', 'manage_options', 'wpcoffeeshop', 'wp_coffeeshop_options_page');
}

/**
 * Initialize the settings page
 */
function wp_coffeeshop_settings_init()
{
    register_setting('pluginPage', 'wp_coffeeshop_settings');

    add_settings_section(
        'wp_coffeeshop_pluginPage_section',
        __('Coffee Shop Options', 'wordpress'),
        'wp_coffeeshop_settings_section_callback',
        'pluginPage'
    );

    add_settings_field(
        'wp_coffeeshop_google_api_key',
        __('Google Maps API Key', 'wordpress'),
        'wp_coffeeshop_google_api_key_render',
        'pluginPage',
        'wp_coffeeshop_pluginPage_section'
    );
}

/**
 * Render the settings page
 */
function wp_coffeeshop_google_api_key_render()
{
    $options = get_option('wp_coffeeshop_settings');
    ?>

    <input type='text' name='wp_coffeeshop_settings[wp_coffeeshop_google_api_key]'
           value='<?php echo $options['wp_coffeeshop_google_api_key']; ?>'>
    <?php
}

/**
 * Callback for creating the description on the options page
 */
function wp_coffeeshop_settings_section_callback()
{
    $content = '<p>
        Before this works, you need to request an API key from Google to use the Google Places API. Check out
        <a href="https://developers.google.com/places/web-service/">the Google Places Web Service page</a> for more
        information about how this works and how to request an API key.
    </p>';
    echo __($content, 'wordpress');
}

/**
 * Rendering HTML for the actual options page
 */
function wp_coffeeshop_options_page()
{

    ?>
    <form action='options.php' method='post'>

        <h2>WPCoffeeShop</h2>

        <?php
        settings_fields('pluginPage');
        do_settings_sections('pluginPage');
        submit_button();
        ?>

    </form>
    <?php

}

/**
 * Admin AJAX handler for requesting data from the Google Maps Places API
 */
function gimme_the_liquid_gold()
{
    global $wpdb;

    $lat = floatval($_POST['wp_coffee_shop_lat']);
    $long = floatval($_POST['wp_coffee_shop_long']);

    $latLong = "$lat,$long";

    $options = get_option('wp_coffeeshop_settings');
    $apiKey = $options['wp_coffeeshop_google_api_key'];

    $responseObject = new \stdClass();

    if (empty($apiKey)) {
        $responseObject->errorMessage = 'It looks like you need to add a Google API Key in options';
    } else {
        $json = file_get_contents("https://maps.googleapis.com/maps/api/place/nearbysearch/json?key=$apiKey&location=$latLong&rankby=distance&keyword=coffee+shop");
        $results = json_decode($json);

        foreach ($results->results as $location) {
            if ($location->opening_hours->open_now) {
                $json = file_get_contents('https://maps.googleapis.com/maps/api/place/details/json?key=' . $apiKey . '&placeid=' . $location->place_id);
                $placeDetails = json_decode($json);

                $responseObject->locationUrl = $placeDetails->result->url;
                $responseObject->locationName = $location->name;
            }
            break;
        }
    }

    echo json_encode($responseObject);
    wp_die();
}

/**
 * Place holder for where the coffee shop recommendation goes
 */
function add_coffeeshop_placeholder()
{
    echo "<div id='wpCoffeeShop'><small><em>Searching for liquid gold...</em></small></div>";
}

/**
 * Load Javascript with the last modified time attached to it
 */
function load_scripts($hook = null)
{
    $my_js_ver = date("ymd-Gis", filemtime(plugin_dir_path(__FILE__) . 'assets/js/wp-coffeeshop.js'));
    wp_enqueue_script('custom_js', plugins_url('assets/js/wp-coffeeshop.js', __FILE__), array(), $my_js_ver);
}

// Hook actions into Wordpress
add_action('admin_init', 'load_scripts');
add_action('admin_init', 'wp_coffeeshop_settings_init');

add_action('admin_menu', 'wp_coffeeshop_add_admin_menu');

add_action('wp_ajax_my_action', 'gimme_the_liquid_gold');
add_action('admin_notices', 'add_coffeeshop_placeholder');

