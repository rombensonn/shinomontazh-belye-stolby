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
    <title>Политика обработки персональных данных — <?= e(SITE_NAME); ?></title>
    <meta name="robots" content="noindex, follow">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main class="system-page">
    <article class="system-card text-page">
        <p class="eyebrow">Документ</p>
        <h1>Политика обработки персональных данных</h1>
        <p>Настоящая политика описывает обработку данных, которые пользователь передаёт через форму заявки на сайте <?= e(SITE_NAME); ?>.</p>
        <h2>Какие данные обрабатываются</h2>
        <p>Имя, телефон, выбранная задача, удобное время визита, комментарий, адрес страницы, пользовательский агент и технический IP-адрес.</p>
        <h2>Цель обработки</h2>
        <p>Связаться с пользователем по заявке, уточнить задачу, ориентировочное время визита и детали работ.</p>
        <h2>Хранение</h2>
        <p>Заявки могут сохраняться в резервном JSONL-файле на сервере и отправляться на email или в Telegram, если эти каналы включены администратором сайта.</p>
        <h2>Согласие</h2>
        <p>Отправляя форму, пользователь подтверждает согласие на обработку данных для ответа на обращение.</p>
        <div class="button-row">
            <a class="btn btn-primary" href="/">Вернуться на сайт</a>
            <a class="btn btn-secondary" href="tel:<?= SITE_PHONE_HREF; ?>">Позвонить</a>
        </div>
    </article>
</main>
</body>
</html>
