<?php
/**
 * Autoloader pour TCPDF
 * 
 * Note: Dans l'implémentation réelle, vous devrez télécharger
 * la librairie TCPDF depuis https://tcpdf.org/
 * et l'extraire dans ce dossier vendor/tcpdf/
 */

if (!defined('ABSPATH')) {
    exit;
}

// Vérifier si TCPDF est disponible
if (!class_exists('TCPDF')) {
    // Chemin vers TCPDF
    $tcpdf_path = BLOCK_TRAITEUR_PLUGIN_DIR . 'vendor/tcpdf/tcpdf.php';
    
    if (file_exists($tcpdf_path)) {
        require_once $tcpdf_path;
    } else {
        // TCPDF non trouvé - utiliser une classe de fallback
        class TCPDF {
            public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false) {
                throw new Exception(__('TCPDF library not found. Please install TCPDF in vendor/tcpdf/', 'block-traiteur'));
            }
        }
    }
}