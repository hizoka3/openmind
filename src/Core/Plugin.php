<?php // src/Core/Plugin.php
namespace Openmind\Core;

class Plugin {

    public static function init(): void {
        self::loadHelpers();
        self::loadHooks();
        self::loadAssets();
        self::loadControllers();
        self::registerFormActions();
    }

    private static function loadHelpers(): void {
        require_once OPENMIND_PATH . 'src/helpers.php';
    }

    private static function loadControllers(): void {
        \Openmind\Controllers\ActivityController::init();
        \Openmind\Controllers\MessageController::init();
        \Openmind\Controllers\PatientController::init();
        \Openmind\Controllers\DiaryController::init();
        \Openmind\Controllers\SessionNoteController::init();
        \Openmind\Controllers\AttachmentController::init();
        \Openmind\Controllers\AuthController::init();
    }

    private static function registerFormActions(): void {
        // Session Notes (Bitácora)
        add_action('admin_post_openmind_save_session_note', [
            '\Openmind\Controllers\SessionNoteController',
            'save'
        ]);

        add_action('admin_post_openmind_update_session_note', [
            '\Openmind\Controllers\SessionNoteController',
            'update'
        ]);

        // Diario paciente
        add_action('admin_post_openmind_save_patient_diary', [
            '\Openmind\Controllers\DiaryController',
            'savePatientDiary'
        ]);
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
        add_filter('get_avatar_url', [self::class, 'customAvatarUrl'], 10, 3);
    }

    public static function loadTemplate(string $template): string {
        if (is_page('auth')) {
            return OPENMIND_PATH . 'templates/auth.php';
        }
        if (is_page(['dashboard-psicologo', 'dashboard-paciente']) && !is_user_logged_in()) {
            wp_redirect(home_url('/auth/'));
            exit;
        }
        if (is_page('dashboard-psicologo') && current_user_can('manage_patients')) {
            return OPENMIND_PATH . 'templates/dashboard-psychologist.php';
        }
        if (is_page('dashboard-paciente') && current_user_can('view_activities')) {
            return OPENMIND_PATH . 'templates/dashboard-patient.php';
        }
        return $template;
    }

    public static function redirectAfterLogin($redirect_to, $request, $user) {
        if (isset($user->roles) && is_array($user->roles)) {
            if (in_array('psychologist', $user->roles)) {
                return home_url('/dashboard-psicologo/');
            }
            if (in_array('patient', $user->roles)) {
                return home_url('/dashboard-paciente/');
            }
        }
        return $redirect_to;
    }

    public static function registerRoles(): void {
        if (!get_role('psychologist')) {
            add_role('psychologist', 'Psicólogo', [
                'read' => true,
                'manage_patients' => true,
                'view_activities' => true
            ]);
        }

        if (!get_role('patient')) {
            add_role('patient', 'Paciente', [
                'read' => true,
                'view_activities' => true,
                'write_diary' => true
            ]);
        }
    }

    public static function registerPostTypes(): void {
        register_post_type('activity', [
            'labels' => [
                'name' => 'Actividades',
                'singular_name' => 'Actividad'
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'capability_type' => 'post',
            'supports' => ['title', 'editor'],
            'menu_icon' => 'dashicons-clipboard'
        ]);
    }

    private static function loadAssets(): void {
        add_action('wp_enqueue_scripts', function() {
            if (is_page(['dashboard-psicologo', 'dashboard-paciente', 'auth'])) {
                wp_enqueue_style('openmind-styles', OPENMIND_URL . 'assets/css/style.css', [], OPENMIND_VERSION);
                wp_enqueue_script('openmind-main', OPENMIND_URL . 'assets/js/main.js', ['jquery'], OPENMIND_VERSION, true);
                wp_enqueue_script('openmind-toast', OPENMIND_URL . 'assets/js/toast.js', [], OPENMIND_VERSION, true);

                wp_localize_script('openmind-main', 'openmindData', [
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('openmind_nonce'),
                    'userId' => get_current_user_id()
                ]);
            }
        });
    }

    public static function customAvatarUrl(string $url, $id_or_email, array $args): string {
        // Obtener user ID
        $user_id = null;

        if (is_numeric($id_or_email)) {
            $user_id = (int) $id_or_email;
        } elseif (is_object($id_or_email) && isset($id_or_email->user_id)) {
            $user_id = (int) $id_or_email->user_id;
        } elseif (is_string($id_or_email)) {
            $user = get_user_by('email', $id_or_email);
            if ($user) {
                $user_id = $user->ID;
            }
        }

        if (!$user_id) {
            return $url;
        }

        // Buscar avatar custom
        $avatar_id = get_user_meta($user_id, 'openmind_avatar_id', true);

        if ($avatar_id) {
            $avatar_url = wp_get_attachment_url($avatar_id);
            if ($avatar_url) {
                return $avatar_url;
            }
        }

        return $url;
    }
}