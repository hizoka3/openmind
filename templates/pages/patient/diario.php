<?php
// templates/pages/patient/diario.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

// Obtener entradas del diario personal (diferente a bitácora)
global $wpdb;
$entries = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM {$wpdb->prefix}openmind_diary
    WHERE patient_id = %d AND is_private = 1
    ORDER BY created_at DESC
    LIMIT 20
", $user_id));
?>

<div class="page-diario-patient">
    <div class="page-header">
        <h1>Mi Diario Personal</h1>
        <button class="btn-primary" id="new-diary-entry">+ Nueva Entrada</button>
    </div>

    <div class="info-box private">
        <p>🔒 <strong>Privado:</strong> Solo tú puedes ver estas entradas. Tu psicólogo NO tiene acceso a este diario.</p>
    </div>

    <!-- Modal para nueva entrada -->
    <div id="diary-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Nueva Entrada</h2>
                <button class="modal-close">&times;</button>
            </div>

            <form id="diary-form">
                <div class="form-group">
                    <label>¿Cómo te sientes hoy?</label>
                    <div class="mood-selector">
                        <label class="mood-option">
                            <input type="radio" name="mood" value="feliz">
                            <span>😊 Feliz</span>
                        </label>
                        <label class="mood-option">
                            <input type="radio" name="mood" value="triste">
                            <span>😢 Triste</span>
                        </label>
                        <label class="mood-option">
                            <input type="radio" name="mood" value="ansioso">
                            <span>😰 Ansioso</span>
                        </label>
                        <label class="mood-option">
                            <input type="radio" name="mood" value="neutral">
                            <span>😐 Neutral</span>
                        </label>
                        <label class="mood-option">
                            <input type="radio" name="mood" value="enojado">
                            <span>😠 Enojado</span>
                        </label>
                        <label class="mood-option">
                            <input type="radio" name="mood" value="calmado">
                            <span>😌 Calmado</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Escribe tus pensamientos</label>
                    <?php
                    wp_editor('', 'diary_content', [
                        'textarea_name' => 'content',
                        'media_buttons' => false,
                        'textarea_rows' => 10,
                        'teeny' => true,
                        'quicktags' => false
                    ]);
                    ?>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" id="cancel-diary">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar Entrada</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de entradas -->
    <div class="diary-entries-list">
        <?php if (empty($entries)): ?>
            <div class="empty-state">
                <p>✍️ Aún no has escrito ninguna entrada personal.</p>
                <button class="btn-secondary" id="start-writing">Comenzar a escribir</button>
            </div>
        <?php else: ?>
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
                <div class="diary-entry-card">
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
                        <button class="btn-icon" data-action="delete-diary" data-entry-id="<?php echo $entry->id; ?>">
                            🗑️
                        </button>
                    </div>

                    <div class="entry-content">
                        <?php echo wp_kses_post($entry->content); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // Modal handling
    const modal = document.getElementById('diary-modal');
    const newEntryBtn = document.getElementById('new-diary-entry');
    const startWritingBtn = document.getElementById('start-writing');
    const cancelBtn = document.getElementById('cancel-diary');
    const closeBtn = document.querySelector('.modal-close');

    [newEntryBtn, startWritingBtn].forEach(btn => {
        btn?.addEventListener('click', () => modal.style.display = 'flex');
    });

    [cancelBtn, closeBtn].forEach(btn => {
        btn?.addEventListener('click', () => modal.style.display = 'none');
    });

    // Enviar entrada
    document.getElementById('diary-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData();
        formData.append('action', 'openmind_save_diary');
        formData.append('nonce', openmindData.nonce);
        formData.append('content', tinyMCE.get('diary_content').getContent());
        formData.append('mood', document.querySelector('input[name="mood"]:checked')?.value || '');
        formData.append('is_private', '1');

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                location.reload();
            } else {
                alert('Error al guardar la entrada');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al guardar la entrada');
        }
    });
</script>