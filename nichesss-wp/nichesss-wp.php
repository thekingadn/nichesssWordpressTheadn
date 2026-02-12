<?php
/*
Plugin Name: Nichesss AI Content Generator
Description: Génère des articles avec texte, illustration et voix via l’API Nichesss, en évitant les doublons.
Version: 0.1
Author: thekingadn
*/

// Sécurité : empêcher l’accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Inclure les fichiers nécessaires
require_once plugin_dir_path(__FILE__) . 'includes/admin-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/nichesss-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/content-generator.php';
