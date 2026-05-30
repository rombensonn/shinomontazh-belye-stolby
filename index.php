<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$csrf = csrf_token();
$canonical = rtrim(SITE_URL, '/') . '/';
$currentUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://')
    . ($_SERVER['HTTP_HOST'] ?? 'localhost')
    . ($_SERVER['REQUEST_URI'] ?? '/');

$reviews = [
    ['name' => 'Артем Жариков', 'date' => '29 апреля 2025', 'text' => 'Спускало колесо, быстро нашёл причину, в течение 15 минут всё было готово. Рекомендую.'],
    ['name' => 'Наталья Б.', 'date' => '5 ноября 2025', 'text' => 'Договорились о времени. Всё чётко по делу и по времени: замена масла, фильтра и смена резины.'],
    ['name' => 'Евгений А.', 'date' => '21 октября 2024', 'text' => 'Хороший домашний мини-сервис: мелкий ремонт, масло, колодки, шиномонтаж. Можно заранее записаться.'],
    ['name' => 'IRazSE', 'date' => '19 июня 2024', 'text' => 'Быстро и качественно заклеили прокол от толстого самореза.'],
    ['name' => 'Борис', 'date' => '9 июля 2025', 'text' => 'Отличный сервис — шиномонтаж, мелкий ремонт, замена тормозных колодок.'],
    ['name' => 'Роман Давыденко', 'date' => '14 августа 2021', 'text' => 'Всегда перекидываю здесь лето на зиму и обратно, можно договориться об удобном времени.'],
    ['name' => 'Владимир Глебов', 'date' => '4 июля 2020', 'text' => 'По шиномонтажу делает качественно и быстро.'],
    ['name' => 'Елена Л.', 'date' => '23 марта 2024', 'text' => 'Быстро, недорого, качественно. Рекомендую.'],
];

function lead_form(string $id, string $csrf, string $currentUrl, string $modifier = ''): void
{
    ?>
    <form class="lead-form <?= e($modifier); ?>" id="<?= e($id); ?>" action="/api/lead.php" method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($csrf); ?>">
        <input type="hidden" name="page_url" value="<?= e($currentUrl); ?>">
        <input type="hidden" name="user_agent" value="">
        <label class="hp-field">Не заполняйте это поле
            <input type="text" name="honeypot" tabindex="-1" autocomplete="off">
        </label>

        <div class="form-grid">
            <label>
                <span>Имя</span>
                <input type="text" name="name" minlength="2" maxlength="60" placeholder="Как к вам обращаться" required>
            </label>
            <label>
                <span>Телефон</span>
                <input type="tel" name="phone" inputmode="tel" placeholder="+7 (___) ___-__-__" required>
            </label>
            <label>
                <span>Какая задача</span>
                <select name="service" required>
                    <option value="">Выберите задачу</option>
                    <option value="spuskaet_koleso">спускает колесо</option>
                    <option value="remont_prokola">ремонт прокола</option>
                    <option value="sezonnyy_shinomontazh">сезонный шиномонтаж</option>
                    <option value="zamena_masla_filtra">замена масла / фильтра</option>
                    <option value="zamena_kolodok">замена колодок</option>
                    <option value="melkiy_remont">мелкий ремонт</option>
                    <option value="drugoe">другое</option>
                </select>
            </label>
            <label>
                <span>Удобное время визита</span>
                <input type="text" name="preferred_time" maxlength="100" placeholder="Например: сегодня после 17:00">
            </label>
            <label class="wide">
                <span>Комментарий</span>
                <textarea name="message" maxlength="1000" rows="4" placeholder="Опишите проблему: прокол, биение, спускает колесо, масло, колодки"></textarea>
            </label>
        </div>

        <label class="check-line">
            <input type="checkbox" name="consent" value="1" required>
            <span>Согласен на обработку персональных данных для связи по заявке</span>
        </label>

        <button class="btn btn-primary form-submit" type="submit">
            <span class="btn-text">Отправить заявку</span>
            <span class="btn-loader" aria-hidden="true"></span>
        </button>
        <p class="form-note">Можно быстрее: позвоните напрямую <a href="tel:<?= SITE_PHONE_HREF; ?>"><?= e(SITE_PHONE); ?></a>.</p>
        <div class="form-status" role="status" aria-live="polite"></div>
    </form>
    <?php
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Шиномонтаж в Белых Столбах, Домодедово — ремонт проколов, сезонная замена шин</title>
    <meta name="description" content="Шиномонтаж на ул. Дзержинского, 2 в Белых Столбах: ремонт проколов, сезонная замена резины, замена масла, колодок и мелкий ремонт. Рейтинг 4,7 на Яндекс.Картах. Запись по телефону">
    <link rel="canonical" href="<?= e($canonical); ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Шиномонтаж в Белых Столбах">
    <meta property="og:description" content="Ремонт проколов, сезонная замена резины, масло, колодки и мелкий ремонт на ул. Дзержинского, 2.">
    <meta property="og:url" content="<?= e($canonical); ?>">
    <meta property="og:image" content="<?= e(rtrim(SITE_URL, '/') . '/assets/img/generated-hero-workshop.jpg'); ?>">
    <meta name="theme-color" content="#111418">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='8' fill='%23111418'/%3E%3Ccircle cx='32' cy='32' r='20' fill='none' stroke='%23f5b642' stroke-width='6'/%3E%3Ccircle cx='32' cy='32' r='7' fill='%23f5b642'/%3E%3C/svg%3E">
    <link rel="stylesheet" href="assets/css/style.css">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "AutoRepair",
        "name": "<?= e(SITE_NAME); ?>",
        "telephone": "<?= e(SITE_PHONE); ?>",
        "url": "<?= e($canonical); ?>",
        "sameAs": "<?= e(YANDEX_MAPS_URL); ?>",
        "areaServed": ["Белые Столбы", "Домодедово"],
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "ул. Дзержинского, 2",
            "addressLocality": "Домодедово",
            "addressRegion": "Московская область",
            "addressCountry": "RU"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.7",
            "ratingCount": "98",
            "reviewCount": "52"
        }
    }
    </script>
</head>
<body>
<header class="site-header" id="top">
    <div class="container header-inner">
        <a class="brand" href="#top" aria-label="На главную">
            <span class="brand-mark" aria-hidden="true"></span>
            <span>
                <strong><?= e(SITE_NAME); ?></strong>
                <small>ул. Дзержинского, 2, Домодедово</small>
            </span>
        </a>
        <nav class="main-nav" id="main-nav" aria-label="Основная навигация">
            <a href="#services">Услуги</a>
            <a href="#process">Как работаем</a>
            <a href="#reviews">Отзывы</a>
            <a href="#contacts">Контакты</a>
        </nav>
        <div class="header-actions">
            <a class="btn btn-ghost" href="tel:<?= SITE_PHONE_HREF; ?>">Позвонить</a>
            <a class="btn btn-primary" href="#lead">Записаться</a>
        </div>
        <button class="menu-toggle" type="button" aria-label="Открыть меню" aria-controls="main-nav" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<main>
    <section class="hero section-dark">
        <div class="container hero-grid">
            <div class="hero-copy reveal">
                <p class="eyebrow">Шиномонтаж Белые Столбы · ул. Дзержинского 2</p>
                <h1>Шиномонтаж и мелкий ремонт в Белых Столбах</h1>
                <p class="hero-lead">Поможем со спускающим колесом, сезонной заменой резины, маслом, фильтром, колодками и другими небольшими задачами по автомобилю. Лучше заранее позвонить и согласовать время визита.</p>
                <div class="trust-row" aria-label="Данные с Яндекс.Карт">
                    <span>Рейтинг 4,7</span>
                    <span>98 оценок</span>
                    <span>52 отзыва</span>
                    <span>Запись по телефону</span>
                </div>
                <div class="hero-actions">
                    <a class="btn btn-primary btn-large" href="tel:<?= SITE_PHONE_HREF; ?>">Позвонить мастеру</a>
                    <a class="btn btn-secondary btn-large" href="#lead">Оставить заявку</a>
                    <a class="route-link" href="<?= e(YANDEX_MAPS_URL); ?>" target="_blank" rel="noopener">Построить маршрут</a>
                </div>
                <p class="microcopy">Точный объём работ и стоимость лучше уточнить до визита.</p>
            </div>

            <aside class="hero-panel reveal" aria-label="Частые обращения сегодня">
                <h2>Частые обращения сегодня</h2>
                <ul class="issue-list">
                    <li>спускает колесо</li>
                    <li>нужен сезонный шиномонтаж</li>
                    <li>нужно заменить масло</li>
                    <li>нужно поменять колодки</li>
                    <li>нужен мелкий ремонт</li>
                </ul>
                <div class="quick-form">
                    <h3>Быстрая заявка</h3>
                    <?php lead_form('hero-form', $csrf, $currentUrl, 'compact-form'); ?>
                </div>
            </aside>
        </div>
    </section>

    <section class="section" id="problems">
        <div class="container">
            <div class="section-head reveal">
                <p class="eyebrow">Выберите ситуацию</p>
                <h2>Не обязательно знать название услуги</h2>
                <p>Опишите проблему простыми словами. Мастер подскажет, что можно сделать и когда лучше подъехать.</p>
            </div>
            <div class="card-grid problem-grid">
                <article class="info-card reveal"><h3>Спускает колесо</h3><p>Осмотрим, найдём причину, подскажем: ремонтировать, герметизировать или менять.</p><a href="tel:<?= SITE_PHONE_HREF; ?>">Уточнить по телефону</a></article>
                <article class="info-card reveal"><h3>Пора переобуваться</h3><p>Сезонная замена летней / зимней резины. Лучше записаться заранее, чтобы не ждать.</p><a href="#lead">Записаться</a></article>
                <article class="info-card reveal"><h3>Прокол или саморез</h3><p>По отзывам клиенты часто обращаются с проколами — мастер быстро находит причину.</p><a href="#lead">Описать прокол</a></article>
                <article class="info-card reveal"><h3>Масло и фильтр</h3><p>Можно уточнить возможность замены масла и масляного фильтра по телефону.</p><a href="tel:<?= SITE_PHONE_HREF; ?>">Уточнить</a></article>
                <article class="info-card reveal"><h3>Колодки</h3><p>В отзывах упоминают замену тормозных колодок. Запишитесь и уточните наличие времени.</p><a href="#lead">Оставить заявку</a></article>
                <article class="info-card reveal"><h3>Мелкий ремонт</h3><p>Небольшие работы по автомобилю — по согласованию с мастером.</p><a href="tel:<?= SITE_PHONE_HREF; ?>">Спросить мастера</a></article>
            </div>
        </div>
    </section>

    <section class="section section-muted" id="services">
        <div class="container">
            <div class="section-head reveal">
                <p class="eyebrow">Услуги</p>
                <h2>Шиномонтаж, колёса и небольшое обслуживание</h2>
                <p>Без выдуманного прайса и лишних обещаний: точную возможность, цену и время лучше согласовать до визита.</p>
            </div>
            <div class="service-columns">
                <article class="service-card reveal">
                    <img src="assets/img/generated-hero-workshop.jpg" alt="Сгенерированный тематический кадр шиномонтажной зоны" loading="lazy">
                    <span class="media-label">Сгенерированный кадр</span>
                    <h3>Шиномонтаж</h3>
                    <ul>
                        <li>сезонная замена шин;</li>
                        <li>снятие / установка колёс;</li>
                        <li>проверка давления;</li>
                        <li>балансировка — если требуется;</li>
                        <li>помощь при биении или дискомфорте после замены.</li>
                    </ul>
                </article>
                <article class="service-card reveal">
                    <img src="assets/img/generated-wheel-repair.jpg" alt="Сгенерированный тематический кадр ремонта прокола колеса" loading="lazy">
                    <span class="media-label">Сгенерированный кадр</span>
                    <h3>Ремонт колёс</h3>
                    <ul>
                        <li>поиск причины спуска;</li>
                        <li>ремонт прокола;</li>
                        <li>помощь при саморезе / мелком повреждении;</li>
                        <li>замена колеса.</li>
                    </ul>
                </article>
                <article class="service-card reveal">
                    <img src="assets/img/generated-service-tools.jpg" alt="Сгенерированный тематический кадр масла, фильтра, колодок и инструмента" loading="lazy">
                    <span class="media-label">Сгенерированный кадр</span>
                    <h3>Небольшое обслуживание</h3>
                    <ul>
                        <li>замена масла;</li>
                        <li>замена масляного фильтра;</li>
                        <li>замена тормозных колодок;</li>
                        <li>мелкий ремонт.</li>
                    </ul>
                </article>
            </div>
            <div class="notice reveal">
                <strong>Важно:</strong> точные возможности, цену и время выполнения лучше уточнить по телефону. Это небольшой сервис, поэтому запись помогает не ехать зря.
            </div>
        </div>
    </section>

    <section class="section" id="why">
        <div class="container">
            <div class="section-head reveal">
                <p class="eyebrow">Почему сюда обращаются</p>
                <h2>Доверие строится на понятных действиях</h2>
            </div>
            <div class="card-grid">
                <article class="info-card reveal"><h3>Можно договориться о времени</h3><p>В отзывах клиенты отмечают, что заранее созванивались и приезжали к согласованному времени.</p></article>
                <article class="info-card reveal"><h3>Небольшой частный формат</h3><p>Мини-сервис без лишней суеты: можно быстро объяснить задачу напрямую мастеру.</p></article>
                <article class="info-card reveal"><h3>Быстро помогают с колесом</h3><p>В отзывах часто пишут про быстрый ремонт проколов и помощь со спускающим колесом.</p></article>
                <article class="info-card reveal"><h3>Понятное согласование работ</h3><p>Перед началом ремонта уточните объём работ и стоимость, чтобы не было неприятных сюрпризов.</p></article>
                <article class="info-card reveal"><h3>Реальные отзывы на Яндекс.Картах</h3><p>4,7 рейтинга, 98 оценок и 52 отзыва — есть история реальных обращений.</p></article>
                <article class="info-card reveal"><h3>Удобно для Белых Столбов</h3><p>Адрес: <?= e(SITE_ADDRESS); ?>.</p></article>
            </div>
        </div>
    </section>

    <section class="section section-dark" id="process">
        <div class="container">
            <div class="section-head reveal">
                <p class="eyebrow">Как проходит обращение</p>
                <h2>Сначала согласование, потом работа</h2>
            </div>
            <div class="steps">
                <article class="step reveal"><span>01</span><h3>Вы звоните или оставляете заявку</h3><p>Кратко опишите задачу: прокол, переобувка, масло, колодки или другое.</p></article>
                <article class="step reveal"><span>02</span><h3>Мастер подтверждает время</h3><p>Так вы не едете зря и понимаете, когда можно подъехать.</p></article>
                <article class="step reveal"><span>03</span><h3>Осмотр и согласование</h3><p>Перед работой уточняются причина, объём и ориентир по стоимости.</p></article>
                <article class="step reveal"><span>04</span><h3>Работа и проверка</h3><p>После шиномонтажа можно попросить проверить давление и уточнить рекомендации.</p></article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container calm-block reveal">
            <div>
                <p class="eyebrow">Чтобы визит прошёл спокойно</p>
                <h2>Позвоните перед выездом и договоритесь о деталях</h2>
                <p>Это небольшой сервис, поэтому лучше заранее позвонить, согласовать время и описать задачу. Перед началом работ уточните стоимость, а после шиномонтажа попросите проверить давление — так проще избежать недопонимания.</p>
            </div>
            <ul class="check-list">
                <li>Позвоните перед выездом</li>
                <li>Согласуйте цену до начала работ</li>
                <li>Опишите проблему заранее: прокол, биение, спускает колесо, замена масла или колодок</li>
            </ul>
        </div>
    </section>

    <section class="section section-muted" id="reviews">
        <div class="container">
            <div class="rating-strip reveal">
                <div><strong>4,7 из 5</strong><span>на Яндекс.Картах</span></div>
                <div><strong>98 оценок</strong><span>52 отзыва</span></div>
                <a class="btn btn-secondary" href="<?= e(YANDEX_MAPS_URL); ?>" target="_blank" rel="noopener">Смотреть карточку на Яндекс.Картах</a>
            </div>
            <div class="review-grid">
                <?php foreach ($reviews as $review): ?>
                    <article class="review-card reveal">
                        <div class="review-top">
                            <strong><?= e($review['name']); ?></strong>
                            <span><?= e($review['date']); ?></span>
                        </div>
                        <p>«<?= e($review['text']); ?>»</p>
                        <small>Источник: Яндекс.Карты</small>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section" id="photos">
        <div class="container">
            <div class="section-head reveal">
                <p class="eyebrow">Работы и задачи</p>
                <h2>Колёса, проколы и небольшое обслуживание</h2>
                <p>В одном месте можно решить типовые задачи водителя: переобуть резину, найти причину спуска, отремонтировать прокол, уточнить замену масла, фильтра или колодок.</p>
            </div>
            <div class="photo-grid">
                <figure class="photo-placeholder photo-realized reveal">
                    <img src="assets/img/generated-hero-workshop.jpg" alt="Сгенерированный тематический кадр рабочей зоны шиномонтажа" loading="lazy">
                    <figcaption>Сезонный шиномонтаж <span>летняя и зимняя резина</span></figcaption>
                </figure>
                <figure class="photo-placeholder photo-realized reveal">
                    <img src="assets/img/generated-wheel-repair.jpg" alt="Сгенерированный тематический кадр ремонта прокола" loading="lazy">
                    <figcaption>Ремонт прокола <span>если повреждение позволяет ремонт</span></figcaption>
                </figure>
                <figure class="photo-placeholder photo-realized reveal">
                    <img src="assets/img/generated-service-tools.jpg" alt="Сгенерированный тематический кадр небольшого обслуживания" loading="lazy">
                    <figcaption>Масло, фильтр, колодки <span>по предварительному согласованию</span></figcaption>
                </figure>
                <figure class="photo-placeholder photo-realized reveal">
                    <img src="assets/img/generated-work-bay.jpg" alt="Сгенерированный тематический кадр рабочей зоны шиномонтажа" loading="lazy">
                    <figcaption>Осмотр колеса <span>причина спуска и рекомендации</span></figcaption>
                </figure>
                <figure class="photo-placeholder photo-realized reveal">
                    <img src="assets/img/generated-wheels-stack.jpg" alt="Сгенерированный тематический кадр колёс и резины" loading="lazy">
                    <figcaption>Колёса и резина <span>проверка давления и состояния</span></figcaption>
                </figure>
                <figure class="photo-placeholder photo-realized reveal">
                    <img src="assets/img/generated-garage-entry.jpg" alt="Сгенерированный тематический кадр въезда в небольшую мастерскую" loading="lazy">
                    <figcaption>Визит по записи <span>перед выездом лучше позвонить</span></figcaption>
                </figure>
            </div>
        </div>
    </section>

    <section class="section section-dark" id="lead">
        <div class="container lead-section">
            <div class="lead-copy reveal">
                <p class="eyebrow">Запись</p>
                <h2>Записаться или уточнить стоимость</h2>
                <p>Оставьте телефон и задачу — мастер свяжется, подскажет по времени и сориентирует по работам.</p>
                <div class="price-hint">Точный расчёт — после описания задачи.</div>
                <p class="microcopy">Если вопрос срочный, звонок быстрее заявки.</p>
            </div>
            <div class="form-shell reveal">
                <?php lead_form('main-form', $csrf, $currentUrl); ?>
            </div>
        </div>
    </section>

    <section class="section" id="faq">
        <div class="container">
            <div class="section-head reveal">
                <p class="eyebrow">FAQ</p>
                <h2>Частые вопросы</h2>
            </div>
            <div class="faq-list">
                <details class="faq-item reveal"><summary>Можно приехать без записи?</summary><p>Лучше заранее позвонить. Это небольшой сервис, поэтому запись помогает не ждать и не приехать в закрытое время.</p></details>
                <details class="faq-item reveal"><summary>Есть точный прайс?</summary><p>Точную стоимость лучше уточнить по телефону: цена зависит от задачи, размера колёс, состояния резины, дисков и объёма работ.</p></details>
                <details class="faq-item reveal"><summary>Ремонтируете проколы?</summary><p>Да, по отзывам клиенты часто обращаются с проколами и спускающими колёсами. Возможность ремонта зависит от повреждения.</p></details>
                <details class="faq-item reveal"><summary>Можно заменить масло и фильтр?</summary><p>В отзывах есть такие обращения. Лучше заранее уточнить по телефону и согласовать время.</p></details>
                <details class="faq-item reveal"><summary>Можно заменить тормозные колодки?</summary><p>Да, такая услуга упоминается в отзывах. Запишитесь заранее и уточните детали.</p></details>
                <details class="faq-item reveal"><summary>Что делать, если колесо бьёт после замены?</summary><p>Причина может быть в балансировке, диске, резине или грыже. Лучше сразу сообщить мастеру и попросить проверить колесо.</p></details>
                <details class="faq-item reveal"><summary>Где находится сервис?</summary><p><?= e(SITE_ADDRESS); ?>.</p></details>
            </div>
        </div>
    </section>

    <section class="section section-muted" id="contacts">
        <div class="container contacts-grid">
            <div class="contact-card reveal">
                <p class="eyebrow">Контакты</p>
                <h2><?= e(SITE_NAME); ?></h2>
                <p><strong>Телефон:</strong> <a href="tel:<?= SITE_PHONE_HREF; ?>"><?= e(SITE_PHONE); ?></a></p>
                <p><strong>Адрес:</strong> <?= e(SITE_ADDRESS); ?></p>
                <p>Перед выездом лучше позвонить и подтвердить время визита.</p>
                <div class="button-row">
                    <a class="btn btn-primary" href="tel:<?= SITE_PHONE_HREF; ?>">Позвонить</a>
                    <a class="btn btn-secondary" href="<?= e(YANDEX_MAPS_URL); ?>" target="_blank" rel="noopener">Построить маршрут</a>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <strong><?= e(SITE_NAME); ?></strong>
            <p><?= e(SITE_ADDRESS); ?></p>
        </div>
        <div>
            <a href="tel:<?= SITE_PHONE_HREF; ?>"><?= e(SITE_PHONE); ?></a>
            <a href="<?= e(YANDEX_MAPS_URL); ?>" target="_blank" rel="noopener">Карточка на Яндекс.Картах</a>
            <button class="footer-link" type="button" data-open-policy>Политика обработки персональных данных</button>
        </div>
        <p class="footer-note">Информация на сайте основана на открытых данных и отзывах. Точные цены, время работы и доступность услуг уточняйте по телефону.</p>
    </div>
</footer>

<div class="mobile-cta" aria-label="Быстрые действия">
    <a href="tel:<?= SITE_PHONE_HREF; ?>">Позвонить</a>
    <a href="#lead">Записаться</a>
    <a href="<?= e(YANDEX_MAPS_URL); ?>" target="_blank" rel="noopener">Маршрут</a>
</div>

<dialog class="policy-dialog" id="policy-dialog">
    <div class="dialog-head">
        <h2>Политика обработки персональных данных</h2>
        <button type="button" aria-label="Закрыть" data-close-policy>×</button>
    </div>
    <p>Данные из формы используются только для связи по заявке: имя, телефон, выбранная задача, удобное время и комментарий. Данные не публикуются на сайте и могут храниться в резервном файле заявок на сервере.</p>
    <p>Отправляя форму, вы соглашаетесь на обработку персональных данных для ответа на обращение.</p>
    <a href="/privacy.php">Открыть полную страницу политики</a>
</dialog>

<script src="assets/js/app.js" defer></script>
</body>
</html>
