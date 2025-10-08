<?php
// templates/components/profile-card.php
$user = $args['user'];
$role = $args['role']; // 'patient' or 'psychologist'
$extra_info = $args['extra_info'] ?? []; // Info adicional específica del rol
$stats = $args['stats'] ?? []; // Estadísticas específicas del rol
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Card principal de perfil -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm p-8">
            <!-- Header con avatar -->
            <div class="flex flex-col items-center text-center pb-8 border-b border-gray-200">
                <div class="relative mb-4">
                    <img id="avatar-preview"
                         src="<?php echo esc_url(get_avatar_url($user->ID, ['size' => 120])); ?>"
                         alt="Avatar"
                         class="w-32 h-32 rounded-full border-4 border-primary-100 object-cover">
                    <input type="file"
                           id="avatar-upload"
                           accept="image/*"
                           class="hidden">
                    <button type="button"
                            class="absolute bottom-0 right-0 w-10 h-10 bg-primary-600 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-primary-700 transition-all border-0 cursor-pointer"
                            id="change-avatar"
                            title="Cambiar foto">
                        <i class="fa-solid fa-camera text-sm"></i>
                    </button>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 m-0 mb-1">
                    <?php echo esc_html($user->display_name); ?>
                </h2>
                <p class="text-gray-500 text-sm m-0">
                    <?php echo $role === 'psychologist' ? 'Psicólogo' : 'Paciente'; ?>
                </p>
            </div>

            <!-- Información personal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Correo electrónico
                    </label>
                    <p class="text-gray-900 m-0"><?php echo esc_html($user->user_email); ?></p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Usuario
                    </label>
                    <p class="text-gray-900 m-0"><?php echo esc_html($user->user_login); ?></p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Miembro desde
                    </label>
                    <p class="text-gray-900 m-0">
                        <?php echo date('d/m/Y', strtotime($user->user_registered)); ?>
                    </p>
                </div>

                <!-- Info extra específica del rol -->
                <?php if (!empty($extra_info)): ?>
                    <?php foreach ($extra_info as $info): ?>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php echo esc_html($info['label']); ?>
                            </label>
                            <div class="text-gray-900">
                                <?php echo $info['content']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Botones de acción -->
            <div class="flex flex-col sm:flex-row gap-3 mt-8 pt-8 border-t border-gray-200">
                <button class="flex-1 px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg border-0 cursor-pointer transition-all hover:bg-primary-700 shadow-none outline-none"
                        id="edit-profile-btn">
                    <i class="fa-solid fa-user-pen mr-2"></i>
                    Editar Perfil
                </button>
                <button class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg border-0 cursor-pointer transition-all hover:bg-gray-200 shadow-none outline-none"
                        id="change-password-btn">
                    <i class="fa-solid fa-key mr-2"></i>
                    Cambiar Contraseña
                </button>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm p-8">
            <h3 class="text-lg font-normal text-gray-900 m-0 mb-6">
                <?php echo $role === 'psychologist' ? 'Estadísticas' : 'Mi Progreso'; ?>
            </h3>

            <?php if (!empty($stats)): ?>
                <div class="space-y-6">
                    <?php foreach ($stats as $stat): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-primary-50 rounded-lg flex items-center justify-center">
                                    <i class="<?php echo $stat['icon']; ?> text-primary-500 text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-xl font-medium text-gray-900 m-0">
                                        <?php echo $stat['value']; ?>
                                    </p>
                                    <p class="text-sm text-gray-600 m-0">
                                        <?php echo esc_html($stat['label']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-8">
                    No hay estadísticas disponibles
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Modal Editar Perfil -->
<div id="edit-profile-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 p-4" style="display: none;">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 m-0">Editar Perfil</h2>
                <button type="button" onclick="closeEditProfileModal()" class="text-gray-400 hover:text-gray-600 shadow-none outline-none border-0 bg-transparent cursor-pointer">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>

            <form id="edit-profile-form" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nombre completo
                    </label>
                    <input type="text"
                           name="display_name"
                           value="<?php echo esc_attr($user->display_name); ?>"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none">
                </div>

                <div class="flex gap-3">
                    <button type="button"
                            onclick="closeEditProfileModal()"
                            class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg border-0 cursor-pointer transition-all hover:bg-gray-200 shadow-none outline-none">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg border-0 cursor-pointer transition-all hover:bg-primary-700 shadow-none outline-none">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambiar Contraseña -->
<div id="change-password-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 p-4" style="display: none;">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 m-0">Cambiar Contraseña</h2>
                <button type="button" onclick="closeChangePasswordModal()" class="text-gray-400 hover:text-gray-600 shadow-none outline-none border-0 bg-transparent cursor-pointer">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>

            <form id="change-password-form" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Contraseña actual
                    </label>
                    <input type="password"
                           name="current_password"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nueva contraseña
                    </label>
                    <input type="password"
                           name="new_password"
                           required
                           minlength="8"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none">
                    <p class="mt-2 text-xs text-gray-500">
                        Mínimo 8 caracteres
                    </p>
                </div>

                <div class="flex gap-3">
                    <button type="button"
                            onclick="closeChangePasswordModal()"
                            class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg border-0 cursor-pointer transition-all hover:bg-gray-200 shadow-none outline-none">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg border-0 cursor-pointer transition-all hover:bg-primary-700 shadow-none outline-none">
                        Cambiar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Modals
    const openEditProfileModal = () => {
        document.getElementById('edit-profile-modal').style.display = 'block';
    };

    const closeEditProfileModal = () => {
        document.getElementById('edit-profile-modal').style.display = 'none';
    };

    const openChangePasswordModal = () => {
        document.getElementById('change-password-modal').style.display = 'block';
    };

    const closeChangePasswordModal = () => {
        document.getElementById('change-password-modal').style.display = 'none';
    };

    document.getElementById('edit-profile-btn')?.addEventListener('click', openEditProfileModal);
    document.getElementById('change-password-btn')?.addEventListener('click', openChangePasswordModal);

    // Cerrar al hacer click fuera
    ['edit-profile-modal', 'change-password-modal'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });

    // Form handlers
    document.getElementById('edit-profile-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Guardando...';

        try {
            const formData = new FormData(e.target);
            formData.append('action', 'openmind_update_profile');
            formData.append('nonce', openmindData.nonce);

            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                closeEditProfileModal();
                Toast.show('Perfil actualizado correctamente', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                Toast.show(data.data?.message || 'Error al actualizar perfil', 'error');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        } catch (error) {
            console.error(error);
            Toast.show('Error de conexión', 'error');
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    document.getElementById('change-password-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Cambiando...';

        try {
            const formData = new FormData(e.target);
            formData.append('action', 'openmind_change_password');
            formData.append('nonce', openmindData.nonce);

            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                Toast.show('Contraseña cambiada correctamente', 'success');
                closeChangePasswordModal();
                e.target.reset();
            } else {
                Toast.show(data.data?.message || 'Error al cambiar contraseña', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Error de conexión', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    // Cambiar avatar
    document.getElementById('change-avatar')?.addEventListener('click', function() {
        document.getElementById('avatar-upload').click();
    });

    document.getElementById('avatar-upload')?.addEventListener('change', async function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validar tipo de archivo
        if (!file.type.startsWith('image/')) {
            Toast.show('Por favor selecciona una imagen válida', 'error');
            return;
        }

        // Validar tamaño (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            Toast.show('La imagen no puede superar 2MB', 'error');
            return;
        }

        // Preview inmediato
        const reader = new FileReader();
        const originalSrc = document.getElementById('avatar-preview').src;

        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        };
        reader.readAsDataURL(file);

        // Subir a servidor
        const formData = new FormData();
        formData.append('action', 'openmind_upload_avatar');
        formData.append('nonce', openmindData.nonce);
        formData.append('avatar', file);

        try {
            Toast.show('Subiendo imagen...', 'info', 0);

            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                Toast.show('Avatar actualizado correctamente', 'success');

                // Actualizar todos los avatares en la página
                const avatarUrl = data.data.avatar_url;
                document.querySelectorAll('img[src*="gravatar"], img[id="avatar-preview"]').forEach(img => {
                    img.src = avatarUrl;
                });
            } else {
                Toast.show(data.data?.message || 'Error al subir imagen', 'error');
                // Revertir preview
                document.getElementById('avatar-preview').src = originalSrc;
            }
        } catch (error) {
            console.error(error);
            Toast.show('Error de conexión', 'error');
            // Revertir preview
            document.getElementById('avatar-preview').src = originalSrc;
        }
    });
</script>