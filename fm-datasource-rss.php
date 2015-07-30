<?php

/*
	Plugin Name: Fieldmanager_Datasource_RSS
	Plugin URI: https://github.com/alleyinteractive/fieldmanager_datasource_rss
	Description: RSS Datasource for Fieldmanager
	Version: 0.1
	Author: Alley Interactive
	Author URI: http://www.alleyinteractive.com/
*/
/*  This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


function rss_datasource_load_plugin() {
	if ( class_exists( 'Fieldmanager_Datasource' ) && ! class_exists( 'Custom_Datasource_RSS' ) ) {
		define( 'RSS_DATASOURCE_PATH', dirname( __FILE__ ) );
		require_once( RSS_DATASOURCE_PATH . '/class-custom-datasource-rss.php' );
		require_once( RSS_DATASOURCE_PATH . '/class-datasource-rss-settings.php' );
	}
}
add_action( 'after_setup_theme', 'rss_datasource_load_plugin' );