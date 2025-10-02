<?php
// templates/pages/psychologist/bitacora.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

$patients = get_users([
    'role' => 'patient',
    'meta_query' => [
        ['key' => 'psychologist_id', 'value' => $user_id, 'compare' => '=']
    ]
]);
?>

<div class="page-bitacora">
    <h1>Bitácora de Pacientes</h1>
    <p class="page-description">Revisa las entradas de bitácora de tus pacientes</p>

    <?php if (empty($patients)): ?>
        <div class="empty-state">
            <p>📖 No tienes pacientes asignados.</p>
        </div>
    <?php else: ?>
        <div class="patients-diary-list">
            <?php foreach ($patients as $patient):
                $entries = \Openmind\Repositories\DiaryRepository::getByPatient($patient->ID, 3);
                ?>
                <div class="patient-diary-section">
                    <div class="section-header">
                        <div class="patient-info">
                            <?php echo get_avatar($patient->ID, 40); ?>
                            <h3><?php echo esc_html($patient->display_name); ?></h3>
                        </div>
                        <a href="#" class="btn-text" data-action="view-all-diary" data-patient-id="<?php echo $patient->ID; ?>">
                            Ver todo
                        </a>
                    </div>

                    <?php if (empty($entries)): ?>
                        <p class="no-entries">Sin entradas aún</p>
                    <?php else: ?>
                        <div class="diary-preview">
                            <?php foreach ($entries as $entry):
                                $mood_emojis = [
                                    'feliz' => '😊',
                                    'triste' => '😢',
                                    'ansioso' => '😰',
                                    'neutral' => '😐',
                                    'enojado' => '😠',
                                    'calmado' => '😌'
                                ];
                                ?>
                                <div class="diary-preview-item">
                                    <div class="entry-meta">
                                        <?php if ($entry->mood): ?>
                                            <span class="mood"><?php echo $mood_emojis[$entry->mood] ?? ''; ?></span>
                                        <?php endif; ?>
                                        <span class="date"><?php echo date('d/m/Y', strtotime($entry->created_at)); ?></span>
                                    </div>
                                    <p class="entry-excerpt">
                                        <?php echo wp_trim_words($entry->content, 20); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>