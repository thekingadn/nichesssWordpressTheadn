<?php
// Génération d’articles et vérification des doublons
function nichesss_ai_generate_article($theme = '', $sujet = '') {
    $prompt = $sujet ?: $theme ?: get_bloginfo('name');
    // Vérifier les doublons
    $existing = get_posts([
        's' => $prompt,
        'post_type' => 'post',
        'posts_per_page' => 1
    ]);
    if ($existing) return false;
    $data = nichesss_api_generate_content($prompt, 'article');
    if (!$data || empty($data['content'])) return false;
    // Créer l’article
    $post_id = wp_insert_post([
        'post_title' => wp_strip_all_tags($data['title'] ?? $prompt),
        'post_content' => $data['content'],
        'post_status' => 'draft',
        'post_author' => get_current_user_id(),
    ]);
    // Ajouter illustration si disponible
    if (!empty($data['image_url'])) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        media_sideload_image($data['image_url'], $post_id);
    }
    // (Optionnel) Ajouter la voix si disponible
    if (!empty($data['audio_url'])) {
        add_post_meta($post_id, 'nichesss_audio_url', esc_url($data['audio_url']));
    }
    return $post_id;
}
