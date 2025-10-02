<?php
// templates/pages/psychologist/mensajeria.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

// Obtener pacientes con mensajes
global $wpdb;
$conversations = $wpdb->get_results($wpdb->prepare("
    SELECT DISTINCT 
        CASE 
            WHEN m.sender_id = %d THEN m.receiver_id
            ELSE m.sender_id
        END as other_user_id,
        MAX(m.created_at) as last_message_at,
        SUM(CASE WHEN m.receiver_id = %d AND m.is_read = 0 THEN 1 ELSE 0 END) as unread_count
    FROM {$wpdb->prefix}openmind_messages m
    WHERE m.sender_id = %d OR m.receiver_id = %d
    GROUP BY other_user_id
    ORDER BY last_message_at DESC
", $user_id, $user_id, $user_id, $user_id));
?>

<div class="page-mensajeria">
    <h1>Mensajería</h1>

    <?php if (empty($conversations)): ?>
        <div class="empty-state">
            <p>💬 No tienes conversaciones aún.</p>
            <p class="help-text">Las conversaciones aparecerán aquí cuando envíes o recibas mensajes.</p>
        </div>
    <?php else: ?>
        <div class="messages-layout">
            <div class="conversations-list">
                <?php foreach ($conversations as $conv):
                    $other_user = get_userdata($conv->other_user_id);
                    if (!$other_user) continue;
                    ?>
                    <div class="conversation-item <?php echo $conv->unread_count > 0 ? 'has-unread' : ''; ?>"
                         data-user-id="<?php echo $conv->other_user_id; ?>">
                        <div class="conv-avatar">
                            <?php echo get_avatar($conv->other_user_id, 50); ?>
                        </div>
                        <div class="conv-info">
                            <h4><?php echo esc_html($other_user->display_name); ?></h4>
                            <p class="last-message-time">
                                <?php echo human_time_diff(strtotime($conv->last_message_at), current_time('timestamp')); ?> atrás
                            </p>
                        </div>
                        <?php if ($conv->unread_count > 0): ?>
                            <span class="unread-badge"><?php echo $conv->unread_count; ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="message-thread" id="message-thread">
                <div class="empty-thread">
                    <p>← Selecciona una conversación para ver los mensajes</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>