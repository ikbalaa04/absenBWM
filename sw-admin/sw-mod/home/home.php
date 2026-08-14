<?php
if(empty($connection)){
  header('location:../../');
} else {
  include_once 'sw-mod/sw-panel.php';

  $query_employees ="SELECT id FROM employees";
  $result_count = $connection->query($query_employees);

  $query_position ="SELECT position_id FROM position";
  $result_count_position = $connection->query($query_position);

  $query_building ="SELECT building_id FROM building";
  $result_count_building = $connection->query($query_building);

  $query_shift ="SELECT shift_id FROM shift";
  $result_count_shift = $connection->query($query_shift);

  $dashboard_limit = 10;
  $page_absent = isset($_GET['page_absent']) ? max(1, (int)$_GET['page_absent']) : 1;
  $page_login = isset($_GET['page_login']) ? max(1, (int)$_GET['page_login']) : 1;
  $page_cuty = isset($_GET['page_cuty']) ? max(1, (int)$_GET['page_cuty']) : 1;
  $page_overtime = isset($_GET['page_overtime']) ? max(1, (int)$_GET['page_overtime']) : 1;
  $page_ranking_staff = isset($_GET['page_ranking_staff']) ? max(1, (int)$_GET['page_ranking_staff']) : 1;
  $page_ranking_management = isset($_GET['page_ranking_management']) ? max(1, (int)$_GET['page_ranking_management']) : 1;
  $page_weekly_work = isset($_GET['page_weekly_work']) ? max(1, (int)$_GET['page_weekly_work']) : 1;
  $offset_absent = ($page_absent - 1) * $dashboard_limit;
  $offset_login = ($page_login - 1) * $dashboard_limit;
  $offset_cuty = ($page_cuty - 1) * $dashboard_limit;
  $offset_overtime = ($page_overtime - 1) * $dashboard_limit;
  $offset_ranking_staff = ($page_ranking_staff - 1) * $dashboard_limit;
  $offset_ranking_management = ($page_ranking_management - 1) * $dashboard_limit;
  $offset_weekly_work = ($page_weekly_work - 1) * $dashboard_limit;

  if (!function_exists('dashboard_pagination')) {
    function dashboard_pagination($param, $current_page, $total_rows, $limit, $extra_query = array()) {
      $total_pages = (int)ceil($total_rows / $limit);
      if ($total_pages <= 1) {
        return '';
      }

      $html = '<div class="box-footer clearfix"><ul class="pagination pagination-sm no-margin pull-right">';
      for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $current_page) ? ' class="active"' : '';
        $query = $_GET;
        $query['mod'] = 'home';
        foreach ($extra_query as $key => $value) {
          $query[$key] = $value;
        }
        $query[$param] = $i;
        $html .= '<li'.$active.'><a href="./?'.http_build_query($query).'">'.$i.'</a></li>';
      }
      $html .= '</ul></div>';

      return $html;
    }
  }
  if (!function_exists('dashboard_ranking_table')) {
    function dashboard_ranking_table($rows, $offset) {
      $html = '<div class="table-responsive">
            <table class="table table-striped">
              <tbody>
                <tr>
                  <th style="width: 10px" class="text-center">Rank</th>
                  <th>Nama</th>
                  <th>Jabatan</th>
                  <th class="text-center">Poin</th>
                  <th class="text-center">Hadir</th>
                  <th class="text-center">Tepat Waktu</th>
                  <th class="text-center">Terlambat</th>
                  <th class="text-center">Tugas</th>
                  <th class="text-center">Izin/Sakit/Cuti</th>
                  <th class="text-center">Alpha</th>
                  <th class="text-right">Aksi</th>
                </tr>';
      if (count($rows) > 0) {
        $rank_no = $offset;
        foreach ($rows as $ranking_row) {
          $rank_no++;
          $summary = $ranking_row['summary'];
          $rank_label = $rank_no <= 3 ? 'label-success' : 'label-default';
          $izin_sakit_cuti = (int)$summary['permission'] + (int)$summary['sick'] + (int)$summary['leave'] + (int)$summary['hourly_permission'];
          $html .= '
                <tr>
                  <td class="text-center"><span class="label '.$rank_label.'">'.$rank_no.'</span></td>
                  <td>'.htmlspecialchars($ranking_row['employees_name'], ENT_QUOTES, 'UTF-8').'</td>
                  <td>'.htmlspecialchars(isset($ranking_row['position_name']) ? $ranking_row['position_name'] : '-', ENT_QUOTES, 'UTF-8').'</td>
                  <td class="text-center"><strong>'.(int)$ranking_row['score'].'</strong></td>
                  <td class="text-center">'.(int)$summary['present'].'</td>
                  <td class="text-center">'.(int)$summary['ontime'].'</td>
                  <td class="text-center">'.(int)$summary['late'].'</td>
                  <td class="text-center">'.(int)$summary['assignment'].'</td>
                  <td class="text-center">'.$izin_sakit_cuti.'</td>
                  <td class="text-center">'.(int)$summary['absent'].'</td>
                  <td class="text-right"><a href="./?mod=absensi&op=views&id='.epm_encode($ranking_row['employees_id']).'" class="btn btn-warning btn-xs"><i class="fa fa-external-link-square" aria-hidden="true"></i></a></td>
                </tr>';
        }
      } else {
        $html .= '
                <tr>
                  <td colspan="11" class="text-center text-muted">Belum ada data ranking pada periode ini.</td>
                </tr>';
      }
      $html .= '
              </tbody>
            </table>
          </div>';
      return $html;
    }
  }
  if (!function_exists('dashboard_week_bounds')) {
    function dashboard_week_bounds($date) {
      $timestamp = strtotime($date);
      if (!$timestamp) {
        $timestamp = time();
      }
      $day_index = (int)date('N', $timestamp);
      return array(
        'start' => date('Y-m-d', strtotime('-'.($day_index - 1).' days', $timestamp)),
        'end' => date('Y-m-d', strtotime('+'.(7 - $day_index).' days', $timestamp))
      );
    }
  }
  if (!function_exists('dashboard_weekly_work_minutes')) {
    function dashboard_weekly_work_minutes($connection, $employees_id, $week_start, $week_end) {
      $employees_id = mysqli_real_escape_string($connection, $employees_id);
      $week_start = mysqli_real_escape_string($connection, $week_start);
      $week_end = mysqli_real_escape_string($connection, $week_end);
      $minutes = 0;
      $days = 0;
      $query = "SELECT presence_date,rule_time_in,rule_time_out,rule_min_work_minutes FROM presence WHERE employees_id='$employees_id' AND presence_date BETWEEN '$week_start' AND '$week_end' AND present_id='1'";
      $result = $connection->query($query);
      if ($result) {
        while ($row = $result->fetch_assoc()) {
          $days++;
          $minutes += attendance_daily_credit_minutes($row['presence_date'], $row['rule_time_in'], $row['rule_time_out'], $row['rule_min_work_minutes']);
        }
      }
      return array('minutes' => (int)$minutes, 'days' => $days);
    }
  }
  if (!function_exists('dashboard_weekly_work_rows')) {
    function dashboard_weekly_work_rows($connection, $week_start, $week_end) {
      $rows = array();
      $query = "SELECT employees.id,employees.employees_name,employees.shift_id,employees.attendance_mode,position.position_name,shift.shift_name
        FROM employees
        INNER JOIN position ON position.position_id=employees.position_id
        INNER JOIN shift ON shift.shift_id=employees.shift_id
        WHERE employees.employees_status='active'
        ORDER BY employees.employees_name ASC";
      $result = $connection->query($query);
      if (!$result) {
        return $rows;
      }
      while ($employee = $result->fetch_assoc()) {
        $target_minutes = attendance_shift_weekly_work_minutes($connection, $employee['shift_id'], isset($employee['attendance_mode']) ? $employee['attendance_mode'] : '');
        $actual = dashboard_weekly_work_minutes($connection, $employee['id'], $week_start, $week_end);
        $actual_minutes = (int)$actual['minutes'];
        $remaining_minutes = max(0, $target_minutes - $actual_minutes);
        $percentage = $target_minutes > 0 ? min(100, (int)floor(($actual_minutes / $target_minutes) * 100)) : 100;
        $rows[] = array(
          'employees_id' => $employee['id'],
          'employees_name' => $employee['employees_name'],
          'position_name' => $employee['position_name'],
          'shift_name' => $employee['shift_name'],
          'target_minutes' => $target_minutes,
          'actual_minutes' => $actual_minutes,
          'remaining_minutes' => $remaining_minutes,
          'days' => (int)$actual['days'],
          'percentage' => $percentage,
          'fulfilled' => $target_minutes <= 0 || $actual_minutes >= $target_minutes
        );
      }
      return $rows;
    }
  }
  if (!function_exists('dashboard_weekly_work_table')) {
    function dashboard_weekly_work_table($rows, $offset) {
      $html = '<div class="table-responsive">
            <table class="table table-striped">
              <tbody>
                <tr>
                  <th style="width: 10px" class="text-center">No.</th>
                  <th>Nama</th>
                  <th>Jabatan</th>
                  <th>Shift</th>
                  <th class="text-center">Hari Hadir</th>
                  <th class="text-center">Target Mingguan</th>
                  <th class="text-center">Terpenuhi</th>
                  <th class="text-center">Sisa</th>
                  <th class="text-center">Progress</th>
                  <th class="text-center">Status</th>
                  <th class="text-right">Aksi</th>
                </tr>';
      if (count($rows) > 0) {
        $no = $offset;
        foreach ($rows as $row) {
          $no++;
          $status = $row['fulfilled'] ? '<span class="label label-success">Terpenuhi</span>' : '<span class="label label-warning">Belum Terpenuhi</span>';
          $html .= '
                <tr>
                  <td class="text-center">'.$no.'</td>
                  <td>'.htmlspecialchars($row['employees_name'], ENT_QUOTES, 'UTF-8').'</td>
                  <td>'.htmlspecialchars($row['position_name'], ENT_QUOTES, 'UTF-8').'</td>
                  <td>'.htmlspecialchars($row['shift_name'], ENT_QUOTES, 'UTF-8').'</td>
                  <td class="text-center">'.(int)$row['days'].'</td>
                  <td class="text-center">'.attendance_format_minutes($row['target_minutes']).'</td>
                  <td class="text-center">'.attendance_format_minutes($row['actual_minutes']).'</td>
                  <td class="text-center">'.attendance_format_minutes($row['remaining_minutes']).'</td>
                  <td class="text-center">'.(int)$row['percentage'].'%</td>
                  <td class="text-center">'.$status.'</td>
                  <td class="text-right"><a href="./?mod=absensi&op=views&id='.epm_encode($row['employees_id']).'" class="btn btn-warning btn-xs"><i class="fa fa-external-link-square" aria-hidden="true"></i></a></td>
                </tr>';
        }
      } else {
        $html .= '
                <tr>
                  <td colspan="11" class="text-center text-muted">Belum ada data pemenuhan jam kerja pada minggu ini.</td>
                </tr>';
      }
      $html .= '
              </tbody>
            </table>
          </div>';
      return $html;
    }
  }

  $ranking_settings = attendance_ranking_get_settings($connection);
  $ranking_enabled = !empty($ranking_settings['ranking_enabled']);
  $ranking_selected_month = isset($_GET['ranking_month']) ? (int)$_GET['ranking_month'] : (int)date('m');
  $ranking_selected_year = isset($_GET['ranking_year']) ? (int)$_GET['ranking_year'] : (int)date('Y');
  if ($ranking_selected_month < 1 || $ranking_selected_month > 12) {
    $ranking_selected_month = (int)date('m');
  }
  if ($ranking_selected_year < 2000 || $ranking_selected_year > ((int)date('Y') + 1)) {
    $ranking_selected_year = (int)date('Y');
  }
  $ranking_month_from = sprintf('%04d-%02d-01', $ranking_selected_year, $ranking_selected_month);
  $ranking_month_to = date('Y-m-t', strtotime($ranking_month_from));
  if ($ranking_selected_year === (int)date('Y') && $ranking_selected_month === (int)date('m')) {
    $ranking_month_to = date('Y-m-d');
  }
  $ranking_start_date = !empty($ranking_settings['ranking_start_date']) ? $ranking_settings['ranking_start_date'] : $ranking_month_from;
  $ranking_from = strtotime($ranking_start_date) > strtotime($ranking_month_from) ? $ranking_start_date : $ranking_month_from;
  $ranking_to = $ranking_month_to;
  $ranking_period_available = strtotime($ranking_from) <= strtotime($ranking_to);
  $ranking_label_from = $ranking_period_available ? $ranking_from : $ranking_month_from;
  $ranking_rows = ($ranking_enabled && $ranking_period_available) ? attendance_ranking_calculate($connection, $ranking_from, $ranking_to, 0) : array();
  $ranking_staff_rows = array();
  $ranking_management_rows = array();
  foreach ($ranking_rows as $ranking_row) {
    if (isset($ranking_row['ranking_group']) && $ranking_row['ranking_group'] === 'management') {
      $ranking_management_rows[] = $ranking_row;
    } else {
      $ranking_staff_rows[] = $ranking_row;
    }
  }
  $total_ranking_staff_rows = count($ranking_staff_rows);
  $total_ranking_management_rows = count($ranking_management_rows);
  if ($total_ranking_staff_rows > 0 && $offset_ranking_staff >= $total_ranking_staff_rows) {
    $page_ranking_staff = max(1, (int)ceil($total_ranking_staff_rows / $dashboard_limit));
    $offset_ranking_staff = ($page_ranking_staff - 1) * $dashboard_limit;
  }
  if ($total_ranking_management_rows > 0 && $offset_ranking_management >= $total_ranking_management_rows) {
    $page_ranking_management = max(1, (int)ceil($total_ranking_management_rows / $dashboard_limit));
    $offset_ranking_management = ($page_ranking_management - 1) * $dashboard_limit;
  }
  $ranking_staff_page_rows = array_slice($ranking_staff_rows, $offset_ranking_staff, $dashboard_limit);
  $ranking_management_page_rows = array_slice($ranking_management_rows, $offset_ranking_management, $dashboard_limit);
  $ranking_active_tab = (isset($_GET['ranking_tab']) && $_GET['ranking_tab'] === 'management') ? 'management' : 'staff';
  $ranking_month_names = array(
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
  );
  $ranking_min_year = (int)date('Y');
  $ranking_min_result = $connection->query("SELECT MIN(YEAR(presence_date)) AS min_year FROM presence");
  if ($ranking_min_result) {
    $ranking_min_row = $ranking_min_result->fetch_assoc();
    if (!empty($ranking_min_row['min_year'])) {
      $ranking_min_year = (int)$ranking_min_row['min_year'];
    }
  }
  if (!empty($ranking_settings['ranking_start_date'])) {
    $ranking_min_year = min($ranking_min_year, (int)date('Y', strtotime($ranking_settings['ranking_start_date'])));
  }
  $ranking_month_options = '';
  foreach ($ranking_month_names as $month_number => $month_name) {
    $selected = $month_number === $ranking_selected_month ? ' selected' : '';
    $ranking_month_options .= '<option value="'.$month_number.'"'.$selected.'>'.$month_name.'</option>';
  }
  $ranking_year_options = '';
  for ($year_option = (int)date('Y'); $year_option >= $ranking_min_year; $year_option--) {
    $selected = $year_option === $ranking_selected_year ? ' selected' : '';
    $ranking_year_options .= '<option value="'.$year_option.'"'.$selected.'>'.$year_option.'</option>';
  }

  $weekly_selected_date = isset($_GET['weekly_date']) ? trim($_GET['weekly_date']) : date('Y-m-d');
  $weekly_selected_timestamp = strtotime($weekly_selected_date);
  if (!$weekly_selected_timestamp) {
    $weekly_selected_date = date('Y-m-d');
    $weekly_selected_timestamp = strtotime($weekly_selected_date);
  }
  if (!empty($ranking_settings['ranking_start_date']) && strtotime($weekly_selected_date) < strtotime($ranking_settings['ranking_start_date'])) {
    $weekly_selected_date = $ranking_settings['ranking_start_date'];
    $weekly_selected_timestamp = strtotime($weekly_selected_date);
  }
  $weekly_bounds = dashboard_week_bounds($weekly_selected_date);
  $weekly_effective_start = !empty($ranking_settings['ranking_start_date']) && strtotime($ranking_settings['ranking_start_date']) > strtotime($weekly_bounds['start']) ? $ranking_settings['ranking_start_date'] : $weekly_bounds['start'];
  $weekly_effective_end = $weekly_bounds['end'];
  $weekly_work_rows = dashboard_weekly_work_rows($connection, $weekly_effective_start, $weekly_effective_end);
  $total_weekly_work_rows = count($weekly_work_rows);
  if ($total_weekly_work_rows > 0 && $offset_weekly_work >= $total_weekly_work_rows) {
    $page_weekly_work = max(1, (int)ceil($total_weekly_work_rows / $dashboard_limit));
    $offset_weekly_work = ($page_weekly_work - 1) * $dashboard_limit;
  }
  $weekly_work_page_rows = array_slice($weekly_work_rows, $offset_weekly_work, $dashboard_limit);


echo'
<div class="content-wrapper">
<section class="content">
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3>'.$result_count->num_rows.'</h3>
              <p>Karyawan</p>
            </div>
            <div class="icon">
              <i class="fa fa-user"></i>
            </div>
              <a href="./?mod=karyawan" class="small-box-footer">
              More info <i class="fa fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-yellow">
            <div class="inner">
              <h3>'.$result_count_position->num_rows.'</h3>
              <p>Jabatan</p>
            </div>
            <div class="icon">
              <i class="fa fa fa-briefcase"></i>
            </div>
            <a href="./?mod=jabatan" class="small-box-footer">
             More info <i class="fa fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-red">
            <div class="inner">
              <h3>'.$result_count_building->num_rows.'</h3>
              <p>Lokasi Kantor</p>
            </div>
            <div class="icon">
              <i class="fa fa-building"></i>
            </div>
            <a href="./?mod=lokasi" class="small-box-footer">
              More info <i class="fa fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-green">
            <div class="inner">
              <h3>'.$result_count_shift->num_rows.'</h3>
              <p>Jam Kerja</p>
            </div>
            <div class="icon">
              <i class="fa fa-retweet"></i>
            </div>
            <a href="./?mod=shift" class="small-box-footer">
              More Info <i class="fa fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
        
      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title">Statistik Absensi</h3>
        </div>
          <div class="box-body">
            <div class="chart">
               <canvas id="areaChart" style="height:300px"></canvas>
            </div>
          </div>
        </div>
      </div>

      ';
      if($ranking_enabled){
        echo'
      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title">Ranking Absensi</h3>
          <div class="box-tools pull-right">
            <span class="label label-primary">'.tgl_ind($ranking_label_from).' - '.tgl_ind($ranking_to).'</span>
          </div>
        </div>
          <div class="box-body">
            <form method="get" action="./" class="form-inline">
              <input type="hidden" name="mod" value="home">
              <input type="hidden" name="page_ranking_staff" value="1">
              <input type="hidden" name="page_ranking_management" value="1">
              <div class="form-group">
                <label>Bulan</label>
                <select name="ranking_month" class="form-control input-sm">'.$ranking_month_options.'</select>
              </div>
              <div class="form-group">
                <label>Tahun</label>
                <select name="ranking_year" class="form-control input-sm">'.$ranking_year_options.'</select>
              </div>
              <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
              <a href="sw-mod/home/ranking-export.php?type=pdf&ranking_month='.$ranking_selected_month.'&ranking_year='.$ranking_selected_year.'" target="_blank" class="btn btn-danger btn-sm"><i class="fa fa-file-pdf-o"></i> PDF</a>
              <a href="sw-mod/home/ranking-export.php?type=xls&ranking_month='.$ranking_selected_month.'&ranking_year='.$ranking_selected_year.'" target="_blank" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Excel</a>
            </form>
          </div>
          <div class="box-body no-padding">
            <ul class="nav nav-tabs">
              <li class="'.($ranking_active_tab === 'staff' ? 'active' : '').'"><a href="#ranking-staff" data-toggle="tab">Staff <span class="label label-default">'.$total_ranking_staff_rows.'</span></a></li>
              <li class="'.($ranking_active_tab === 'management' ? 'active' : '').'"><a href="#ranking-management" data-toggle="tab">Manajemen <span class="label label-default">'.$total_ranking_management_rows.'</span></a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane '.($ranking_active_tab === 'staff' ? 'active' : '').'" id="ranking-staff">
                '.dashboard_ranking_table($ranking_staff_page_rows, $offset_ranking_staff).dashboard_pagination('page_ranking_staff', $page_ranking_staff, $total_ranking_staff_rows, $dashboard_limit, array('ranking_tab' => 'staff')).'
              </div>
              <div class="tab-pane '.($ranking_active_tab === 'management' ? 'active' : '').'" id="ranking-management">
                '.dashboard_ranking_table($ranking_management_page_rows, $offset_ranking_management).dashboard_pagination('page_ranking_management', $page_ranking_management, $total_ranking_management_rows, $dashboard_limit, array('ranking_tab' => 'management')).'
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>';
      }
      echo'

      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title">Pemenuhan Jam Minimal Mingguan</h3>
          <div class="box-tools pull-right">
            <span class="label label-primary">'.tgl_ind($weekly_effective_start).' - '.tgl_ind($weekly_effective_end).'</span>
          </div>
        </div>
          <div class="box-body">
            <form method="get" action="./" class="form-inline">
              <input type="hidden" name="mod" value="home">
              <input type="hidden" name="ranking_month" value="'.$ranking_selected_month.'">
              <input type="hidden" name="ranking_year" value="'.$ranking_selected_year.'">
              <input type="hidden" name="page_weekly_work" value="1">
              <div class="form-group">
                <label>Minggu</label>
                <input type="date" name="weekly_date" class="form-control input-sm" value="'.htmlspecialchars($weekly_selected_date, ENT_QUOTES, 'UTF-8').'">
              </div>
              <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
              <span class="text-muted" style="margin-left:8px">Tanggal efektif aplikasi: '.tgl_ind($ranking_settings['ranking_start_date']).'</span>
            </form>
          </div>
          <div class="box-body no-padding">
            '.dashboard_weekly_work_table($weekly_work_page_rows, $offset_weekly_work).dashboard_pagination('page_weekly_work', $page_weekly_work, $total_weekly_work_rows, $dashboard_limit, array('weekly_date' => $weekly_selected_date, 'ranking_month' => $ranking_selected_month, 'ranking_year' => $ranking_selected_year)).'
          </div>
        </div>
      </div>

      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
        <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title">Absensi Hari ini</h3>
        </div>
          <div class="box-body no-padding">
            <div class="table-responsive">
            <table class="table">
              <tbody>
                <tr>
                  <th style="width: 10px" class="text-center">No.</th>
                  <th>Nama</th>
                  <th>Jam Masuk</th>
                  <th>Jam Pulang</th>
                  <th class="text-right">Aksi</th>
                </tr>
                ';
                $query_absent_count ="SELECT presence.presence_id FROM presence,employees WHERE presence.employees_id=employees.id AND presence.presence_date='$date'";
                $result_absent_count = $connection->query($query_absent_count);
                $total_absent_day = $result_absent_count ? $result_absent_count->num_rows : 0;
                $query_absent_day ="SELECT presence.employees_id,presence.time_in,presence.time_out,employees.employees_name FROM presence,employees WHERE presence.employees_id=employees.id AND presence.presence_date='$date' ORDER BY presence.presence_id DESC LIMIT $dashboard_limit OFFSET $offset_absent";
                $result_absent_day = $connection->query($query_absent_day);
                if($result_absent_day->num_rows > 0){
                $no=$offset_absent;
                while ($row = $result_absent_day->fetch_assoc()) {
                  $no++;
                  echo'
                <tr>
                  <td class="text-center">'.$no.'</td>
                  <td>'.$row['employees_name'].'</td>
                  <td>'.$row['time_in'].'</td>
                  <td>'.$row['time_out'].'</td>
                  <td class="text-right"><a href="./?mod=absensi&op=views&id='.epm_encode($row['employees_id']).'" class="btn btn-warning btn-xs"><i class="fa fa-external-link-square" aria-hidden="true"></i></a></td>
                </tr>';}}
                echo'
              </tbody>
            </table>
            </div>
          </div>
          '.dashboard_pagination('page_absent', $page_absent, $total_absent_day, $dashboard_limit).'
        </div>
      </div>

      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
        <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title">Last Login Karyawan</h3>
          <div class="box-tools pull-right">
            <a href="./?mod=karyawan" class="btn btn-success btn-flat">Data Karyawan</a>
          </div>
        </div>
          <div class="box-body no-padding">
          <div class="table-responsive">
          <table class="table">
            <tbody>
                <tr>
                  <th style="width: 10px" class="text-center">No.</th>
                  <th>Nama</th>
                  <th>Email</th>
                  <th class="text-right">Last Login</th>
                </tr>
                ';
                $query_last_login_count="SELECT id FROM employees";
                $result_last_login_count = $connection->query($query_last_login_count);
                $total_last_login = $result_last_login_count ? $result_last_login_count->num_rows : 0;
                $query_last_login="SELECT employees_name,employees_email,created_login FROM employees ORDER BY created_login DESC LIMIT $dashboard_limit OFFSET $offset_login";
                $result_last_login = $connection->query($query_last_login);
                if($result_last_login->num_rows > 0){
                $no=$offset_login;
                while ($row_login= $result_last_login->fetch_assoc()) {
                $no++;
                $last_login = ($row_login['created_login'] != '0000-00-00 00:00:00' && !empty($row_login['created_login'])) ? tgl_indo($row_login['created_login']).' - '.jam_indo($row_login['created_login']) : '<span class="text-muted">Belum login</span>';
                  echo'
                <tr>
                  <td class="text-center">'.$no.'</td>
                  <td>'.$row_login['employees_name'].'</td>
                  <td>'.$row_login['employees_email'].'</td>
                  <td class="text-right">'.$last_login.'</td>
                </tr>';}
                }
          echo'
            </tbody>
          </table>
          </div>
          </div>
          '.dashboard_pagination('page_login', $page_login, $total_last_login, $dashboard_limit).'
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title">Permohonan Cuti</h3>
          <div class="box-tools pull-right">
            <a href="./?mod=cuty" class="btn btn-success btn-flat">Data Cuti</a>
          </div>
        </div>
          <div class="box-body no-padding">
          <div class="table-responsive">
          <table class="table">
            <tbody>
                <tr>
                  <th style="width: 10px" class="text-center">No.</th>
                  <th>Nama</th>
                  <th>Tanggal Cuti</th>
                  <th class="text-center">Jumlah</th>
                  <th class="text-right">Masuk Kerja</th>
                </tr>
                ';
                $query_cuty_count="SELECT cuty.cuty_id FROM employees,cuty WHERE employees.id=cuty.employees_id AND cuty.cuty_status='3'";
                $result_cuty_count = $connection->query($query_cuty_count);
                $total_cuty = $result_cuty_count ? $result_cuty_count->num_rows : 0;
                $query_cuty="SELECT employees.employees_name,cuty.* FROM employees,cuty WHERE employees.id=cuty.employees_id AND cuty.cuty_status='3' order by cuty.cuty_id DESC LIMIT $dashboard_limit OFFSET $offset_cuty";
                $result_cuty = $connection->query($query_cuty);
                if($result_cuty->num_rows > 0){
                $no=$offset_cuty;
                while ($row_cuty= $result_cuty->fetch_assoc()) {
                $no++;
                  echo'
                <tr>
                  <td class="text-center">'.$no.'</td>
                  <td>'.$row_cuty['employees_name'].'</td>
                  <td>'.tgl_ind($row_cuty['cuty_start']).' sampai '.tgl_ind($row_cuty['cuty_end']).'</td>
                  <td class="text-center"><label class="label label-warning">'.$row_cuty['cuty_total'].'</label></td>
                  <td class="text-right">'.tgl_ind($row_cuty['date_work']).'</td>
                </tr>';}
                }
          echo'
            </tbody>
          </table>
          </div>
          </div>
          '.dashboard_pagination('page_cuty', $page_cuty, $total_cuty, $dashboard_limit).'
        </div>
      </div>

      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title">Permohonan Lembur</h3>
          <div class="box-tools pull-right">
            <a href="./?mod=overtime" class="btn btn-success btn-flat">Data Lembur</a>
          </div>
        </div>
          <div class="box-body no-padding">
          <div class="table-responsive">
          <table class="table">
            <tbody>
                <tr>
                  <th style="width: 10px" class="text-center">No.</th>
                  <th>Nama</th>
                  <th>Tanggal</th>
                  <th class="text-center">Durasi</th>
                  <th>Pekerjaan</th>
                  <th class="text-right">Status</th>
                </tr>
                ';
                $query_overtime_count="SELECT overtime_requests.overtime_id FROM employees,overtime_requests WHERE employees.id=overtime_requests.employees_id AND overtime_requests.status='pending'";
                $result_overtime_count = $connection->query($query_overtime_count);
                $total_overtime = $result_overtime_count ? $result_overtime_count->num_rows : 0;
                $query_overtime="SELECT employees.employees_name,overtime_requests.* FROM employees,overtime_requests WHERE employees.id=overtime_requests.employees_id AND overtime_requests.status='pending' order by overtime_requests.overtime_id DESC LIMIT $dashboard_limit OFFSET $offset_overtime";
                $result_overtime = $connection->query($query_overtime);
                if($result_overtime && $result_overtime->num_rows > 0){
                $no=$offset_overtime;
                while ($row_overtime= $result_overtime->fetch_assoc()) {
                $no++;
                  echo'
                <tr>
                  <td class="text-center">'.$no.'</td>
                  <td>'.$row_overtime['employees_name'].'</td>
                  <td>'.tgl_ind($row_overtime['overtime_date']).'</td>
                  <td class="text-center"><label class="label label-warning">'.overtime_format_minutes($row_overtime['requested_minutes']).'</label></td>
                  <td>'.htmlspecialchars($row_overtime['description'], ENT_QUOTES, 'UTF-8').'</td>
                  <td class="text-right"><label class="label label-warning">Menunggu</label></td>
                </tr>';}
                }
          echo'
            </tbody>
          </table>
          </div>
          </div>
          '.dashboard_pagination('page_overtime', $page_overtime, $total_overtime, $dashboard_limit).'
        </div>
      </div>
  </div>
</section>
</div>';


  $date = date("d-m-Y",strtotime("-6 days"));
    $D = substr($date,0,2);
    $M = substr($date,3,2)-1;
    $Y = substr($date,6,4);
    $tgl_skrg = date("Y-m-d");
    $seminggu = strtotime("-1 week +1 day",strtotime($tgl_skrg));
    $hasilnya = date('Y-m-d', $seminggu);
    //visitor
    for ($i=0; $i<=6; $i++){
      $tgl_pengujung   = strtotime("+$i day",strtotime($hasilnya));
      $hasil_pengujung = date("Y-m-d", $tgl_pengujung);
      $tanggal_visitor []= tgl_ind($hasil_pengujung);
      $query_absensi ="SELECT presence_date FROM presence WHERE presence_date='$hasil_pengujung'";
      $result_absensi = $connection->query($query_absensi);
      $absensi [] = $result_absensi->num_rows;

    }
 $tanggal_visitor = implode('","',$tanggal_visitor);?>
 <script type="text/javascript">
    var lineChartData = {
      labels :["<?php echo $tanggal_visitor;?>"],
      datasets : [
        {
          label: "Statistik Absensi",
          fillColor : "rgba(29,75,251,0.7)",
          strokeColor : "rgba(220,220,220,1)",
          pointColor : "rgba(220,220,220,1)",
          pointStrokeColor : "#fff",
          pointHighlightFill : "#fff",
          pointHighlightStroke : "rgba(220,220,220,1)",
          data :<?php echo json_encode($absensi);?>

        }
      ]

    }

  window.onload = function(){
    var ctx = document.getElementById("areaChart").getContext("2d");
    window.myLine = new Chart(ctx).Line(lineChartData, {
      responsive: true
    });
  }
 
</script>
<?PHP
}?>
