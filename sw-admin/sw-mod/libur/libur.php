<?php
if(empty($connection)){
  header('location:../../');
} else {
  include_once 'sw-mod/sw-panel.php';
echo'
  <div class="content-wrapper">';
    switch(@$_GET['op']){
    default:
    $calendar_month = !empty($_GET['bulan']) ? mysqli_real_escape_string($connection, $_GET['bulan']) : date('Y-m');
    if (!preg_match('/^[0-9]{4}-[0-9]{2}$/', $calendar_month)) {
      $calendar_month = date('Y-m');
    }
    $month_start = $calendar_month.'-01';
    $month_title = date('F Y', strtotime($month_start));
    $previous_month = date('Y-m', strtotime('-1 month', strtotime($month_start)));
    $next_month = date('Y-m', strtotime('+1 month', strtotime($month_start)));
    $days_in_month = (int)date('t', strtotime($month_start));
    $first_day_index = (int)date('w', strtotime($month_start));
    $holiday_map = array();
    $query_holidays = "SELECT holiday_id,holiday_date,holiday_name,description,is_active FROM attendance_holidays WHERE holiday_date BETWEEN '$month_start' AND '".date('Y-m-t', strtotime($month_start))."'";
    $result_holidays = $connection->query($query_holidays);
    if($result_holidays && $result_holidays->num_rows > 0){
      while($row_holiday = $result_holidays->fetch_assoc()){
        $holiday_map[$row_holiday['holiday_date']] = $row_holiday;
      }
    }
echo'
<style>
  .holiday-calendar {
    display: grid;
    grid-template-columns: repeat(7, minmax(96px, 1fr));
    border: 1px solid #e5e7eb;
    background: #fff;
  }
  .holiday-day-name,
  .holiday-cell {
    border-right: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
  }
  .holiday-day-name {
    padding: 10px;
    background: #f8fafc;
    font-weight: 700;
    text-align: center;
  }
  .holiday-cell {
    min-height: 112px;
    padding: 10px;
    cursor: pointer;
    transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
  }
  .holiday-cell:hover {
    transform: translateY(-1px);
    box-shadow: inset 0 0 0 2px #3c8dbc;
  }
  .holiday-cell.empty {
    cursor: default;
    background: #fafafa;
  }
  .holiday-cell.empty:hover {
    transform: none;
    box-shadow: none;
  }
  .holiday-cell.is-holiday {
    background: #fff7ed;
  }
  .holiday-cell.is-inactive {
    background: #f3f4f6;
    color: #888;
  }
  .holiday-date-number {
    font-size: 18px;
    font-weight: 700;
  }
  .holiday-badge {
    display: inline-block;
    margin-top: 10px;
    padding: 5px 8px;
    border-radius: 4px;
    background: #f97316;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
  }
  .holiday-cell.is-inactive .holiday-badge {
    background: #9ca3af;
  }
  .holiday-note {
    margin-top: 6px;
    font-size: 12px;
    color: #6b7280;
  }
  .holiday-calendar-tools {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
  }
  @media (max-width: 767px) {
    .holiday-calendar {
      grid-template-columns: repeat(7, minmax(42px, 1fr));
    }
    .holiday-cell {
      min-height: 82px;
      padding: 6px;
    }
    .holiday-badge,
    .holiday-note {
      display: none;
    }
  }
</style>
<section class="content-header">
  <h1>Kalender<small> Libur</small></h1>
    <ol class="breadcrumb">
      <li><a href="./?mod=home"><i class="fa fa-dashboard"></i> Beranda</a></li>
      <li class="active">Kalender Libur</li>
    </ol>
</section>
<section class="content">
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title"><b>Kalender Libur</b></h3>
        </div>
        <div class="box-body">
          <div class="holiday-calendar-tools">
            <a class="btn btn-default" href="./?mod=libur&bulan='.$previous_month.'"><i class="fa fa-chevron-left"></i> Bulan Sebelumnya</a>
            <h3 style="margin:0">'.$month_title.'</h3>
            <a class="btn btn-default" href="./?mod=libur&bulan='.$next_month.'">Bulan Berikutnya <i class="fa fa-chevron-right"></i></a>
          </div>
          <div class="alert alert-info">
            Klik tanggal pada kalender untuk menandai atau mengubah hari libur. Tanggal libur aktif akan menonaktifkan absensi reguler untuk semua karyawan.
          </div>
          <div class="holiday-calendar">';
          $day_names = array('Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab');
          foreach($day_names as $day_name){
            echo'<div class="holiday-day-name">'.$day_name.'</div>';
          }
          for($empty = 0; $empty < $first_day_index; $empty++){
            echo'<div class="holiday-cell empty"></div>';
          }
          for($day = 1; $day <= $days_in_month; $day++){
            $current_date = $calendar_month.'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
            $holiday = isset($holiday_map[$current_date]) ? $holiday_map[$current_date] : null;
            $is_weekend = attendance_is_regular_off_day($current_date);
            $classes = 'holiday-cell';
            if($holiday){
              $classes .= (int)$holiday['is_active'] === 1 ? ' is-holiday' : ' is-inactive';
            } elseif($is_weekend) {
              $classes .= ' is-holiday';
            }
            $data_id = $holiday ? $holiday['holiday_id'] : '';
            $data_name = $holiday ? htmlspecialchars($holiday['holiday_name'], ENT_QUOTES, 'UTF-8') : '';
            $data_description = $holiday ? htmlspecialchars($holiday['description'], ENT_QUOTES, 'UTF-8') : '';
            $data_active = $holiday ? $holiday['is_active'] : '1';
            echo'
            <div class="'.$classes.'" data-id="'.$data_id.'" data-date="'.$current_date.'" data-name="'.$data_name.'" data-description="'.$data_description.'" data-active="'.$data_active.'">
              <div class="holiday-date-number">'.$day.'</div>';
              if($holiday){
                echo'<div class="holiday-badge"><i class="fa fa-star"></i> '.$holiday['holiday_name'].'</div>';
                if(!empty($holiday['description'])){
                  echo'<div class="holiday-note">'.$holiday['description'].'</div>';
                }
              } elseif($is_weekend) {
                echo'<div class="holiday-badge"><i class="fa fa-star"></i> Libur Akhir Pekan</div>';
              }
              echo'
            </div>';
          }
          echo'
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="modal fade" id="modalHoliday" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="holidayModalTitle">Tandai Hari Libur</h4>
      </div>
      <form class="form save-libur">
      <input type="hidden" name="id" id="holidayid">
      <div class="modal-body">
        <div class="form-group">
            <label>Tanggal</label>
            <input type="date" class="form-control" name="holiday_date" id="holidaydate" required>
        </div>
        <div class="form-group">
            <label>Nama Libur</label>
            <input type="text" class="form-control" name="holiday_name" id="holidayname" required>
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <textarea class="form-control" name="description" id="holidaydescription" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" name="is_active" id="holidayactive">
              <option value="1">Aktif</option>
              <option value="0">Nonaktif</option>
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger pull-left holiday-delete-btn" style="display:none"><i class="fa fa-trash-o"></i> Hapus</button>
        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Simpan</button>
      </div>
      </form>
    </div>
  </div>
</div>';
break;
}
echo'
  </div>';
}
?>
