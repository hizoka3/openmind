<?php // src/Core/Plugin.php
namespace Openmind\Core;

class Plugin {

    public static function init(): void {
        self::loadHelpers();
        self::loadHooks();
        self::loadAssets();
        self::loadControllers();
    }

    private static function loadHelpers(): void {
        require_once OPENMIND_PATH . 'src/helpers.php';
    }

    private static function loadControllers(): void {
        \Openmind\Controllers\ActivityController::init();
        \Openmind\Controllers\MessageController::init();
        \Openmind\Controllers\PatientController::init();
        \Openmind\Controllers\DiaryController::init();
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
        add_filter('template_include', [self::class, 'loadTemplate']);
        add_filter('login_redirect', [self::class, 'redirectAfterLogin'], 10, 3);
    }

    public static function loadTemplate(string $template): string {
        if (is_page('dashboard-psicologo') && current_user_can('manage_patients')) {
            return OPENMIND_PATH . 'templates/dashboard-psychologist.php';
        }
        if (is_page('dashboard-paciente') && current_user_can('view_activities')) {
            return OPENMIND_PATH . 'templates/dashboard-patient.php';
        }
        return $template;
    }

    public static function redirectAfterLogin(string $redirect, string $request, $user): string {
        if (!isset($user->roles)) return $redirect;

        if (in_array('psychologist', $user->roles)) {
            return home_url('/dashboard-psicologo');
        }
        if (in_array('patient', $user->roles)) {
            return home_url('/dashboard-paciente');
        }
        return $redirect;
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