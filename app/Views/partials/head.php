<?php
$appBase = rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/');
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="app-base" content="<?= $appBase ?>">
    <meta name="theme-color" content="#0D4A77">

    <!-- PWA / Android -->
    <meta name="mobile-web-app-capable" content="yes">

    <!-- PWA / iOS (Safari) -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Yemma">

    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= e(APP_NAME) ?></title>

    <!-- Manifest dinámico (servido por PHP con paths correctos) -->
    <link rel="manifest" href="<?= $appBase ?>/manifest.json">

    <!-- Íconos generados por PHP con GD -->
    <link rel="icon"             href="<?= $appBase ?>/icons/icon-192.png" sizes="192x192">
    <link rel="apple-touch-icon" href="<?= $appBase ?>/icons/icon-192.png">
    <link rel="apple-touch-icon" href="<?= $appBase ?>/icons/icon-512.png" sizes="512x512">

    <!-- Splash screens iOS básicos -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;600;700&family=Special+Elite&display=swap" rel="stylesheet">

    <!-- Estilos -->
    <link rel="stylesheet" href="<?= $appBase ?>/css/app.css">
    <link rel="stylesheet" href="<?= $appBase ?>/css/components.css">
