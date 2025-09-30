<?php // templates/components/diary-list.php
use Openmind\Repositories\DiaryRepository;

$patient_id = $args['patient_id'] ?? get_current_user_id();
$entries = DiaryRepository::getByPatient($patient_id, 10);

$mood_emojis = [
    'feliz' => '😊',
    'triste' => '😢',
    'ansioso' => '😰',
    'neutral' => '😐',
    'enojado' => '😠',
    'calmado' => '😌'
];
?>

<div class="diary-entries">
    <?php if (empty($entries)): ?>
        <p class="empty-state">Aún no has escrito ninguna entrada.</p>
    <?php else: ?>
        <?php foreach ($entries as $entry): ?>
            <div class="diary-entry" data-entry-id="<?php echo $entry->id; ?>">
                <div class="entry-header">
                    <div class="entry-meta">
                        <?php if ($entry->mood): ?>
                            <span class="mood-badge">
                                <?php echo $mood_emojis[$entry->mood] ?? ''; ?>
                                <?php echo esc_html(ucfirst($entry->mood)); ?>
                            </span>
                        <?php endif; ?>

                        <span class="entry-date">
                            <?php echo date('d/m/Y H:i', strtotime($entry->created_at)); ?>
                        </span>
                    </div>

                    <?php if ($patient_id == get_current_user_id()): ?>
                        <button
                            class="btn-icon btn-delete"
                            data-action="delete-diary"
                            data-entry-id="<?php echo $entry->id; ?>"
                            title="Eliminar">
                            🗑️
                        </button>
                    <?php endif; ?>
                </div>

                <div class="entry-content">
                    <?php echo wp_kses_post(wpautop($entry->content)); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>