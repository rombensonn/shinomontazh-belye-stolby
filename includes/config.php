<?php
declare(strict_types=1);

const SITE_NAME = 'Шиномонтаж в Белых Столбах';
const SITE_PHONE = '+7 (977) 340-55-65';
const SITE_PHONE_HREF = '+79773405565';
const SITE_ADDRESS = 'ул. Дзержинского, 2, микрорайон Белые Столбы, Домодедово';
const YANDEX_MAPS_URL = 'https://yandex.ru/maps/-/CPHczN2D';
const SITE_URL = 'https://rombensonn.github.io/shinomontazh-belye-stolby';

const ADMIN_EMAIL = 'admin@example.com';
const FROM_EMAIL = 'no-reply@example.com';

const TELEGRAM_BOT_TOKEN = '';
const TELEGRAM_CHAT_ID = '';
const ENABLE_TELEGRAM = false;
const ENABLE_EMAIL = true;

const RATE_LIMIT_SECONDS = 60;
const STORAGE_DIR = __DIR__ . '/../storage';
const LEADS_FILE = STORAGE_DIR . '/leads.jsonl';
const RATE_LIMIT_FILE = STORAGE_DIR . '/rate-limit.json';

const ALLOWED_SERVICES = [
    'spuskaet_koleso' => 'спускает колесо',
    'remont_prokola' => 'ремонт прокола',
    'sezonnyy_shinomontazh' => 'сезонный шиномонтаж',
    'zamena_masla_filtra' => 'замена масла / фильтра',
    'zamena_kolodok' => 'замена колодок',
    'melkiy_remont' => 'мелкий ремонт',
    'drugoe' => 'другое',
];
