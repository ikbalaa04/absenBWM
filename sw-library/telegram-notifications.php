<?php
if (!function_exists('telegram_ensure_schema')) {
  function telegram_ensure_schema($connection) {
    static $done = false;
    if ($done || empty($connection)) {
      return;
    }

    $site_columns = array();
    $result = $connection->query("SHOW COLUMNS FROM sw_site");
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $site_columns[$row['Field']] = true;
      }
    }
    if (empty($site_columns['telegram_bot_token'])) {
      $connection->query("ALTER TABLE sw_site ADD telegram_bot_token varchar(150) NOT NULL DEFAULT '' AFTER attendance_checkin_grace_minutes");
    }
    if (empty($site_columns['telegram_admin_chat_ids'])) {
      $connection->query("ALTER TABLE sw_site ADD telegram_admin_chat_ids text NULL AFTER telegram_bot_token");
    }
    if (empty($site_columns['telegram_reminder_minutes'])) {
      $connection->query("ALTER TABLE sw_site ADD telegram_reminder_minutes tinyint(2) NOT NULL DEFAULT 10 AFTER telegram_admin_chat_ids");
    }
    if (empty($site_columns['telegram_cron_token'])) {
      $connection->query("ALTER TABLE sw_site ADD telegram_cron_token varchar(64) NOT NULL DEFAULT '' AFTER telegram_reminder_minutes");
    }

    $employee_columns = array();
    $result = $connection->query("SHOW COLUMNS FROM employees");
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $employee_columns[$row['Field']] = true;
      }
    }
    if (empty($employee_columns['telegram_chat_id'])) {
      $connection->query("ALTER TABLE employees ADD telegram_chat_id varchar(100) NOT NULL DEFAULT '' AFTER employees_email");
    }

    $connection->query("CREATE TABLE IF NOT EXISTS telegram_notification_log (
      log_id int(11) NOT NULL AUTO_INCREMENT,
      notification_key varchar(190) NOT NULL,
      target_type enum('employee','admin') NOT NULL,
      target_id varchar(100) NOT NULL DEFAULT '',
      chat_id varchar(100) NOT NULL DEFAULT '',
      message text NOT NULL,
      sent_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (log_id),
      UNIQUE KEY notification_key (notification_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $done = true;
  }
}

if (!function_exists('telegram_setting_row')) {
  function telegram_setting_row($connection) {
    telegram_ensure_schema($connection);
    $result = $connection->query("SELECT telegram_bot_token,telegram_admin_chat_ids,telegram_reminder_minutes,telegram_cron_token,site_name FROM sw_site LIMIT 1");
    if ($result && $result->num_rows > 0) {
      return $result->fetch_assoc();
    }
    return array(
      'telegram_bot_token' => '',
      'telegram_admin_chat_ids' => '',
      'telegram_reminder_minutes' => 10,
      'telegram_cron_token' => '',
      'site_name' => 'Absensi'
    );
  }
}

if (!function_exists('telegram_escape')) {
  function telegram_escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8', false);
  }
}

if (!function_exists('telegram_admin_chat_ids')) {
  function telegram_admin_chat_ids($connection) {
    $setting = telegram_setting_row($connection);
    $raw = (string)$setting['telegram_admin_chat_ids'];
    $items = preg_split('/[\s,;]+/', $raw);
    $chat_ids = array();
    foreach ($items as $item) {
      $item = trim($item);
      if ($item !== '') {
        $chat_ids[] = $item;
      }
    }
    return array_values(array_unique($chat_ids));
  }
}

if (!function_exists('telegram_send_message')) {
  function telegram_send_message($connection, $chat_id, $text, $options = array()) {
    telegram_ensure_schema($connection);
    $setting = telegram_setting_row($connection);
    $token = trim((string)$setting['telegram_bot_token']);
    $chat_id = trim((string)$chat_id);
    $text = trim((string)$text);
    if ($token === '' || $chat_id === '' || $text === '') {
      return false;
    }

    $payload = array_merge(array(
      'chat_id' => $chat_id,
      'text' => $text,
      'parse_mode' => 'HTML',
      'disable_web_page_preview' => true
    ), $options);
    $url = 'https://api.telegram.org/bot'.$token.'/sendMessage';

    if (function_exists('curl_init')) {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
      $response = curl_exec($ch);
      $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      return $response !== false && $http_code >= 200 && $http_code < 300;
    }

    $context = stream_context_create(array(
      'http' => array(
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => json_encode($payload),
        'timeout' => 10
      )
    ));
    $response = @file_get_contents($url, false, $context);
    return $response !== false;
  }
}

if (!function_exists('telegram_log_once')) {
  function telegram_log_once($connection, $notification_key, $target_type, $target_id, $chat_id, $message) {
    telegram_ensure_schema($connection);
    $notification_key = mysqli_real_escape_string($connection, substr((string)$notification_key, 0, 190));
    $target_type = $target_type === 'admin' ? 'admin' : 'employee';
    $target_id = mysqli_real_escape_string($connection, substr((string)$target_id, 0, 100));
    $chat_id = mysqli_real_escape_string($connection, substr((string)$chat_id, 0, 100));
    $message = mysqli_real_escape_string($connection, (string)$message);
    $insert = "INSERT IGNORE INTO telegram_notification_log (notification_key,target_type,target_id,chat_id,message,sent_at) VALUES('$notification_key','$target_type','$target_id','$chat_id','$message',NOW())";
    if ($connection->query($insert) === false) {
      return false;
    }
    return $connection->affected_rows > 0;
  }
}

if (!function_exists('telegram_send_once')) {
  function telegram_send_once($connection, $notification_key, $target_type, $target_id, $chat_id, $message) {
    if (!telegram_log_once($connection, $notification_key, $target_type, $target_id, $chat_id, $message)) {
      return false;
    }
    return telegram_send_message($connection, $chat_id, $message);
  }
}

if (!function_exists('telegram_send_admin')) {
  function telegram_send_admin($connection, $message, $key_prefix = '') {
    $sent = 0;
    foreach (telegram_admin_chat_ids($connection) as $chat_id) {
      $key = $key_prefix !== '' ? $key_prefix.'-admin-'.md5($chat_id) : '';
      if ($key !== '') {
        if (telegram_send_once($connection, $key, 'admin', 'admin', $chat_id, $message)) {
          $sent++;
        }
      } elseif (telegram_send_message($connection, $chat_id, $message)) {
        $sent++;
      }
    }
    return $sent;
  }
}

if (!function_exists('telegram_send_employee')) {
  function telegram_send_employee($connection, $employees_id, $message, $key_prefix = '') {
    telegram_ensure_schema($connection);
    $employees_id = mysqli_real_escape_string($connection, $employees_id);
    $result = $connection->query("SELECT telegram_chat_id FROM employees WHERE id='$employees_id' LIMIT 1");
    if (!$result || $result->num_rows == 0) {
      return false;
    }
    $row = $result->fetch_assoc();
    $chat_id = trim((string)$row['telegram_chat_id']);
    if ($chat_id === '') {
      return false;
    }
    if ($key_prefix !== '') {
      return telegram_send_once($connection, $key_prefix.'-employee-'.$employees_id, 'employee', $employees_id, $chat_id, $message);
    }
    return telegram_send_message($connection, $chat_id, $message);
  }
}

if (!function_exists('telegram_status_label')) {
  function telegram_status_label($status) {
    if ((string)$status === '1' || $status === 'active') {
      return 'disetujui';
    }
    if ((string)$status === '2' || $status === 'cancelled') {
      return 'ditolak/dibatalkan';
    }
    if ((string)$status === 'completed') {
      return 'selesai';
    }
    return 'diperbarui';
  }
}
