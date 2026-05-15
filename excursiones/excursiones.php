<?php
/*
Plugin Name: Excursiones
Description: Plugin para gestionar excursiones con tipos, ubicaciones y detalles como precio y plazas.
Version: 5.0
Author: Francisco Javier Montelongo Costas
Text Domain: funciones-excursiones
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'EXCURSIONES_DIR', plugin_dir_path( __FILE__ ) );
define( 'EXCURSIONES_URL', plugin_dir_url( __FILE__ ) );

require_once EXCURSIONES_DIR . 'includes/cpt.php';
require_once EXCURSIONES_DIR . 'includes/taxonomias.php';
require_once EXCURSIONES_DIR . 'includes/metabox.php';
require_once EXCURSIONES_DIR . 'includes/frontend.php';
require_once EXCURSIONES_DIR . 'includes/menu.php';
