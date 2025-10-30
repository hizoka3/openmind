<?php // src/Core/Plugin.php
namespace Openmind\Core;

use Openmind\Admin\ActivityMetaboxes;

class Plugin {

    public static function init(): void {
        self::loadHelpers();
        self::loadHooks();
        self::loadAssets();
        self::loadControllers();
        self::registerFormActions();
        self::registerAjaxActions();
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

        \Openmind\Admin\PsychologistProfile::register();
        \Openmind\Admin\PatientsAdmin::init();
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

        // DEBUG TEMPORAL - VERIFICAR QUE EL HOOK EXISTE
        error_log('=== HOOKS REGISTRADOS ===');
        error_log('admin_post_openmind_save_session_note registrado: ' . (has_action('admin_post_openmind_save_session_note') ? 'SI' : 'NO'));
    }

    private static function registerAjaxActions(): void {
        // ===== FILTROS DE PACIENTES =====
        add_action('wp_ajax_openmind_filter_patients', [
            '\Openmind\Controllers\PatientController',
            'filterPatients'
        ]);
        // ===== RESPUESTAS DE ACTIVIDADES (Unificado en ResponseController) =====

        // Crear/Editar respuesta del paciente
        add_action('wp_ajax_openmind_submit_response', [
            '\Openmind\Controllers\ResponseController',
            'submitResponse'
        ]);

        // Ocultar respuesta (soft delete)
        add_action('wp_ajax_openmind_hide_response', [
            '\Openmind\Controllers\ResponseController',
            'hideResponse'
        ]);

        // Alias para compatibilidad (redirige a hideResponse)
        add_action('wp_ajax_openmind_delete_response', [
            '\Openmind\Controllers\ResponseController',
            'deleteResponse'
        ]);

        // Comentario del psicólogo sobre actividad
        add_action('wp_ajax_openmind_psychologist_response', [
            '\Openmind\Controllers\ActivityController',
            'psychologistResponse'
        ]);

        add_action('wp_ajax_openmind_render_resource_row', [
            '\Openmind\Admin\ActivityMetaboxes',
            'ajaxRenderResourceRow'
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
        add_action('add_meta_boxes', [self::class, 'registerMetaboxes']);
        add_action('save_post_activity', [self::class, 'saveActivityMeta'], 10, 2);
        add_filter('template_include', [self::class, 'loadTemplate']);
        add_filter('login_redirect', [self::class, 'redirectAfterLogin'], 10, 3);
        add_filter('get_avatar_url', [self::class, 'customAvatarUrl'], 10, 3);

        // === NUEVOS HOOKS PARA BLOQUEAR ACCESO A WP-ADMIN Y OCULTAR BARRA ===
        add_action('after_setup_theme', [self::class, 'hideAdminBar']);
        add_action('admin_init', [self::class, 'restrictAdminAccess']);
        add_filter('show_admin_bar', [self::class, 'shouldShowAdminBar']);
    }

    // === NUEVOS MÉTODOS PARA RESTRICCIÓN ===
    public static function hideAdminBar(): void {
        if (!current_user_can('administrator')) {
            $user = wp_get_current_user();
            if (in_array('psychologist', $user->roles) || in_array('patient', $user->roles)) {
                show_admin_bar(false);
            }
        }
    }

    public static function shouldShowAdminBar($show): bool {
        if (!is_user_logged_in()) return false;

        $user = wp_get_current_user();
        if (in_array('psychologist', $user->roles) || in_array('patient', $user->roles)) {
            return false;
        }
        return $show;
    }

    public static function restrictAdminAccess(): void {
        // NO REDIRIGIR si es AJAX o admin-post.php
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        // NO REDIRIGIR si es admin-post.php
        global $pagenow;
        if ($pagenow === 'admin-post.php') {
            return;
        }

        $user = wp_get_current_user();

        if (!empty($user->roles)) {
            if (in_array('psychologist', $user->roles)) {
                wp_redirect(home_url('/dashboard-psicologo/'));
                exit;
            } elseif (in_array('patient', $user->roles)) {
                wp_redirect(home_url('/dashboard-paciente/'));
                exit;
            }
        }
    }
    // === FIN NUEVOS MÉTODOS ===

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

        // Agregar capability a administrator
        $admin = get_role('administrator');
        if ($admin && !$admin->has_cap('manage_activity_library')) {
            $admin->add_cap('manage_activity_library');
        }
    }

    public static function registerPostTypes(): void {
        // CPT: Biblioteca de Actividades (Solo Admin)
        register_post_type('activity', [
            'labels' => [
                'name' => 'Biblioteca de Actividades',
                'singular_name' => 'Actividad',
                'add_new' => 'Agregar Actividad',
                'add_new_item' => 'Nueva Actividad',
                'edit_item' => 'Editar Actividad',
                'view_item' => 'Ver Actividad',
                'search_items' => 'Buscar Actividades',
                'not_found' => 'No se encontraron actividades',
                'not_found_in_trash' => 'No hay actividades en papelera'
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'capability_type' => 'post',
            'capabilities' => [
                'edit_post' => 'manage_activity_library',
                'read_post' => 'manage_activity_library',
                'delete_post' => 'manage_activity_library',
                'edit_posts' => 'manage_activity_library',
                'edit_others_posts' => 'manage_activity_library',
                'delete_posts' => 'manage_activity_library',
                'publish_posts' => 'manage_activity_library',
                'read_private_posts' => 'manage_activity_library'
            ],
            'supports' => ['title', 'editor'],
            'menu_icon' => 'dashicons-book-alt',
            'menu_position' => 25,
            'has_archive' => false,
            'rewrite' => false,
            'show_in_rest' => false
        ]);

        // CPT: Actividades Asignadas (Oculto - solo vía código)
        register_post_type('activity_assignment', [
            'labels' => [
                'name' => 'Actividades Asignadas',
                'singular_name' => 'Asignación'
            ],
            'public' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'supports' => ['title', 'editor'],
            'has_archive' => false,
            'rewrite' => false
        ]);
    }

    public static function registerMetaboxes(): void {
        ActivityMetaboxes::register();
    }

    public static function saveActivityMeta($post_id, $post): void {
        ActivityMetaboxes::save($post_id, $post);
    }

    private static function loadAssets(): void {
        // ===== FRONTEND =====
        add_action('wp_enqueue_scripts', function() {
            if (is_page(['dashboard-psicologo', 'dashboard-paciente', 'auth'])) {
                // Estilos globales
                wp_enqueue_style('openmind-styles', OPENMIND_URL . 'assets/css/style.css', [], OPENMIND_VERSION);
                wp_enqueue_style('openmind-font-awesome', OPENMIND_URL . 'assets/css/all.min.css', [], OPENMIND_VERSION);

                // Scripts globales
                wp_enqueue_script('openmind-font-awesome', OPENMIND_URL . 'assets/js/fontawesome-all.min.js', ['jquery'], '6.1.1', true);

                // Modal Utils - DEBE CARGARSE ANTES que main.js y activity-detail.js
                wp_enqueue_script('openmind-modal-utils', OPENMIND_URL . 'assets/js/modal-utils.js', ['jquery'], OPENMIND_VERSION, true);

                wp_enqueue_script('openmind-main', OPENMIND_URL . 'assets/js/main.js', ['jquery', 'openmind-modal-utils'], OPENMIND_VERSION, true);
                wp_enqueue_script('openmind-toast', OPENMIND_URL . 'assets/js/toast.js', ['jquery'], OPENMIND_VERSION, true);

                // Script específico para detalle de actividad
                if (isset($_GET['view']) && $_GET['view'] === 'actividad-detalle') {
                    wp_enqueue_script(
                        'openmind-activity-detail',
                        OPENMIND_URL . 'assets/js/activity-detail.js',
                        ['jquery', 'openmind-modal-utils'],
                        OPENMIND_VERSION,
                        true
                    );
                }

                // Localizar datos para JavaScript
                wp_localize_script('openmind-main', 'openmindData', [
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('openmind_nonce'),
                    'userId' => get_current_user_id(),
                    'userRole' => current_user_can('manage_patients') ? 'psychologist' : 'patient'
                ]);
            }
        }, 99);

        // ===== ADMIN (WP-ADMIN) =====
        add_action('admin_enqueue_scripts', function($hook) {
            // Solo cargar en páginas de perfil de usuario
            if ($hook === 'profile.php' || $hook === 'user-edit.php') {
                wp_enqueue_style(
                    'openmind-admin-styles',
                    OPENMIND_URL . 'assets/css/style.css',
                    [],
                    OPENMIND_VERSION
                );
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