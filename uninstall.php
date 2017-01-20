<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

unregister_setting('pluginPage', 'wp_coffeeshop_settings');
