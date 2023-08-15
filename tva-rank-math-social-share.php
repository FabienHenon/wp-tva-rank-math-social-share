<?php
/*
Plugin Name: Thrive Apprentice Rank Math social share
Plugin URI: https://fabien404.fr/
Description: Allow social sharing for Thrive Apprentice courses, modules, and lessons
Version: 1.0.3
Author: Fabien 404
Author URI: https://fabien404.fr/
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit(); // Exit if accessed directly.
}

// Vérifie si Rank Math et Thrive Apprentice sont activés
if (!function_exists('is_plugin_active')) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if (
    !is_plugin_active('seo-by-rank-math/rank-math.php') ||
    !is_plugin_active('thrive-apprentice/thrive-apprentice.php')
) {
    return; // Si Rank Math ou Thrive Apprentice ne sont pas activés, arrête l'exécution du code ici
}

// Modifie les métadonnées pour les types de post de Thrive Apprentice
function custom_metadata_for_rankmath($url)
{
    // Vérifie si le post est du type "tva_course_overview"
    if (
        get_post_type() == 'tva_course_overview' ||
        get_post_type() == 'tva_lesson'
    ) {
        // Créez un nouvel objet de cours en utilisant l'ID
        $course = new TVA_Course_V2(TVA_Course_V2::get_active_course_id());

        return $course->get_cover_image();
    }

    return $url;
}
add_filter(
    'rank_math/opengraph/facebook/image',
    'custom_metadata_for_rankmath'
);
add_filter('rank_math/opengraph/twitter/image', 'custom_metadata_for_rankmath');

// Modifie le titre en utilisant le format défini
function custom_social_title_for_rankmath($title)
{
    // Vérifie si le post est du type "tva_course_overview"
    if (get_post_type() == 'tva_course_overview') {
        if (class_exists('TVA_Course_V2')) {
            // Créez un nouvel objet de cours en utilisant l'ID
            $course = new TVA_Course_V2(TVA_Course_V2::get_active_course_id());

            $format = get_option('title_format', 'Course : %title%');
            return str_replace('%title%', $course->name, $format);
        }
    }

    return $title;
}
add_filter(
    'rank_math/frontend/title',
    'custom_social_title_for_rankmath',
    10,
    2
);

// Modifie la meta description pour le type "tva_course_type"
function custom_meta_description_for_rankmath($description)
{
    // Vérifie si le post est du type "tva_course_overview"
    if (get_post_type() == 'tva_course_overview') {
        if (class_exists('TVA_Course_V2')) {
            // Créez un nouvel objet de cours en utilisant l'ID
            $course = new TVA_Course_V2(TVA_Course_V2::get_active_course_id());

            return wp_trim_words($course->get_excerpt(), 45, '...');
        }
    }

    return $description;
}
add_filter(
    'rank_math/frontend/description',
    'custom_meta_description_for_rankmath'
);

// Ajouter une page d'administration
function custom_social_share_admin_menu()
{
    add_options_page(
        'TVA Rank math social share',
        'TVA Rank math social share',
        'manage_options',
        'custom-social-share',
        'custom_social_share_settings_page'
    );
}
add_action('admin_menu', 'custom_social_share_admin_menu');

// Afficher le contenu de la page d'administration
function custom_social_share_settings_page()
{
    ?>
    <div class="wrap">
        <h2>TVA Rank math social share settings</h2>
        <form action="options.php" method="post">
            <?php
            settings_fields('custom_social_share_options');
            do_settings_sections('custom-social-share');
            submit_button();?>
        </form>
    </div>
    <?php
}

// Initialiser les paramètres
function custom_social_share_admin_init()
{
    register_setting(
        'custom_social_share_options',
        'title_format',
        'sanitize_text_field'
    );

    add_settings_section(
        'custom_social_share_main',
        'Title Settings',
        'custom_social_share_section_text',
        'custom-social-share'
    );

    add_settings_field(
        'title_format',
        'Title Format',
        'custom_social_share_title_format_input',
        'custom-social-share',
        'custom_social_share_main'
    );
}
add_action('admin_init', 'custom_social_share_admin_init');

// Texte des sections
function custom_social_share_section_text()
{
    echo '<p>Define how the title should be formatted. Use <strong>%title%</strong> as a placeholder for the course/module/lesson title.</p>';
}

// Champ de format de titre
function custom_social_share_title_format_input()
{
    $title_format = get_option('title_format', 'Course : %title%');
    echo "<input id='title_format' name='title_format' size='40' type='text' value='{$title_format}' />";
}

?>
