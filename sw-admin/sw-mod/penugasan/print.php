<?php session_start();
require_once'../../../sw-library/sw-config.php';
require_once'../../../sw-library/sw-function.php';
if(empty($_SESSION['SESSION_USER']) && empty($_SESSION['SESSION_ID'])){
  header('location:../login/');
} else {
switch (@$_GET['action']){
case 'print':
  $error = array();
  if (empty($_GET['id'])) {
    $error[] = 'ID tidak boleh kosong';
  } else {
    $id = mysqli_real_escape_string($connection, epm_decode($_GET['id']));
  }
  if (empty($error)) {
    $query="SELECT assignments.*,employees.employees_name,employees.employees_code,position.position_name,signer.employees_name AS signer_name,signer_position.position_name AS signer_position_name FROM assignments INNER JOIN employees ON employees.id=assignments.employees_id INNER JOIN position ON position.position_id=employees.position_id LEFT JOIN employees AS signer ON signer.id=assignments.assignment_signer_id LEFT JOIN position AS signer_position ON signer_position.position_id=signer.position_id WHERE assignments.assignment_id='$id'";
    $result = $connection->query($query);
    if($result && $result->num_rows > 0){
      $row = $result->fetch_assoc();
      $assignment_signer = !empty($row['signer_name']) ? $row['signer_name'] : $site_manager;
      $assignment_signer_position = !empty($row['signer_position_name']) ? $row['signer_position_name'] : 'Manajemen';
      $letter_header_file = !empty($site_letter_header) ? basename($site_letter_header) : '';
      $letter_header_path = __DIR__.'/../../../sw-content/'.$letter_header_file;
      if(!empty($letter_header_file) && file_exists($letter_header_path)){
        $letter_header_html = '<div class="header text-center"><img class="letter-header-img" src="../../../sw-content/'.htmlspecialchars($letter_header_file, ENT_QUOTES, 'UTF-8').'" alt="Header Surat"></div>';
      } else {
        $letter_header_html = '<div class="header text-center"><h2>'.strtoupper($site_company).'</h2><div>'.$site_address.'</div></div>';
      }
      echo'
<!DOCTYPE html>
<html>
<head>
  <title>Surat Tugas '.$row['employees_name'].'</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;color:#000}
    .container{margin:0 auto;max-width:820px}
    .text-center{text-align:center}.text-right{text-align:right}
    h2,h3{margin:0;line-height:1.35}.header{border-bottom:2px solid #000;padding-bottom:12px;margin-bottom:30px}.letter-header-img{max-width:100%;max-height:140px}
    table{width:100%;border-collapse:collapse}td{padding:7px 4px;vertical-align:top}
    .content{font-size:15px;line-height:1.6}.signature{margin-top:55px}
    .name{font-weight:bold;text-decoration:underline;text-transform:uppercase;margin-top:70px}
    @media print { a[href]:after{content:none!important} @page{margin:0} body{margin:1.6cm} }
  </style>
  <script>window.onafterprint = window.close; window.print();</script>
</head>
<body>
<section class="container">
  '.$letter_header_html.'
  <h3 class="text-center">SURAT TUGAS</h3>
  <p class="text-center">Nomor: '.$row['assignment_number'].'</p>
  <div class="content">
    <p>Yang bertanda tangan di bawah ini menugaskan kepada:</p>
    <table>
      <tr><td width="170">Nama</td><td>: '.$row['employees_name'].'</td></tr>
      <tr><td>NIK</td><td>: '.$row['employees_code'].'</td></tr>
      <tr><td>Jabatan</td><td>: '.$row['position_name'].'</td></tr>
      <tr><td>Waktu Penugasan</td><td>: '.tgl_ind($row['assignment_start']).' sampai '.tgl_ind($row['assignment_end']).'</td></tr>
      <tr><td>Lokasi/Tujuan</td><td>: '.htmlspecialchars($row['assignment_location'], ENT_QUOTES, 'UTF-8').'</td></tr>
      <tr><td>Keterangan</td><td>: '.nl2br(htmlspecialchars($row['assignment_description'], ENT_QUOTES, 'UTF-8')).'</td></tr>
    </table>
    <p>Selama masa penugasan, absensi dilakukan melalui menu Penugasan dan tercatat sebagai absensi dalam tugas.</p>
    <p>Demikian surat tugas ini dibuat untuk digunakan sebagaimana mestinya.</p>
    <div class="signature">
      <div class="text-right">Tanggal '.tgl_indo($date).'</div>
      <table>
        <tr>
          <td class="text-center">Menugaskan<br><small>'.$assignment_signer_position.'</small><p class="name">'.$assignment_signer.'</p></td>
          <td class="text-center">Mengetahui<p class="name">'.$site_director.'</p></td>
        </tr>
      </table>
    </div>
  </div>
</section>
</body>
</html>';
    } else {
      echo'Data tidak ditemukan';
    }
  }
break;
}
}
?>
