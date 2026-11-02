<?php
// 문의 폼(contact-form.js)을 받아 회사 메일로 보낸다. 서버의 옛 contact.php 와는 별개다.
header('Content-Type: application/json; charset=UTF-8');

$to = 'info@fukuoka-estate.co.jp';

function respond($code, $ok, $error = '')
{
    http_response_code($code);
    echo json_encode(array('ok' => $ok, 'error' => $error));
    exit;
}

function field($key, $maxLength)
{
    $value = isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
    if (!mb_check_encoding($value, 'UTF-8')) {
        return '';
    }
    return mb_substr($value, 0, $maxLength, 'UTF-8');
}

function singleLine($value)
{
    return trim(str_replace(array("\r", "\n"), ' ', $value));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, false, 'method');
}

$origin = isset($_SERVER['HTTP_ORIGIN']) ? parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST) : null;
if ($origin && $origin !== $_SERVER['HTTP_HOST']) {
    respond(403, false, 'origin');
}

// 사람에게는 보이지 않는 칸. 값이 있으면 자동 입력 봇이므로 보낸 척만 한다.
if (field('website', 200) !== '') {
    respond(200, true);
}

$types = array('不動産売却相談（売りたい）', '不動産購入相談（買いたい）', '賃貸（借りたい）', 'その他');
$type = field('inquiry_type', 40);
$lastName = singleLine(field('last_name', 60));
$firstName = singleLine(field('first_name', 60));
$email = singleLine(field('email', 200));
$tel = singleLine(field('tel', 40));
$address = singleLine(field('address', 200));
$message = field('message', 5000);

if (!in_array($type, $types, true) || $lastName === '' || $firstName === '' || $tel === '' || $address === ''
    || field('privacy', 10) === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(422, false, 'invalid');
}

$body = "ホームページのお問い合わせフォームから送信されました。\n\n"
    . "お問い合わせ項目: {$type}\n"
    . "お名前: {$lastName} {$firstName}\n"
    . "メールアドレス: {$email}\n"
    . "電話番号: {$tel}\n"
    . "ご住所: {$address}\n\n"
    . "お問い合わせ内容:\n{$message}\n";

mb_language('uni');
mb_internal_encoding('UTF-8');

$sent = mb_send_mail(
    $to,
    '【ホームページ】お問い合わせ: ' . $type,
    $body,
    "From: {$to}\r\nReply-To: {$email}",
    "-f{$to}"
);

if (!$sent) {
    respond(500, false, 'mail');
}
respond(200, true);
