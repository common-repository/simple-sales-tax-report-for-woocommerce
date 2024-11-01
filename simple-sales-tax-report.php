<?php
/*
Plugin Name: Simple Sales Tax Report for WooCommerce
Description: A very basic sales tax reporting tool for WooCommerce. Get annual reports of sales tax collected in the United States, broken down by ZIP code.
Version: 1.1.0
Author: Room 34 Creative Services, LLC
Author URI: https://room34.com/
License: GPL2
Text Domain: r34sstr
*/

/*  Copyright 2021 Room 34 Creative Services, LLC (email: info@room34.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



// Don't load directly
if (!defined('ABSPATH')) { exit; }


// Load required files
require_once(plugin_dir_path(__FILE__) . 'class-r34sstr.php');


// Initialize plugin
add_action('plugins_loaded', function() {
	global $R34SSTR;
	$R34SSTR = new R34SSTR();
});


// Load text domain for translations
/*
add_action('plugins_loaded', function() {
	load_plugin_textdomain('r34sstr', FALSE, basename(plugin_dir_path(__FILE__)) . '/i18n/languages/');
});
*/


// Flush rewrite rules when plugin is activated
register_activation_hook(__FILE__, function() { flush_rewrite_rules(); });


// Install/upgrade
register_activation_hook(__FILE__, 'r34sstr_install');
add_action('plugins_loaded', function() {
	global $R34SSTR;
	if (isset($R34SSTR) && get_option('r34sstr_version') != @$R34SSTR->version) {
		r34sstr_install();
	}
}, 11);


// Plugin installation
function r34sstr_install() {
	global $R34SSTR;
	// Update version
	if (isset($R34SSTR)) {
		update_option('r34sstr_version', @$R34SSTR->version);
	}
}

