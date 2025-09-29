<?php // src/Core/Plugin.php
namespace Openmind\Core;

class Plugin {

    public static function init(): void {
        self::loadHooks();
        self::loadAssets();
    }

    public static function activate(): void {
        Installer::install();
        flush_rewrite_rules();
    }

    public static function deactivate(): void {
        flush_rewrite_rules();
    }

    private static function loadHooks(): void {
        add_action('init', [self::class, 'registerRoles']);
        add_action('init', [self::class, 'registerPostTypes']);
    }

    private static function loadAssets(): void {
        add_action('wp_enqueue_scripts', function() {
            if (!is_user_logged_in()) return;

            wp_enqueue_style('openmind', OPENMIND_URL . 'assets/css/style.css', [], OPENMIND_VERSION);
            wp_enqueue_script('openmind', OPENMIND_URL . 'assets/js/main.js', ['jquery'], OPENMIND_VERSION, true);

            wp_localize_script('openmind', 'openmindData', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('openmind_nonce'),
                'userId' => get_current_user_id()
            ]);
        });
    }

    public static function registerRoles(): void {
        if (get_role('psychologist')) return;

        add_role('psychologist', 'Psicólogo', [
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'manage_patients' => true,
            'manage_activities' => true
        ]);

        add_role('patient', 'Paciente', [
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'view_activities' => true,
            'write_diary' => true
        ]);
    }

    public static function registerPostTypes(): void {
        register_post_type('activity', [
            'labels' => [
                'name' => 'Actividades',
                'singular_name' => 'Actividad'
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'supports' => ['title', 'editor', 'author'],
            'has_archive' => false
        ]);
    }
}