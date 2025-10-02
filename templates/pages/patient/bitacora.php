<?php
// templates/pages/patient/bitacora.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$psychologist_id = get_user_meta($user_id, 'psychologist_id', true);
$psychologist = $psychologist_id ? get_userdata($psychologist_id) : null;
?>

<div class="page-bitacora-patient">
    <div class="page-header">
        <h1>Bitácora</h1>
        <p class="page-description">Notas y reflexiones compartidas con tu psicólogo</p>
    </div>

    <?php if ($psychologist): ?>
        <div class="info-box">
            <p>📖 Tu psicólogo <strong><?php echo esc_html($psychologist->display_name); ?></strong> puede ver estas entradas.</p>
        </div>
    <?php endif; ?>

    <div id="diary-entries">
        <?php
        $patient_id = $user_id;
        include OPENMIND_PATH . 'templates/components/diary-list.php';
        ?>
    </div>
</div>