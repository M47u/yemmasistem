<!DOCTYPE html>
<html lang="es">
<head>
<?php include VIEW_PATH . '/partials/head.php'; ?>
</head>
<body class="app-body">
<?php include VIEW_PATH . '/partials/flash.php'; ?>
<main class="app-main">
<?= $content ?>
</main>
<?php include VIEW_PATH . '/partials/nav.php'; ?>
<script>
    const APP_URL   = '<?= APP_URL ?>';
    const CSRF_TOKEN = '<?= csrf_token() ?>';
</script>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/pwa.js') ?>"></script>
</body>
</html>
