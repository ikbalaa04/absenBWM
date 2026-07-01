<?php
session_start();
if(empty($_SESSION['SESSION_USER']) || empty($_SESSION['SESSION_ID'])){
  header('location:../../login/');
  exit;
}

require_once '../../../sw-library/sw-config.php';
require_once '../../login/login_session.php';
include '../../../sw-library/sw-function.php';
require_once '../../../sw-library/attendance-ranking.php';

function ranking_export_period($connection) {
  $settings = attendance_ranking_get_settings($connection);
  $selected_month = isset($_GET['ranking_month']) ? (int)$_GET['ranking_month'] : (int)date('m');
  $selected_year = isset($_GET['ranking_year']) ? (int)$_GET['ranking_year'] : (int)date('Y');

  if ($selected_month < 1 || $selected_month > 12) {
    $selected_month = (int)date('m');
  }
  if ($selected_year < 2000 || $selected_year > ((int)date('Y') + 1)) {
    $selected_year = (int)date('Y');
  }

  $month_from = sprintf('%04d-%02d-01', $selected_year, $selected_month);
  $month_to = date('Y-m-t', strtotime($month_from));
  if ($selected_year === (int)date('Y') && $selected_month === (int)date('m')) {
    $month_to = date('Y-m-d');
  }

  $start_date = !empty($settings['ranking_start_date']) ? $settings['ranking_start_date'] : $month_from;
  $from = strtotime($start_date) > strtotime($month_from) ? $start_date : $month_from;
  $available = strtotime($from) <= strtotime($month_to);

  return array(
    'from' => $from,
    'to' => $month_to,
    'label_from' => $available ? $from : $month_from,
    'available' => $available,
    'month' => $selected_month,
    'year' => $selected_year
  );
}

function ranking_export_rows($connection, $period) {
  if (empty($period['available'])) {
    return array();
  }

  return attendance_ranking_calculate($connection, $period['from'], $period['to'], 0);
}

function ranking_export_table_rows($rows) {
  $html = '';
  $rank = 0;
  foreach ($rows as $row) {
    $rank++;
    $summary = $row['summary'];
    $izin_sakit_cuti = (int)$summary['permission'] + (int)$summary['sick'] + (int)$summary['leave'] + (int)$summary['hourly_permission'];
    $html .= '<tr>
      <td class="text-center">'.$rank.'</td>
      <td>'.htmlspecialchars($row['employees_name'], ENT_QUOTES, 'UTF-8').'</td>
      <td class="text-center">'.(int)$row['score'].'</td>
      <td class="text-center">'.(int)$summary['present'].'</td>
      <td class="text-center">'.(int)$summary['ontime'].'</td>
      <td class="text-center">'.(int)$summary['late'].'</td>
      <td class="text-center">'.(int)$summary['assignment'].'</td>
      <td class="text-center">'.$izin_sakit_cuti.'</td>
      <td class="text-center">'.(int)$summary['absent'].'</td>
      <td class="text-center">'.(int)$summary['missing_checkout'].'</td>
      <td class="text-center">'.(int)$summary['leave_early'].'</td>
    </tr>';
  }

  if ($html === '') {
    $html = '<tr><td colspan="11" class="text-center">Belum ada data ranking pada periode ini.</td></tr>';
  }

  return $html;
}

$type = !empty($_GET['type']) ? strtolower($_GET['type']) : 'pdf';
$period = ranking_export_period($connection);
$rows = ranking_export_rows($connection, $period);
$filename_date = sprintf('%04d-%02d', $period['year'], $period['month']);
$period_label = tgl_ind($period['label_from']).' - '.tgl_ind($period['to']);
$printed_at = tgl_indo(date('Y-m-d H:i:s')).' - '.jam_indo(date('Y-m-d H:i:s'));

$table = '<table>
  <thead>
    <tr>
      <th width="35">Rank</th>
      <th>Nama</th>
      <th>Poin</th>
      <th>Hadir</th>
      <th>Tepat Waktu</th>
      <th>Terlambat</th>
      <th>Tugas</th>
      <th>Izin/Sakit/Cuti</th>
      <th>Alpha</th>
      <th>Lupa Pulang</th>
      <th>Pulang Cepat</th>
    </tr>
  </thead>
  <tbody>'.ranking_export_table_rows($rows).'</tbody>
</table>';

if ($type === 'xls') {
  header('Content-Type: application/vnd-ms-excel; charset=utf-8');
  header('Content-Disposition: attachment; filename=Ranking-Absensi-'.$filename_date.'.xls');
  echo '<html><head><meta charset="utf-8"></head><body>
    <h3>TOP RANKING ABSENSI</h3>
    <p>Periode: '.$period_label.'</p>
    <p>Dicetak: '.$printed_at.'</p>
    '.$table.'
  </body></html>';
  exit;
}

require_once '../../../sw-library/vendor/autoload.php';
$html = '<html><head><style>
  body{font-family:Arial,Helvetica,sans-serif;font-size:10px;color:#000}
  h3{text-align:center;margin:0 0 8px}
  p{margin:3px 0}
  table{width:100%;border-collapse:collapse;margin-top:12px}
  th,td{border:1px solid #777;padding:5px;vertical-align:middle}
  th{background:#f0f0f0;text-align:center}
  .text-center{text-align:center}
</style></head><body>
  <h3>TOP RANKING ABSENSI</h3>
  <p><strong>Periode:</strong> '.$period_label.'</p>
  <p><strong>Dicetak:</strong> '.$printed_at.'</p>
  '.$table.'
</body></html>';

$mpdf = new \Mpdf\Mpdf(array('orientation' => 'L'));
$mpdf->WriteHTML($html);
$mpdf->Output('Ranking-Absensi-'.$filename_date.'.pdf', 'I');
exit;
?>
