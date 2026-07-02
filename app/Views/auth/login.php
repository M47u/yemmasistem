<div class="login-wrap">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="24" cy="24" r="22" fill="#0D4A77" opacity=".12"/>
                    <path d="M12 32 L24 14 L36 32" stroke="#0D4A77" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    <path d="M16 26 L24 26 L32 26" stroke="#0D4A77" stroke-width="2.5" stroke-linecap="round"/>
                    <circle cx="24" cy="14" r="2.5" fill="#0D4A77"/>
                </svg>
            </div>
            <h1 class="login-title"><?= e(APP_NAME) ?></h1>
            <p class="login-subtitle">Sistema de Gestión ISP</p>
        </div>

        <form method="POST" action="<?= url('/login') ?>" class="login-form" id="loginForm">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input
                    class="form-input"
                    type="email"
                    id="email"
                    name="email"
                    placeholder="admin@yemma.local"
                    autocomplete="email"
                    required
                    autofocus
                >
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <input
                    class="form-input"
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                >
            </div>
            <button type="submit" class="btn-login" id="loginBtn">
                Ingresar
            </button>
        </form>
    </div>
</div>
