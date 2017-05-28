<?php
/*
Plugin Name: NaiGalleco Cumples Plugin
Plugin URI:  http://www.proyectos.josu-astigarraga.es/NaiGallego/Cumples
Description: Plugin para administrar los cumpleaños de una lista y enviar emails automáticos
Version:     1.0
Author:      Josu Astigarraga
Author URI:  http://www.josu-astigarraga.es
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
/*
NaiGalleco Cumples Plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
NaiGalleco Cumples Plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with NaiGalleco Cumples Plugin. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

if(!function_exists("add_action")){
	die("No se puede ejecutar el plugin sin wordpress");
}
error_reporting( E_ALL );
global $wpdb;
require_once plugin_dir_path(__FILE__) . "ngc-migrations.php";
$migrationManager = new NGCMigrationManager($wpdb);
$migrationManager->migrate();
function ngc_install(){
	require_once plugin_dir_path(__FILE__) . "ngc-cumples.install.php";
}
function ngc_menu(){
	require_once plugin_dir_path(__FILE__) . "ngc-cumples.menu.php";
}
register_activation_hook( __FILE__, 'ngc_install' );
add_action("admin_menu","ngc_menu");
	add_action( 'rest_api_init', 'ngc_register_wp_api_endpoints' );
function ngc_register_wp_api_endpoints(){
	global $wpdb;
	require_once plugin_dir_path(__FILE__) . "ngc-cumples.api.php";
}



