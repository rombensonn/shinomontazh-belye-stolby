<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Заявка отправлена — <?= e(SITE_NAME); ?></title>
    <meta name="robots" content="noindex, follow">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<main class="system-page">
    <section class="system-card">
        <p class="eyebrow">Спасибо</p>
        <h1>Заявка отправлена</h1>
        <p>Мастер свяжется с вами, подскажет по времени и сориентирует по работам. Если вопрос срочный — лучше сразу позвонить.</p>
        <div class="button-row">
            <a class="btn btn-primary" href="tel:<?= SITE_PHONE_HREF; ?>">Позвонить</a>
            <a class="btn btn-secondary" href="/">Вернуться на сайт</a>
        </div>
    </section>
</main>
</body>
</html>
