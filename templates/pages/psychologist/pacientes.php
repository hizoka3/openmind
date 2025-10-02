<?php
// templates/pages/psychologist/pacientes.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

$patients = get_users([
    'role' => 'patient',
    'meta_query' => [
        ['key' => 'psychologist_id', 'value' => $user_id, 'compare' => '=']
    ]
]);
?>

<div class="page-pacientes">
    <div class="page-header">
        <h1>Mis Pacientes</h1>
        <button class="btn-primary" id="add-patient">+ Agregar Paciente</button>
    </div>

    <?php if (empty($patients)): ?>
        <div class="empty-state">
            <p>📋 No tienes pacientes asignados aún.</p>
            <button class="btn-secondary" id="add-first-patient">Agregar tu primer paciente</button>
        </div>
    <?php else: ?>
        <div class="patients-table">
            <table class="openmind-table">
                <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Correo</th>
                    <th>Creado en</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($patients as $patient):
                    $last_activity = get_user_meta($patient->ID, 'last_activity_date', true);
                    $pending_count = count(get_posts([
                        'post_type' => 'activity',
                        'meta_query' => [
                            ['key' => 'assigned_to', 'value' => $patient->ID],
                            ['key' => 'completed', 'value' => '0']
                        ],
                        'posts_per_page' => -1,
                        'fields' => 'ids'
                    ]));
                    ?>
                    <tr>
                        <td>
                            <div class="patient-cell">
                                <?php echo get_avatar($patient->ID, 40); ?>
                                <strong><?php echo esc_html($patient->display_name); ?></strong>
                            </div>
                        </td>
                        <td><?php echo esc_html($patient->user_email); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($patient->user_registered)); ?></td>
                        <td>
                                <span class="status-badge <?php echo $last_activity ? 'active' : 'inactive'; ?>">
                                    <?php echo $last_activity ? '🟢 Activo' : '⚪ Inactivo'; ?>
                                </span>
                        </td>
                        <td>
                            <button class="btn-icon" data-action="view-patient" data-patient-id="<?php echo $patient->ID; ?>" title="Ver detalles">
                                👁️
                            </button>
                            <button class="btn-icon" data-action="message-patient" data-patient-id="<?php echo $patient->ID; ?>" title="Enviar mensaje">
                                💬
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>