<?php
// templates/auth.php
if (!defined('ABSPATH')) exit;

// Redirigir si ya está logueado
if (is_user_logged_in()) {
    $user = wp_get_current_user();
    if (in_array('psychologist', $user->roles)) {
        wp_redirect(home_url('/dashboard-psicologo/'));
    } else {
        wp_redirect(home_url('/dashboard-paciente/'));
    }
    exit;
}

get_header();
?>

    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-primary-600 mb-2">OpenMind</h1>
                <p class="text-gray-600">Bienvenido a tu espacio de bienestar</p>
            </div>

            <!-- Card principal -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Tabs -->
                <div class="flex border-b border-gray-200 bg-gray-50">
                    <button class="auth-tab flex-1 px-6 py-4 text-center font-semibold bg-white border-b-4 border-primary-600 text-primary-600 transition-all shadow-none outline-none"
                            data-tab="login">
                        Iniciar Sesión
                    </button>
                    <button class="auth-tab flex-1 px-6 py-4 text-center font-semibold bg-transparent border-b-4 border-transparent text-gray-500 transition-all hover:text-gray-700 hover:bg-white shadow-none outline-none"
                            data-tab="register">
                        Crear Cuenta
                    </button>
                </div>

                <!-- Contenido de los tabs -->
                <div class="p-8">
                    <!-- Tab Login -->
                    <div class="auth-content" data-content="login">
                        <form id="login-form" class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Correo electrónico
                                </label>
                                <input type="email"
                                       name="email"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none"
                                       placeholder="tu@email.com">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Contraseña
                                </label>
                                <input type="password"
                                       name="password"
                                       required
                                       minlength="8"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none"
                                       placeholder="••••••••">
                            </div>

                            <div class="flex items-center justify-between text-sm">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           name="remember"
                                           class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500 shadow-none">
                                    <span class="ml-2 text-gray-600">Recordarme</span>
                                </label>
                                <button type="button"
                                        id="forgot-password-btn"
                                        class="text-primary-600 hover:text-primary-700 font-medium bg-transparent border-0 cursor-pointer shadow-none outline-none">
                                    ¿Olvidaste tu contraseña?
                                </button>
                            </div>

                            <button type="submit"
                                    class="w-full px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg border-0 cursor-pointer transition-all hover:bg-primary-700 active:bg-primary-800 shadow-none outline-none">
                                Iniciar Sesión
                            </button>
                        </form>
                    </div>

                    <!-- Tab Registro -->
                    <div class="auth-content hidden" data-content="register">
                        <form id="register-form" class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nombre completo
                                </label>
                                <input type="text"
                                       name="name"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none"
                                       placeholder="Tu nombre">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Correo electrónico
                                </label>
                                <input type="email"
                                       name="email"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none"
                                       placeholder="tu@email.com">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Contraseña
                                </label>
                                <input type="password"
                                       name="password"
                                       required
                                       minlength="8"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none"
                                       placeholder="Mínimo 8 caracteres">
                                <p class="mt-2 text-xs text-gray-500">
                                    La contraseña debe tener al menos 8 caracteres
                                </p>
                            </div>

                            <div class="flex items-start">
                                <input type="checkbox"
                                       name="terms"
                                       required
                                       class="w-4 h-4 mt-1 text-primary-600 border-gray-300 rounded focus:ring-primary-500 shadow-none">
                                <label class="ml-2 text-sm text-gray-600">
                                    Acepto los <a href="#" class="text-primary-600 hover:text-primary-700 font-medium">términos y condiciones</a>
                                </label>
                            </div>

                            <button type="submit"
                                    class="w-full px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg border-0 cursor-pointer transition-all hover:bg-primary-700 active:bg-primary-800 shadow-none outline-none">
                                Crear Cuenta
                            </button>

                            <p class="text-xs text-center text-gray-500">
                                Al crear tu cuenta, serás registrado como paciente
                            </p>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-sm text-gray-600 mt-8">
                ¿Eres psicólogo? <a href="#" class="text-primary-600 hover:text-primary-700 font-semibold">Contacta al administrador</a>
            </p>
        </div>
    </div>

    <!-- Toast para notificaciones -->
    <div id="auth-toast" class="fixed top-4 right-4 max-w-sm w-full transform translate-x-full transition-transform duration-300 z-50">
        <div class="bg-white rounded-lg shadow-lg p-4 border-l-4" id="toast-content">
            <div class="flex items-start">
                <div class="flex-shrink-0 text-lg" id="toast-icon"></div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900" id="toast-message"></p>
                </div>
                <button onclick="hideToast()" class="ml-4 text-gray-400 hover:text-gray-600 shadow-none outline-none">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal recuperar contraseña -->
    <div id="forgot-password-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Recuperar contraseña</h2>
                <button onclick="closeForgotPasswordModal()" class="text-gray-400 hover:text-gray-600 shadow-none outline-none">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>

            <p class="text-gray-600 mb-6">
                Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
            </p>

            <form id="forgot-password-form" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Correo electrónico
                    </label>
                    <input type="email"
                           name="email"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none"
                           placeholder="tu@email.com">
                </div>

                <div class="flex gap-3">
                    <button type="button"
                            onclick="closeForgotPasswordModal()"
                            class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg border-0 cursor-pointer transition-all hover:bg-gray-200 shadow-none outline-none">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg border-0 cursor-pointer transition-all hover:bg-primary-700 active:bg-primary-800 shadow-none outline-none">
                        Enviar enlace
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tabs
        document.querySelectorAll('.auth-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const targetTab = this.dataset.tab;

                // Actualizar tabs
                document.querySelectorAll('.auth-tab').forEach(t => {
                    if (t.dataset.tab === targetTab) {
                        t.classList.remove('border-transparent', 'text-gray-500', 'bg-transparent');
                        t.classList.add('border-primary-600', 'text-primary-600', 'bg-white');
                    } else {
                        t.classList.remove('border-primary-600', 'text-primary-600', 'bg-white');
                        t.classList.add('border-transparent', 'text-gray-500', 'bg-transparent');
                    }
                });

                // Mostrar contenido
                document.querySelectorAll('.auth-content').forEach(content => {
                    if (content.dataset.content === targetTab) {
                        content.classList.remove('hidden');
                    } else {
                        content.classList.add('hidden');
                    }
                });
            });
        });

        // Modal recuperar contraseña
        function openForgotPasswordModal() {
            const modal = document.getElementById('forgot-password-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeForgotPasswordModal() {
            const modal = document.getElementById('forgot-password-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('forgot-password-btn').addEventListener('click', openForgotPasswordModal);

        // Cerrar modal al hacer click fuera
        document.getElementById('forgot-password-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeForgotPasswordModal();
            }
        });

        // Forgot password form
        document.getElementById('forgot-password-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Enviando...';

            try {
                const formData = new FormData(e.target);
                formData.append('action', 'openmind_forgot_password');
                formData.append('nonce', '<?php echo wp_create_nonce("openmind_auth"); ?>');

                const response = await fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showToast('Enlace enviado. Revisa tu correo.', 'success');
                    closeForgotPasswordModal();
                    e.target.reset();
                } else {
                    showToast(data.data.message || 'Error al enviar enlace', 'error');
                }

                btn.disabled = false;
                btn.textContent = originalText;
            } catch (error) {
                console.error(error);
                showToast('Error de conexión', 'error');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });

        // Toast notifications
        function showToast(message, type = 'success') {
            const toast = document.getElementById('auth-toast');
            const content = document.getElementById('toast-content');
            const icon = document.getElementById('toast-icon');
            const messageEl = document.getElementById('toast-message');

            // Configurar colores según tipo
            const colors = {
                success: { border: 'border-green-500', icon: '✓' },
                error: { border: 'border-red-500', icon: '✕' },
                info: { border: 'border-blue-500', icon: 'ℹ' }
            };

            const config = colors[type] || colors.info;

            content.className = `bg-white rounded-lg shadow-lg p-4 border-l-4 ${config.border}`;
            icon.textContent = config.icon;
            messageEl.textContent = message;

            toast.classList.remove('translate-x-full');

            setTimeout(() => hideToast(), 5000);
        }

        function hideToast() {
            document.getElementById('auth-toast').classList.add('translate-x-full');
        }

        // Login form
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Iniciando sesión...';

            try {
                const formData = new FormData(e.target);
                formData.append('action', 'openmind_login');
                formData.append('nonce', '<?php echo wp_create_nonce("openmind_auth"); ?>');

                const response = await fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showToast('¡Bienvenido! Redirigiendo...', 'success');
                    setTimeout(() => {
                        window.location.href = data.data.redirect_url;
                    }, 1500);
                } else {
                    showToast(data.data.message || 'Error al iniciar sesión', 'error');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            } catch (error) {
                console.error(error);
                showToast('Error de conexión', 'error');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });

        // Register form
        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Creando cuenta...';

            try {
                const formData = new FormData(e.target);
                formData.append('action', 'openmind_register');
                formData.append('nonce', '<?php echo wp_create_nonce("openmind_auth"); ?>');

                const response = await fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showToast('¡Cuenta creada! Redirigiendo...', 'success');
                    e.target.reset();
                    setTimeout(() => {
                        window.location.href = data.data.redirect_url;
                    }, 1500);
                } else {
                    showToast(data.data.message || 'Error al crear cuenta', 'error');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            } catch (error) {
                console.error(error);
                showToast('Error de conexión', 'error');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    </script>

<?php get_footer(); ?>