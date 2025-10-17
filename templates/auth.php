<?php
// templates/auth.php
if (!defined('ABSPATH')) exit;

// Detectar modo reset
$is_reset_mode = isset($_GET['action']) && $_GET['action'] === 'reset';
$reset_key = $_GET['key'] ?? '';
$reset_login = $_GET['login'] ?? '';

// Redirigir si ya está logueado
if (is_user_logged_in()) {
    $user = wp_get_current_user();

    if (in_array('administrator', $user->roles)) {
        wp_redirect(admin_url());
    } elseif (in_array('psychologist', $user->roles)) {
        wp_redirect(home_url('/dashboard-psicologo/'));
    } else {
        wp_redirect(home_url('/dashboard-paciente/'));
    }
    exit;
}

get_header();
include OPENMIND_PATH . 'templates/components/toast.php';
?>

    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-primary-600 mb-2">OpenMind</h1>
                <p class="text-gray-600">
                    <?php echo $is_reset_mode ? 'Restablece tu contraseña' : 'Bienvenido a tu espacio de bienestar'; ?>
                </p>
            </div>

            <!-- Card principal -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

                <?php if ($is_reset_mode): ?>
                    <!-- MODO RESET PASSWORD -->
                    <div class="p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Nueva contraseña</h2>

                        <form id="reset-password-form" class="space-y-6">
                            <input type="hidden" name="login" value="<?php echo esc_attr($reset_login); ?>">
                            <input type="hidden" name="key" value="<?php echo esc_attr($reset_key); ?>">

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nueva contraseña
                                </label>
                                <div class="relative">
                                    <input type="password" name="new_password" required minlength="8"
                                           class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none"
                                           id="reset-new-password"
                                           placeholder="Mínimo 8 caracteres"
                                           autocomplete="new-password">
                                    <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 bg-transparent border-0 cursor-pointer shadow-none outline-none p-1">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>

                                <!-- Indicador de fuerza -->
                                <div class="mt-3 space-y-2">
                                    <div class="flex gap-1">
                                        <div class="h-1 flex-1 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="password-strength-bar h-full w-0 transition-all duration-300"></div>
                                        </div>
                                    </div>
                                    <p class="text-xs password-strength-text text-gray-500">
                                        Ingresa al menos 8 caracteres
                                    </p>
                                    <ul class="text-xs space-y-1 text-gray-600" id="password-requirements">
                                        <li data-req="length">
                                            <span class="req-indicator w-2 h-2 rounded-full mr-2 bg-gray-300 inline-block"></span>
                                            Mínimo 8 caracteres
                                        </li>
                                        <li data-req="uppercase">
                                            <span class="req-indicator w-2 h-2 rounded-full mr-2 bg-gray-300 inline-block"></span>
                                            Una letra mayúscula
                                        </li>
                                        <li data-req="number">
                                            <span class="req-indicator w-2 h-2 rounded-full mr-2 bg-gray-300 inline-block"></span>
                                            Un número
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Confirmar contraseña
                                </label>
                                <div class="relative">
                                    <input type="password" name="password_confirm" required minlength="8"
                                           class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none"
                                           id="reset-password-confirm"
                                           placeholder="Repite tu contraseña"
                                           autocomplete="new-password">
                                    <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 bg-transparent border-0 cursor-pointer shadow-none outline-none p-1">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <span class="text-xs text-red-500 mt-1 hidden" data-error="password-match">Las contraseñas no coinciden</span>
                            </div>

                            <button type="submit"
                                    class="w-full px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg border-0 cursor-pointer transition-all hover:bg-primary-700 active:bg-primary-800 shadow-none outline-none">
                                Restablecer Contraseña
                            </button>
                        </form>

                        <div class="mt-6 text-center">
                            <a href="<?php echo home_url('/auth/'); ?>" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                                ← Volver a iniciar sesión
                            </a>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- MODO NORMAL: Login/Registro -->
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

                    <div class="p-8">
                        <!-- Tab Login -->
                        <div class="auth-content" data-content="login">
                            <form id="login-form" class="space-y-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Correo electrónico
                                    </label>
                                    <input type="email" name="email" required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none"
                                           placeholder="tu@email.com" autocomplete="username">
                                    <span class="text-xs text-red-500 mt-1 hidden" data-error="email"></span>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Contraseña
                                    </label>
                                    <div class="relative">
                                        <input type="password" name="password" required
                                               class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none"
                                               placeholder="••••••••" autocomplete="current-password">
                                        <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 bg-transparent border-0 cursor-pointer shadow-none outline-none p-1">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" name="remember"
                                               class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500 shadow-none">
                                        <span class="ml-2 text-gray-600">Recordarme</span>
                                    </label>
                                    <button type="button" id="forgot-password-btn"
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
                                    <input type="text" name="name" required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none"
                                           placeholder="Tu nombre">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Correo electrónico
                                    </label>
                                    <input type="email" name="email" required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none email-validation"
                                           placeholder="tu@email.com" autocomplete="username">
                                    <span class="text-xs text-red-500 mt-1 hidden" data-error="register-email"></span>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Contraseña
                                    </label>
                                    <div class="relative">
                                        <input type="password" name="password" required minlength="8"
                                               class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none"
                                               id="register-password"
                                               placeholder="Mínimo 8 caracteres"
                                               autocomplete="new-password">
                                        <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 bg-transparent border-0 cursor-pointer shadow-none outline-none p-1">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="mt-3 space-y-2">
                                        <div class="flex gap-1">
                                            <div class="h-1 flex-1 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="password-strength-bar h-full w-0 transition-all duration-300"></div>
                                            </div>
                                        </div>
                                        <p class="text-xs password-strength-text text-gray-500">
                                            Ingresa al menos 8 caracteres
                                        </p>
                                        <ul class="text-xs space-y-1 text-gray-600" id="password-requirements">
                                            <li data-req="length">
                                                <span class="req-indicator w-2 h-2 rounded-full mr-2 bg-gray-300 inline-block"></span>
                                                Mínimo 8 caracteres
                                            </li>
                                            <li data-req="uppercase">
                                                <span class="req-indicator w-2 h-2 rounded-full mr-2 bg-gray-300 inline-block"></span>
                                                Una letra mayúscula
                                            </li>
                                            <li data-req="number">
                                                <span class="req-indicator w-2 h-2 rounded-full mr-2 bg-gray-300 inline-block"></span>
                                                Un número
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Confirmar contraseña
                                    </label>
                                    <div class="relative">
                                        <input type="password" name="password_confirm" required minlength="8"
                                               class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none"
                                               id="register-password-confirm"
                                               placeholder="Repite tu contraseña"
                                               autocomplete="new-password">
                                        <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 bg-transparent border-0 cursor-pointer shadow-none outline-none p-1">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                    <span class="text-xs text-red-500 mt-1 hidden" data-error="password-match">Las contraseñas no coinciden</span>
                                </div>

                                <div class="flex items-start">
                                    <input type="checkbox" name="terms" required
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
                <?php endif; ?>
            </div>

            <?php if (!$is_reset_mode): ?>
                <!-- Footer -->
                <p class="text-center text-sm text-gray-600 mt-8">
                    ¿Eres psicólogo? <a href="#" class="text-primary-600 hover:text-primary-700 font-semibold">Contacta al administrador</a>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal recuperar contraseña (solo en modo normal) -->
<?php if (!$is_reset_mode): ?>
    <div id="forgot-password-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Recuperar contraseña</h2>
                <button onclick="closeForgotPasswordModal()" class="text-gray-400 hover:text-gray-600 shadow-none outline-none border-0 bg-transparent cursor-pointer">
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
                    <input type="email" name="email" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all shadow-none"
                           id="forgot-email"
                           placeholder="tu@email.com">
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeForgotPasswordModal()"
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
<?php endif; ?>

    <script>
        const IS_RESET_MODE = <?php echo $is_reset_mode ? 'true' : 'false'; ?>;

        // Sistema de tabs (solo en modo normal)
        if (!IS_RESET_MODE) {
            const TabSystem = {
                init() {
                    this.checkUrlTab();
                    this.bindTabEvents();
                    this.handlePopState();
                },

                checkUrlTab() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const tab = urlParams.get('tab');
                    if (tab === 'register' || tab === 'login') {
                        this.switchToTab(tab);
                    }
                },

                bindTabEvents() {
                    document.querySelectorAll('.auth-tab').forEach(tab => {
                        tab.addEventListener('click', () => {
                            const targetTab = tab.dataset.tab;
                            this.switchToTab(targetTab);
                            this.updateUrl(targetTab);
                        });
                    });
                },

                switchToTab(tabName) {
                    document.querySelectorAll('.auth-tab').forEach(t => {
                        const isActive = t.dataset.tab === tabName;
                        t.classList.toggle('border-primary-600', isActive);
                        t.classList.toggle('text-primary-600', isActive);
                        t.classList.toggle('bg-white', isActive);
                        t.classList.toggle('border-transparent', !isActive);
                        t.classList.toggle('text-gray-500', !isActive);
                        t.classList.toggle('bg-transparent', !isActive);
                    });

                    document.querySelectorAll('.auth-content').forEach(content => {
                        content.classList.toggle('hidden', content.dataset.content !== tabName);
                    });
                },

                updateUrl(tab) {
                    const url = new URL(window.location);
                    url.searchParams.set('tab', tab);
                    window.history.pushState({tab}, '', url);
                },

                handlePopState() {
                    window.addEventListener('popstate', () => this.checkUrlTab());
                }
            };

            // Inicializar TabSystem inmediatamente
            document.addEventListener('DOMContentLoaded', () => TabSystem.init());
        }

        // Sistema de validación de contraseña (para registro Y reset)
        const PasswordValidator = {
            init() {
                const passwordInput = IS_RESET_MODE
                    ? document.getElementById('reset-new-password')
                    : document.getElementById('register-password');

                const confirmInput = IS_RESET_MODE
                    ? document.getElementById('reset-password-confirm')
                    : document.getElementById('register-password-confirm');

                if (passwordInput) {
                    passwordInput.addEventListener('input', (e) => this.checkStrength(e.target.value));
                }

                if (confirmInput) {
                    confirmInput.addEventListener('input', () => this.checkMatch());
                }
            },

            checkStrength(password) {
                const requirements = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    number: /\d/.test(password)
                };

                Object.keys(requirements).forEach(req => {
                    const li = document.querySelector(`[data-req="${req}"]`);
                    if (!li) return;
                    const indicator = li.querySelector('.req-indicator');

                    if (requirements[req]) {
                        indicator.classList.remove('bg-gray-300');
                        indicator.classList.add('bg-green-500');
                        li.classList.add('text-green-600');
                        li.classList.remove('text-gray-600');
                    } else {
                        indicator.classList.add('bg-gray-300');
                        indicator.classList.remove('bg-green-500');
                        li.classList.remove('text-green-600');
                        li.classList.add('text-gray-600');
                    }
                });

                const strength = Object.values(requirements).filter(Boolean).length;
                this.updateStrengthBar(strength, password.length > 0);
            },

            updateStrengthBar(strength, hasPassword) {
                const bar = document.querySelector('.password-strength-bar');
                const text = document.querySelector('.password-strength-text');

                if (!bar || !text) return;

                const configs = {
                    0: { width: '0%', color: 'bg-gray-200', text: 'Ingresa al menos 8 caracteres' },
                    1: { width: '33%', color: 'bg-red-500', text: 'Contraseña débil' },
                    2: { width: '66%', color: 'bg-yellow-500', text: 'Contraseña media' },
                    3: { width: '100%', color: 'bg-green-500', text: 'Contraseña fuerte' }
                };

                const config = hasPassword ? configs[strength] : configs[0];

                bar.className = `password-strength-bar h-full transition-all duration-300 ${config.color}`;
                bar.style.width = config.width;
                text.textContent = config.text;
                text.className = `text-xs password-strength-text ${
                    strength === 0 ? 'text-gray-500' :
                        strength === 1 ? 'text-red-500' :
                            strength === 2 ? 'text-yellow-600' : 'text-green-600'
                }`;
            },

            checkMatch() {
                const passwordId = IS_RESET_MODE ? 'reset-new-password' : 'register-password';
                const confirmId = IS_RESET_MODE ? 'reset-password-confirm' : 'register-password-confirm';

                const password = document.getElementById(passwordId).value;
                const confirm = document.getElementById(confirmId).value;
                const errorSpan = document.querySelector('[data-error="password-match"]');

                if (confirm && password !== confirm) {
                    errorSpan.classList.remove('hidden');
                } else {
                    errorSpan.classList.add('hidden');
                }
            }
        };

        // Toggle mostrar/ocultar contraseña
        document.querySelectorAll('.password-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const svg = this.querySelector('svg');

                if (input.type === 'password') {
                    input.type = 'text';
                    if (svg) svg.setAttribute('data-icon', 'eye-slash');
                } else {
                    input.type = 'password';
                    if (svg) svg.setAttribute('data-icon', 'eye');
                }
            });
        });

        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        // === MODO RESET: Form handler ===
        if (IS_RESET_MODE) {
            document.getElementById('reset-password-form').addEventListener('submit', async (e) => {
                e.preventDefault();

                const password = document.getElementById('reset-new-password').value;
                const confirm = document.getElementById('reset-password-confirm').value;

                if (password !== confirm) {
                    Toast.show('Las contraseñas no coinciden', 'error');
                    return;
                }

                const btn = e.target.querySelector('button[type="submit"]');
                const originalText = btn.textContent;
                btn.disabled = true;
                btn.textContent = 'Restableciendo...';

                try {
                    const formData = new FormData(e.target);
                    formData.append('action', 'openmind_reset_password');
                    formData.append('nonce', '<?php echo wp_create_nonce("openmind_auth"); ?>');

                    const response = await fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        Toast.show('¡Contraseña restablecida! Redirigiendo...', 'success');
                        setTimeout(() => {
                            window.location.href = '<?php echo home_url("/auth/?tab=login"); ?>';
                        }, 1500);
                    } else {
                        Toast.show(data.data?.message || 'Error al restablecer contraseña', 'error');
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
        }

        // === MODO NORMAL: Modal y forms ===
        if (!IS_RESET_MODE) {
            // Validación de email en registro
            document.querySelectorAll('.email-validation').forEach(input => {
                input.addEventListener('blur', function() {
                    const email = this.value;
                    const errorSpan = document.querySelector('[data-error="register-email"]');

                    if (email && !isValidEmail(email)) {
                        if (errorSpan) {
                            errorSpan.textContent = 'Formato de email inválido';
                            errorSpan.classList.remove('hidden');
                        }
                    } else {
                        if (errorSpan) errorSpan.classList.add('hidden');
                    }
                });
            });

            // Modal forgot password
            const openForgotPasswordModal = () => {
                const modal = document.getElementById('forgot-password-modal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');

                const loginEmail = document.querySelector('#login-form input[name="email"]').value;
                if (loginEmail) {
                    document.getElementById('forgot-email').value = loginEmail;
                }
            };

            const closeForgotPasswordModal = () => {
                const modal = document.getElementById('forgot-password-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            document.getElementById('forgot-password-btn').addEventListener('click', openForgotPasswordModal);
            document.getElementById('forgot-password-modal').addEventListener('click', (e) => {
                if (e.target === e.currentTarget) closeForgotPasswordModal();
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

                    Toast.show(
                        data.success ? 'Enlace enviado. Revisa tu correo.' : (data.data?.message || 'Error al enviar enlace'),
                        data.success ? 'success' : 'error'
                    );

                    if (data.success) {
                        closeForgotPasswordModal();
                        e.target.reset();
                    }
                } catch (error) {
                    console.error(error);
                    Toast.show('Error de conexión', 'error');
                } finally {
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            });

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
                        Toast.show('¡Bienvenido! Redirigiendo...', 'success');
                        setTimeout(() => window.location.href = data.data.redirect_url, 1500);
                    } else {
                        Toast.show(data.data?.message || 'Error al iniciar sesión', 'error');
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

            // Register form
            document.getElementById('register-form').addEventListener('submit', async (e) => {
                e.preventDefault();

                const password = document.getElementById('register-password').value;
                const confirm = document.getElementById('register-password-confirm').value;

                if (password !== confirm) {
                    Toast.show('Las contraseñas no coinciden', 'error');
                    return;
                }

                const email = e.target.querySelector('input[name="email"]').value;
                if (!isValidEmail(email)) {
                    Toast.show('Por favor ingresa un email válido', 'error');
                    return;
                }

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
                        Toast.show('¡Cuenta creada! Redirigiendo...', 'success');
                        e.target.reset();
                        setTimeout(() => window.location.href = data.data.redirect_url, 1500);
                    } else {
                        Toast.show(data.data?.message || 'Error al crear cuenta', 'error');
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
        }

        // Inicializar sistemas
        document.addEventListener('DOMContentLoaded', () => {
            PasswordValidator.init();
        });
    </script>

<?php get_footer(); ?>