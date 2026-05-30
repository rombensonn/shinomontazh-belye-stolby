<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function clean_text(?string $value, int $maxLength): string
{
    $value = trim((string) $value);
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
    $value = preg_replace('/\s+/u', ' ', $value) ?? '';

    if (mb_strlen($value, 'UTF-8') > $maxLength) {
        $value = mb_substr($value, 0, $maxLength, 'UTF-8');
    }

    return $value;
}

function is_ajax_request(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

    return str_contains($accept, 'application/json')
        || strtolower($requestedWith) === 'xmlhttprequest';
}

function json_response(bool $success, string $message, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function html_message(string $title, string $message, int $status = 200): never
{
    http_response_code($status);
    $safeTitle = e($title);
    $safeMessage = e($message);
    echo "<!doctype html><html lang=\"ru\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"><title>{$safeTitle}</title><link rel=\"stylesheet\" href=\"/assets/css/style.css\"></head><body><main class=\"system-page\"><section class=\"system-card\"><h1>{$safeTitle}</h1><p>{$safeMessage}</p><div class=\"button-row\"><a class=\"btn btn-primary\" href=\"/\">Вернуться на сайт</a><a class=\"btn btn-secondary\" href=\"tel:+79773405565\">Позвонить</a></div></section></main></body></html>";
    exit;
}

function request_ip(): string
{
    $headers = [
        $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '',
        $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ];

    $ip = trim(explode(',', $headers[0] ?: $headers[1] ?: $headers[2])[0]);
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

function rate_limit_check(string $ip, int $seconds): bool
{
    if (!is_dir(STORAGE_DIR)) {
        mkdir(STORAGE_DIR, 0755, true);
    }

    $now = time();
    $data = [];

    if (is_file(RATE_LIMIT_FILE)) {
        $raw = file_get_contents(RATE_LIMIT_FILE);
        $decoded = json_decode((string) $raw, true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }

    foreach ($data as $storedIp => $timestamp) {
        if (!is_int($timestamp) || $timestamp < ($now - 86400)) {
            unset($data[$storedIp]);
        }
    }

    if (isset($data[$ip]) && is_int($data[$ip]) && ($now - $data[$ip]) < $seconds) {
        file_put_contents(RATE_LIMIT_FILE, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX);
        return false;
    }

    $data[$ip] = $now;
    file_put_contents(RATE_LIMIT_FILE, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX);
    return true;
}

function save_lead(array $lead): bool
{
    if (!is_dir(STORAGE_DIR)) {
        mkdir(STORAGE_DIR, 0755, true);
    }

    $line = json_encode($lead, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    return file_put_contents(LEADS_FILE, $line, FILE_APPEND | LOCK_EX) !== false;
}

function send_email_lead(array $lead): bool
{
    if (!ENABLE_EMAIL || ADMIN_EMAIL === 'admin@example.com') {
        return false;
    }

    $subject = 'Новая заявка с сайта: ' . SITE_NAME;
    $body = build_lead_message($lead);
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . SITE_NAME . ' <' . FROM_EMAIL . '>',
        'Reply-To: ' . FROM_EMAIL,
    ];

    return @mail(ADMIN_EMAIL, $subject, $body, implode("\r\n", $headers));
}

function send_telegram_lead(array $lead): bool
{
    if (!ENABLE_TELEGRAM || TELEGRAM_BOT_TOKEN === '' || TELEGRAM_CHAT_ID === '') {
        return false;
    }

    $url = 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage';
    $payload = http_build_query([
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => build_lead_message($lead),
        'disable_web_page_preview' => 'true',
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 5,
        ],
    ]);

    $result = @file_get_contents($url, false, $context);
    return $result !== false;
}

function build_lead_message(array $lead): string
{
    return "Новая заявка с сайта:\n"
        . 'Сервис: ' . SITE_NAME . "\n"
        . 'Имя: ' . ($lead['name'] ?? '') . "\n"
        . 'Телефон: ' . ($lead['phone'] ?? '') . "\n"
        . 'Задача: ' . ($lead['service_label'] ?? '') . "\n"
        . 'Удобное время: ' . ($lead['preferred_time'] ?? '') . "\n"
        . 'Комментарий: ' . ($lead['message'] ?? '') . "\n"
        . 'Страница: ' . ($lead['page_url'] ?? '') . "\n"
        . 'Дата: ' . ($lead['created_at'] ?? '') . "\n"
        . 'IP: ' . ($lead['ip'] ?? '');
}
