<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (is_ajax_request()) {
        json_response(false, 'Метод не поддерживается.', 405);
    }
    html_message('Метод не поддерживается', 'Форма принимает только POST-запросы.', 405);
}

$ajax = is_ajax_request();

$name = clean_text($_POST['name'] ?? '', 60);
$phone = clean_text($_POST['phone'] ?? '', 30);
$service = clean_text($_POST['service'] ?? '', 80);
$preferredTime = clean_text($_POST['preferred_time'] ?? '', 100);
$message = clean_text($_POST['message'] ?? '', 1000);
$consent = isset($_POST['consent']) ? '1' : '';
$honeypot = trim((string) ($_POST['honeypot'] ?? ''));
$csrf = (string) ($_POST['csrf_token'] ?? '');
$pageUrl = clean_text($_POST['page_url'] ?? '', 300);
$userAgent = clean_text($_SERVER['HTTP_USER_AGENT'] ?? ($_POST['user_agent'] ?? ''), 300);
$ip = request_ip();

$errors = [];

if ($honeypot !== '') {
    $errors[] = 'Заявка похожа на спам.';
}

if ($csrf === '' || empty($_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], $csrf)) {
    $errors[] = 'Обновите страницу и отправьте заявку ещё раз.';
}

if (mb_strlen($name, 'UTF-8') < 2 || mb_strlen($name, 'UTF-8') > 60) {
    $errors[] = 'Укажите имя от 2 до 60 символов.';
}

$digits = preg_replace('/\D+/', '', $phone) ?? '';
if ($phone === '' || !preg_match('/^[0-9+\s()\-]{7,30}$/u', $phone) || !preg_match('/^(7|8)?9\d{9}$/', $digits)) {
    $errors[] = 'Укажите телефон в российском формате.';
}

if (!array_key_exists($service, ALLOWED_SERVICES)) {
    $errors[] = 'Выберите задачу из списка.';
}

if (mb_strlen($preferredTime, 'UTF-8') > 100) {
    $errors[] = 'Поле удобного времени слишком длинное.';
}

if (mb_strlen($message, 'UTF-8') > 1000) {
    $errors[] = 'Комментарий слишком длинный.';
}

if ($consent !== '1') {
    $errors[] = 'Нужно согласие на обработку персональных данных.';
}

if ($errors !== []) {
    $text = implode(' ', $errors);
    if ($ajax) {
        json_response(false, $text, 422);
    }
    html_message('Заявка не отправлена', $text, 422);
}

if (!rate_limit_check($ip, RATE_LIMIT_SECONDS)) {
    $text = 'Заявка уже отправлялась недавно. Если вопрос срочный, лучше позвонить мастеру.';
    if ($ajax) {
        json_response(false, $text, 429);
    }
    html_message('Заявка не отправлена', $text, 429);
}

$lead = [
    'created_at' => date('c'),
    'site' => SITE_NAME,
    'name' => $name,
    'phone' => $phone,
    'service' => $service,
    'service_label' => ALLOWED_SERVICES[$service],
    'preferred_time' => $preferredTime,
    'message' => $message,
    'page_url' => $pageUrl,
    'user_agent' => $userAgent,
    'ip' => $ip,
];

$saved = save_lead($lead);
$emailSent = send_email_lead($lead);
$telegramSent = send_telegram_lead($lead);

if (!$saved && !$emailSent && !$telegramSent) {
    $text = 'Не получилось сохранить заявку. Пожалуйста, позвоните мастеру напрямую.';
    if ($ajax) {
        json_response(false, $text, 500);
    }
    html_message('Заявка не отправлена', $text, 500);
}

unset($_SESSION['csrf_token']);

$successMessage = 'Заявка отправлена. Если вопрос срочный — лучше сразу позвонить мастеру.';

if ($ajax) {
    json_response(true, $successMessage);
}

header('Location: /thanks.php', true, 303);
exit;
