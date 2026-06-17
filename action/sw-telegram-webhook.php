<?php
require_once'../sw-library/sw-config.php';

telegram_ensure_schema($connection);
$setting = telegram_setting_row($connection);
$configured_secret = trim((string)$setting['telegram_webhook_secret']);
$request_secret = isset($_GET['secret']) ? trim((string)$_GET['secret']) : '';

header('Content-Type: application/json; charset=utf-8');
if ($configured_secret !== '' && !hash_equals($configured_secret, $request_secret)) {
  http_response_code(403);
  echo json_encode(array('ok' => false));
  exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
  echo json_encode(array('ok' => true));
  exit;
}

$message = array();
if (!empty($payload['message']) && is_array($payload['message'])) {
  $message = $payload['message'];
} elseif (!empty($payload['edited_message']) && is_array($payload['edited_message'])) {
  $message = $payload['edited_message'];
}

$text = isset($message['text']) ? trim((string)$message['text']) : '';
$chat_id = isset($message['chat']['id']) ? (string)$message['chat']['id'] : '';
if ($text === '' || $chat_id === '') {
  echo json_encode(array('ok' => true));
  exit;
}

if (!preg_match('/^\/start(?:@\S+)?\s+([A-Za-z0-9_-]+)$/', $text, $matches)) {
  echo json_encode(array('ok' => true));
  exit;
}

$token = mysqli_real_escape_string($connection, $matches[1]);
$result = $connection->query("SELECT id,employees_name FROM employees WHERE telegram_connection_token='$token' AND (telegram_connection_token_expires_at IS NULL OR telegram_connection_token_expires_at >= NOW()) LIMIT 1");
if (!$result || $result->num_rows == 0) {
  telegram_send_message($connection, $chat_id, 'Kode koneksi Telegram tidak valid atau sudah kedaluwarsa.');
  echo json_encode(array('ok' => true));
  exit;
}

$employee = $result->fetch_assoc();
$username = '';
if (!empty($message['from']['username'])) {
  $username = $message['from']['username'];
} elseif (!empty($message['chat']['username'])) {
  $username = $message['chat']['username'];
}

$employee_id = mysqli_real_escape_string($connection, $employee['id']);
$safe_chat_id = mysqli_real_escape_string($connection, $chat_id);
$safe_username = mysqli_real_escape_string($connection, $username);
$connection->query("UPDATE employees SET telegram_chat_id='$safe_chat_id', telegram_username='$safe_username', telegram_connected_at=NOW(), telegram_connection_token=NULL, telegram_connection_token_expires_at=NULL WHERE id='$employee_id'");

telegram_send_message($connection, $chat_id, 'Telegram berhasil terhubung ke akun '.telegram_escape($employee['employees_name']).'.');
echo json_encode(array('ok' => true));
