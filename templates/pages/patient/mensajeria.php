<?php
// templates/pages/patient/mensajeria.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$psychologist_id = get_user_meta($user_id, 'psychologist_id', true);
$psychologist = $psychologist_id ? get_userdata($psychologist_id) : null;
?>

<div class="page-mensajeria-patient">
    <h1>Mensajería</h1>

    <?php if (!$psychologist): ?>
        <div class="empty-state">
            <p>👨‍⚕️ Aún no tienes un psicólogo asignado.</p>
        </div>
    <?php else: ?>
        <div class="message-container">
            <div class="chat-header">
                <div class="psychologist-info">
                    <?php echo get_avatar($psychologist->ID, 50); ?>
                    <div>
                        <h3><?php echo esc_html($psychologist->display_name); ?></h3>
                        <p class="subtitle">Tu psicólogo</p>
                    </div>
                </div>
            </div>

            <div class="chat-messages" id="chat-messages">
                <div class="loading">Cargando mensajes...</div>
            </div>

            <div class="chat-input">
                <form id="message-form">
                    <input type="hidden" name="receiver_id" value="<?php echo $psychologist_id; ?>">
                    <textarea
                        name="message"
                        placeholder="Escribe tu mensaje..."
                        rows="3"
                        required></textarea>
                    <button type="submit" class="btn-primary">Enviar</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Cargar mensajes al iniciar
    document.addEventListener('DOMContentLoaded', function() {
        const psychologistId = <?php echo $psychologist_id ?? 0; ?>;
        if (psychologistId) {
            loadMessages(psychologistId);
        }
    });

    async function loadMessages(otherUserId) {
        const formData = new FormData();
        formData.append('action', 'openmind_get_messages');
        formData.append('nonce', openmindData.nonce);
        formData.append('other_user_id', otherUserId);

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                renderMessages(data.data.messages);
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    function renderMessages(messages) {
        const container = document.getElementById('chat-messages');
        const currentUserId = <?php echo $user_id; ?>;

        if (messages.length === 0) {
            container.innerHTML = '<div class="empty-state">No hay mensajes aún. ¡Inicia la conversación!</div>';
            return;
        }

        container.innerHTML = messages.map(msg => `
        <div class="message ${msg.sender_id == currentUserId ? 'sent' : 'received'}">
            <div class="message-content">${msg.message}</div>
            <div class="message-time">${new Date(msg.created_at).toLocaleString()}</div>
        </div>
    `).join('');

        container.scrollTop = container.scrollHeight;
    }

    // Enviar mensaje
    document.getElementById('message-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'openmind_send_message');
        formData.append('nonce', openmindData.nonce);

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                this.reset();
                loadMessages(<?php echo $psychologist_id ?? 0; ?>);
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    });