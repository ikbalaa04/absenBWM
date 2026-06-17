<?php
session_start();
if(empty($_SESSION['SESSION_USER']) || empty($_SESSION['SESSION_ID'])){
    header('location:../../login/');
 exit;}
else {
require_once'../../../sw-library/sw-config.php';
require_once'../../login/login_session.php';
include('../../../sw-library/sw-function.php');

function assignment_number($connection, $date) {
  $year = date('Y', strtotime($date));
  $month = date('m', strtotime($date));
  $prefix = 'ST/'.$year.'/'.$month.'/';
  $prefix_sql = mysqli_real_escape_string($connection, $prefix);
  $result = $connection->query("SELECT assignment_number FROM assignments WHERE assignment_number LIKE '$prefix_sql%' ORDER BY assignment_id DESC LIMIT 1");
  $next = 1;
  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last = (int)substr($row['assignment_number'], -4);
    $next = $last + 1;
  }
  return $prefix.sprintf('%04d', $next);
}

function validate_assignment_signer($connection, $assignment_signer_id) {
  $assignment_signer_id = mysqli_real_escape_string($connection, $assignment_signer_id);
  $query = "SELECT employees.id FROM employees INNER JOIN position ON position.position_id=employees.position_id WHERE employees.id='$assignment_signer_id' AND position.position_name LIKE '%Manajemen%' LIMIT 1";
  $result = $connection->query($query);
  return $result && $result->num_rows > 0;
}

switch (@$_GET['action']){
case 'add':
  $error = array();

  if (empty($_POST['employees_id'])) {
      $error[] = 'Staff wajib dipilih';
    } else {
      $employees_id = mysqli_real_escape_string($connection, $_POST['employees_id']);
  }

  if (empty($_POST['assignment_signer_id'])) {
      $error[] = 'Pemberi tugas wajib dipilih';
    } else {
      $assignment_signer_id = mysqli_real_escape_string($connection, $_POST['assignment_signer_id']);
      if (!validate_assignment_signer($connection, $assignment_signer_id)) {
        $error[] = 'Pemberi tugas harus user dengan jabatan Manajemen';
      }
  }

  if (empty($_POST['assignment_start'])) {
      $error[] = 'Tanggal mulai wajib diisi';
    } else {
      $assignment_start = date('Y-m-d', strtotime($_POST['assignment_start']));
  }

  if (empty($_POST['assignment_end'])) {
      $error[] = 'Tanggal selesai wajib diisi';
    } else {
      $assignment_end = date('Y-m-d', strtotime($_POST['assignment_end']));
  }

  if (!empty($assignment_start) && !empty($assignment_end) && strtotime($assignment_start) > strtotime($assignment_end)) {
      $error[] = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai';
  }

  if (empty($_POST['assignment_location'])) {
      $error[] = 'Lokasi/tujuan tugas wajib diisi';
    } else {
      $assignment_location = mysqli_real_escape_string($connection, strip_tags($_POST['assignment_location']));
  }

  if (empty($_POST['assignment_description'])) {
      $error[] = 'Keterangan tugas wajib diisi';
    } else {
      $assignment_description = mysqli_real_escape_string($connection, strip_tags($_POST['assignment_description']));
  }

  if (empty($error)) {
    $check = $connection->query("SELECT assignment_id FROM assignments WHERE employees_id='$employees_id' AND assignment_status='active' AND assignment_start <= '$assignment_end' AND assignment_end >= '$assignment_start' LIMIT 1");
    if ($check && $check->num_rows > 0) {
      echo'Staff sudah memiliki penugasan aktif pada rentang tanggal tersebut.';
      break;
    }

    $assignment_number = mysqli_real_escape_string($connection, assignment_number($connection, $assignment_start));
    $add ="INSERT INTO assignments (employees_id,assignment_start,assignment_end,assignment_location,assignment_description,assignment_number,assignment_signer_id,assignment_status,assignment_source,approved_at,approved_by,created_at,updated_at)
          VALUES('$employees_id','$assignment_start','$assignment_end','$assignment_location','$assignment_description','$assignment_number','$assignment_signer_id','active','admin','$timeNow','$user_id','$timeNow','$timeNow')";
    if($connection->query($add) === false) {
        echo'Data tidak berhasil disimpan: '.$connection->error;
    } else{
        echo'success';
    }
  } else {
    echo implode('<br>', $error);
  }
break;

case 'update':
  $error = array();

  if (empty($_POST['assignment_id'])) {
      $error[] = 'ID penugasan wajib diisi';
    } else {
      $assignment_id = mysqli_real_escape_string($connection, $_POST['assignment_id']);
  }

  if (empty($_POST['employees_id'])) {
      $error[] = 'Staff wajib dipilih';
    } else {
      $employees_id = mysqli_real_escape_string($connection, $_POST['employees_id']);
  }

  if (empty($_POST['assignment_signer_id'])) {
      $error[] = 'Pemberi tugas wajib dipilih';
    } else {
      $assignment_signer_id = mysqli_real_escape_string($connection, $_POST['assignment_signer_id']);
      if (!validate_assignment_signer($connection, $assignment_signer_id)) {
        $error[] = 'Pemberi tugas harus user dengan jabatan Manajemen';
      }
  }

  if (empty($_POST['assignment_start'])) {
      $error[] = 'Tanggal mulai wajib diisi';
    } else {
      $assignment_start = date('Y-m-d', strtotime($_POST['assignment_start']));
  }

  if (empty($_POST['assignment_end'])) {
      $error[] = 'Tanggal selesai wajib diisi';
    } else {
      $assignment_end = date('Y-m-d', strtotime($_POST['assignment_end']));
  }

  if (!empty($assignment_start) && !empty($assignment_end) && strtotime($assignment_start) > strtotime($assignment_end)) {
      $error[] = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai';
  }

  if (empty($_POST['assignment_location'])) {
      $error[] = 'Lokasi/tujuan tugas wajib diisi';
    } else {
      $assignment_location = mysqli_real_escape_string($connection, strip_tags($_POST['assignment_location']));
  }

  if (empty($_POST['assignment_description'])) {
      $error[] = 'Keterangan tugas wajib diisi';
    } else {
      $assignment_description = mysqli_real_escape_string($connection, strip_tags($_POST['assignment_description']));
  }

  $allowed_status = array('pending','active','completed','cancelled');
  if (empty($_POST['assignment_status']) || !in_array($_POST['assignment_status'], $allowed_status)) {
      $error[] = 'Status tidak valid';
    } else {
      $assignment_status = mysqli_real_escape_string($connection, $_POST['assignment_status']);
  }

  if (empty($error)) {
    if ($assignment_status == 'active') {
      $check = $connection->query("SELECT assignment_id FROM assignments WHERE assignment_id!='$assignment_id' AND employees_id='$employees_id' AND assignment_status='active' AND assignment_start <= '$assignment_end' AND assignment_end >= '$assignment_start' LIMIT 1");
      if ($check && $check->num_rows > 0) {
        echo'Staff sudah memiliki penugasan aktif pada rentang tanggal tersebut.';
        break;
      }
    }

    $update ="UPDATE assignments SET employees_id='$employees_id',
              assignment_start='$assignment_start',
              assignment_end='$assignment_end',
              assignment_location='$assignment_location',
              assignment_description='$assignment_description',
              assignment_signer_id='$assignment_signer_id',
              assignment_status='$assignment_status',
              updated_at='$timeNow'
              WHERE assignment_id='$assignment_id'";
    if($connection->query($update) === false) {
        echo'Data tidak berhasil disimpan: '.$connection->error;
    } else{
        $connection->query("UPDATE assignment_attendance SET employees_id='$employees_id' WHERE assignment_id='$assignment_id'");
        echo'success';
    }
  } else {
    echo implode('<br>', $error);
  }
break;

case 'extend':
  $error = array();
  if (empty($_POST['assignment_id'])) {
      $error[] = 'ID penugasan wajib diisi';
    } else {
      $assignment_id = mysqli_real_escape_string($connection, $_POST['assignment_id']);
  }
  if (empty($_POST['assignment_end'])) {
      $error[] = 'Tanggal selesai wajib diisi';
    } else {
      $assignment_end = date('Y-m-d', strtotime($_POST['assignment_end']));
  }

  if (empty($error)) {
    $query = $connection->query("SELECT employees_id,assignment_start FROM assignments WHERE assignment_id='$assignment_id' LIMIT 1");
    if (!$query || $query->num_rows == 0) {
      echo'Data penugasan tidak ditemukan.';
      break;
    }
    $row = $query->fetch_assoc();
    if (strtotime($assignment_end) < strtotime($row['assignment_start'])) {
      echo'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
      break;
    }
    $update="UPDATE assignments SET assignment_end='$assignment_end', assignment_status='active', updated_at='$timeNow' WHERE assignment_id='$assignment_id'";
    if($connection->query($update) === false) {
        echo'Data tidak berhasil disimpan: '.$connection->error;
    } else{
        echo'success';
    }
  } else {
    echo implode('<br>', $error);
  }
break;

case 'update-status':
  $error = array();
  if (empty($_POST['id'])) {
      $error[] = 'ID penugasan wajib diisi';
    } else {
      $assignment_id = mysqli_real_escape_string($connection, $_POST['id']);
  }
  $allowed_status = array('active','completed','cancelled');
  if (empty($_GET['status']) || !in_array($_GET['status'], $allowed_status)) {
      $error[] = 'Status tidak valid';
    } else {
      $status = mysqli_real_escape_string($connection, $_GET['status']);
  }

  if (empty($error)) {
    if ($status == 'active') {
      $query_assignment = $connection->query("SELECT assignment_id,employees_id,assignment_start,assignment_end,assignment_number,assignment_status FROM assignments WHERE assignment_id='$assignment_id' LIMIT 1");
      if (!$query_assignment || $query_assignment->num_rows == 0) {
        echo'Data penugasan tidak ditemukan.';
        break;
      }
      $assignment = $query_assignment->fetch_assoc();
      $employees_id = mysqli_real_escape_string($connection, $assignment['employees_id']);
      $assignment_start = mysqli_real_escape_string($connection, $assignment['assignment_start']);
      $assignment_end = mysqli_real_escape_string($connection, $assignment['assignment_end']);
      $check = $connection->query("SELECT assignment_id FROM assignments WHERE assignment_id!='$assignment_id' AND employees_id='$employees_id' AND assignment_status='active' AND assignment_start <= '$assignment_end' AND assignment_end >= '$assignment_start' LIMIT 1");
      if ($check && $check->num_rows > 0) {
        echo'Staff sudah memiliki penugasan aktif pada rentang tanggal tersebut.';
        break;
      }
      $assignment_number = mysqli_real_escape_string($connection, $assignment['assignment_number']);
      if (empty($assignment_number)) {
        $assignment_number = mysqli_real_escape_string($connection, assignment_number($connection, $assignment_start));
      }
      $update="UPDATE assignments SET assignment_status='active', assignment_number='$assignment_number', approved_at='$timeNow', approved_by='$user_id', updated_at='$timeNow' WHERE assignment_id='$assignment_id'";
    } else {
      $update="UPDATE assignments SET assignment_status='$status', updated_at='$timeNow' WHERE assignment_id='$assignment_id'";
    }
    if($connection->query($update) === false) {
        echo'Data tidak berhasil disimpan: '.$connection->error;
    } else{
        $query_notify = $connection->query("SELECT assignments.*,employees.employees_name FROM assignments INNER JOIN employees ON employees.id=assignments.employees_id WHERE assignments.assignment_id='$assignment_id' LIMIT 1");
        if ($query_notify && $query_notify->num_rows > 0) {
          $notify = $query_notify->fetch_assoc();
          $message = '<b>Penugasan '.telegram_status_label($status).'</b>'."\n".
            'No: '.telegram_escape($notify['assignment_number'])."\n".
            'Tanggal: '.telegram_escape(tgl_ind($notify['assignment_start'])).' - '.telegram_escape(tgl_ind($notify['assignment_end']))."\n".
            'Lokasi: '.telegram_escape($notify['assignment_location']);
          telegram_send_employee($connection, $notify['employees_id'], $message, 'assignment-status-'.$assignment_id.'-'.$status);
        }
        echo'success';
    }
  } else {
    echo implode('<br>', $error);
  }
break;

case 'delete':
  if (empty($_POST['id'])) {
      echo'ID penugasan wajib diisi';
      break;
  }
  $assignment_id = mysqli_real_escape_string($connection, epm_decode($_POST['id']));
  if (empty($assignment_id)) {
      echo'ID penugasan tidak valid';
      break;
  }

  $connection->query("DELETE FROM assignment_attendance WHERE assignment_id='$assignment_id'");
  $deleted = "DELETE FROM assignments WHERE assignment_id='$assignment_id'";
  if($connection->query($deleted) === false) {
      echo'Data tidak berhasil dihapus: '.$connection->error;
  } else {
      echo'success';
  }
break;
}

}
?>
