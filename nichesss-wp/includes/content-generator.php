<?php
// Génération d’articles et vérification des doublons
function nichesss_ai_generate_article($theme = '', $sujet = '') {
    global $wpdb;
    $prompt = $sujet ?: $theme ?: get_bloginfo('name');
    $personna = nichesss_ai_pick_personna($sujet);
    $full_prompt = $personna['prompt'] . " Sujet : " . $prompt;
    $prompt_hash = md5(strtolower(trim($full_prompt)));
    // Vérifier la table des doublons
    $table = $wpdb->prefix . 'nichesss_ai_hashes';
    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE prompt_hash = %s", $prompt_hash));
    if ($exists) return false;
    $data = nichesss_api_generate_content($full_prompt, 'article');
    if (!$data || empty($data['content'])) return false;
    // Vérification avancée : similarité avec les articles existants
    $posts = get_posts([
        'post_type' => 'post',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ]);
    foreach ($posts as $pid) {
        $content = get_post_field('post_content', $pid);
        similar_text(strip_tags($data['content']), strip_tags($content), $percent);
        if ($percent > 80) return false; // Doublon détecté
    }
    // Créer l’article avec catégorie et tags automatiques
    $cat_id = get_option('nichesss_ai_default_cat');
    $tags = [];
    if (!empty($data['tags']) && is_array($data['tags'])) {
        $tags = array_map('sanitize_text_field', $data['tags']);
    } elseif (!empty($theme)) {
        $tags[] = $theme;
    }
    $post_id = wp_insert_post([
        'post_title' => wp_strip_all_tags($data['title'] ?? $prompt),
        'post_content' => $data['content'],
        'post_status' => 'draft',
        'post_author' => get_current_user_id(),
        'post_category' => $cat_id ? [$cat_id] : [],
        'tags_input' => $tags,
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
    // Enregistrer le hash du prompt en base
    $wpdb->insert($table, [
        'prompt_hash' => $prompt_hash,
        'created_at' => current_time('mysql'),
    ]);
    // Ajouter le personna utilisé en meta
    if ($post_id) {
        add_post_meta($post_id, 'nichesss_personna', $personna['name']);
    }
    // Optimisation SEO post-création
    if ($post_id) {
        // Générer une meta description (extrait ou résumé)
        $meta_desc = mb_substr(strip_tags($data['content']), 0, 155);
        update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_desc);
        // Générer un focus keyword (mot-clé principal)
        $focus_kw = $theme ?: $sujet;
        if ($focus_kw) {
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus_kw);
        }
        // Générer un titre SEO optimisé
        $seo_title = ($data['title'] ?? $prompt) . ' | ' . get_bloginfo('name');
        update_post_meta($post_id, '_yoast_wpseo_title', $seo_title);
    }
    return $post_id;
}

// Création de la table à l’activation du plugin
register_activation_hook(dirname(__FILE__,2).'/nichesss-wp.php', function() {
    global $wpdb;
    $table = $wpdb->prefix . 'nichesss_ai_hashes';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        prompt_hash CHAR(32) NOT NULL,
        created_at DATETIME NOT NULL
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});

// Fonction pour obtenir les personnas
function nichesss_ai_get_personnas() {
    // Exemple de personnas, à enrichir via l’admin plus tard
    return [
        [
            'name' => 'Expert SEO',
            'prompt' => 'Rédige comme un expert SEO, structure claire, mots-clés optimisés.'
        ],
        [
            'name' => 'Storyteller',
            'prompt' => 'Raconte une histoire captivante, ton narratif, immersif.'
        ],
        [
            'name' => 'Journaliste',
            'prompt' => 'Adopte un style journalistique, factuel et informatif.'
        ],
    ];
}

// Fonction pour choisir un personna
function nichesss_ai_pick_personna($sujet = '') {
    $personnas = nichesss_ai_get_personnas();
    // À terme, associer certains sujets à certains personnas
    // Pour l’instant, choix aléatoire
    return $personnas[array_rand($personnas)];
}
