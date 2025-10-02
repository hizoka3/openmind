<?php
// templates/pages/patient/perfil.php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$psychologist_id = get_user_meta($current_user->ID, 'psychologist_id', true);
$psychologist = $psychologist_id ? get_userdata($psychologist_id) : null;
?>

<div class="page-perfil-patient">
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
                    <label>Miembro desde</label>
                    <p><?php echo date('d/m/Y', strtotime($current_user->user_registered)); ?></p>
                </div>

                <?php if ($psychologist): ?>
                    <div class="info-group">
                        <label>Mi psicólogo</label>
                        <div class="psychologist-mini">
                            <?php echo get_avatar($psychologist->ID, 40); ?>
                            <span><?php echo esc_html($psychologist->display_name); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="profile-actions">
                <button class="btn-primary" id="edit-profile">Editar Perfil</button>
                <button class="btn-secondary" id="change-password">Cambiar Contraseña</button>
            </div>
        </div>

        <div class="profile-stats">
            <h3>Mi Progreso</h3>
            <?php
            $completed_activities = count(get_posts([
                'post_type' => 'activity',
                'meta_query' => [
                    ['key' => 'assigned_to', 'value' => $current_user->ID],
                    ['key' => 'completed', 'value' => '1']
                ],
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]));

            $total_activities = count(get_posts([
                'post_type' => 'activity',
                'meta_query' => [
                    ['key' => 'assigned_to', 'value' => $current_user->ID]
                ],
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]));

            $completion_rate = $total_activities > 0 ? round(($completed_activities / $total_activities) * 100) : 0;
            ?>

            <div class="stats-list">
                <div class="stat-item">
                    <span class="stat-value"><?php echo $completed_activities; ?></span>
                    <span class="stat-label">Actividades completadas</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo $completion_rate; ?>%</span>
                    <span class="stat-label">Tasa de completitud</span>
                </div>
            </div>
        </div>
    </div>
</div>