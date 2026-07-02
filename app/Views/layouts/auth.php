<!DOCTYPE html>
<html lang="es">
<head>
<?php include VIEW_PATH . '/partials/head.php'; ?>
</head>
<body class="auth-body">
<?php include VIEW_PATH . '/partials/flash.php'; ?>
<?= $content ?>
<script>const APP_URL = '<?= APP_URL ?>';</script>
<script src="<?= asset('js/pwa.js') ?>"></script>
</body>
</html>
