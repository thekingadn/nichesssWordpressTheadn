<?php
// Page d’options pour configurer la clé API Nichesss et les paramètres par défaut
add_action('admin_menu', function() {
    add_options_page(
        'Nichesss AI',
        'Nichesss AI',
        'manage_options',
        'nichesss-ai',
        'nichesss_ai_options_page'
    );
    add_menu_page(
        'Générer un article IA',
        'Article IA',
        'manage_options',
        'nichesss-ai-generate',
        'nichesss_ai_generate_page',
        'dashicons-edit',
        26
    );
});

function nichesss_ai_options_page() {
    ?>
    <div class="wrap">
        <h1>Nichesss AI - Paramètres</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('nichesss_ai_options');
            do_settings_sections('nichesss_ai');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', function() {
    register_setting('nichesss_ai_options', 'nichesss_ai_api_key');
    add_settings_section('nichesss_ai_section', 'Paramètres API', null, 'nichesss_ai');
    add_settings_field(
        'nichesss_ai_api_key',
        'Clé API Nichesss',
        function() {
            $value = esc_attr(get_option('nichesss_ai_api_key'));
            echo "<input type='text' name='nichesss_ai_api_key' value='$value' size='50' />";
        },
        'nichesss_ai',
        'nichesss_ai_section'
    );
})

add_action('admin_init', function() {
    register_setting('nichesss_ai_options', 'nichesss_ai_api_key');
    add_settings_section('nichesss_ai_section', 'Paramètres API', null, 'nichesss_ai');
    add_settings_field(
        'nichesss_ai_api_key',
        'Clé API Nichesss',
        function() {
            $value = esc_attr(get_option('nichesss_ai_api_key'));
            echo "<input type='text' name='nichesss_ai_api_key' value='$value' size='50' />";
        },
        'nichesss_ai',
        'nichesss_ai_section'
    );
});

function nichesss_ai_generate_page() {
    if (isset($_POST['nichesss_generate'])) {
        $theme = sanitize_text_field($_POST['nichesss_theme'] ?? '');
        $sujet = sanitize_text_field($_POST['nichesss_sujet'] ?? '');
        $post_id = nichesss_ai_generate_article($theme, $sujet);
        if ($post_id) {
            echo '<div class="notice notice-success"><p>Article généré en brouillon ! <a href="' . get_edit_post_link($post_id) . '">Voir l\'article</a></p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Erreur ou doublon détecté.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1>Générer un article IA</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="nichesss_theme">Thème</label></th>
                    <td><input type="text" name="nichesss_theme" id="nichesss_theme" value="" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="nichesss_sujet">Sujet</label></th>
                    <td><input type="text" name="nichesss_sujet" id="nichesss_sujet" value="" class="regular-text" /></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="nichesss_generate" class="button-primary" value="Générer" /></p>
        </form>
    </div>
    <?php
}