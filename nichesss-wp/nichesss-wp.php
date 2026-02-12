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

// Planification automatique via cron WordPress
add_action('nichesss_ai_cron_hook', 'nichesss_ai_cron_generate');

function nichesss_ai_cron_generate() {
    // Exemple : générer un article avec thème par défaut
    $theme = get_option('nichesss_ai_cron_theme', '');
    $sujet = get_option('nichesss_ai_cron_sujet', '');
    $post_id = nichesss_ai_generate_article($theme, $sujet);
    if ($post_id) {
        // Log ou notification possible ici
    }
}

// Activation du cron à l’activation du plugin
register_activation_hook(__FILE__, function() {
    if (!wp_next_scheduled('nichesss_ai_cron_hook')) {
        wp_schedule_event(time(), 'daily', 'nichesss_ai_cron_hook');
    }
});

// Désactivation du cron à la désactivation du plugin
register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook('nichesss_ai_cron_hook');
});
