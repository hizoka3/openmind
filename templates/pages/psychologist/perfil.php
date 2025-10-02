<?php
// templates/pages/psychologist/perfil.php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
?>

<div class="page-perfil">
    <h1>Mi Perfil</h1>

    <div class="profile-layout">
        <div class="profile-card">
            <div class="profile-header">
                <?php echo get_avatar($current_user->ID, 120); ?>
                <button class="btn-secondary btn-sm" id="change-avatar">Cambiar foto</button>
            </div>

            <div class="profile-info">
                <div class="info-group">
                    <label>Nombre completo</label>
                    <p><?php echo esc_html($current_user->display_name); ?></p>
                </div>

                <div class="info-group">
                    <label>Correo electrónico</label>
                    <p><?php echo esc_html($current_user->user_email); ?></p>
                </div>

                <div class="info-group">
                    <label>Usuario</label>
                    <p><?php echo esc_html($current_user->user_login); ?></p>
                </div>

                <div class="info-group">
                    <label>Rol</label>
                    <p>Psicólogo</p>
                </div>

                <div class="info-group">
                    <label>Miembro desde</label>
                    <p><?php echo date('d/m/Y', strtotime($current_user->user_registered)); ?></p>
                </div>
            </div>

            <div class="profile-actions">
                <button class="btn-primary" id="edit-profile">Editar Perfil</button>
                <button class="btn-secondary" id="change-password">Cambiar Contraseña</button>
            </div>
        </div>

        <div class="profile-stats">
            <h3>Estadísticas</h3>
            <?php
            $patients_count = count(get_users([
                'role' => 'patient',
                'meta_query' => [
                    ['key' => 'psychologist_id', 'value' => $current_user->ID, 'compare' => '=']
                ]
            ]));

            $activities_count = count(get_posts([
                'post_type' => 'activity',
                'author' => $current_user->ID,
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]));
            ?>

            <div class="stats-list">
                <div class="stat-item">
                    <span class="stat-value"><?php echo $patients_count; ?></span>
                    <span class="stat-label">Pacientes activos</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo $activities_count; ?></span>
                    <span class="stat-label">Actividades creadas</span>
                </div>
            </div>
        </div>
    </div>
</div>