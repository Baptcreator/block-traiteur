<?php
/**
 * Script de désinstallation Block Traiteur
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/class-database.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-installer.php';

Block_Traiteur_Installer::uninstall();