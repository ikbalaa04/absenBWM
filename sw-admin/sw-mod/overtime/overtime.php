<?php 
if(empty($connection)){
  header('location:../../');
} else {
  include_once 'sw-mod/sw-panel.php';
  overtime_autocomplete_running($connection);
echo'
  <div class="content-wrapper">';
echo'
<section class="content-header">
  <h1>Data<small> Pengajuan Lembur</small></h1>
    <ol class="breadcrumb">
      <li><a href="./?mod=home"><i class="fa fa-dashboard"></i> Beranda</a></li>
      <li class="active">Pengajuan Lembur</li>
    </ol>
</section>';
echo'
<section class="content">
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title"><b>Data Pengajuan Lembur</b></h3>
        </div>
        <div class="box-body">
          <div class="table-responsive">
            <table id="swdatatable" class="table table-bordered">
              <thead>
                <tr>
                  <th style="width:10px">No</th>
                  <th>Nama</th>
                  <th>Tanggal</th>
                  <th>Pengajuan</th>
                  <th>Disetujui</th>
                  <th>Aktual</th>
                  <th>Pekerjaan</th>
                  <th>Hasil</th>
                  <th>Status</th>
                  <th style="width:170px" class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>';
  $query="SELECT overtime_requests.*,employees.employees_name FROM overtime_requests INNER JOIN employees ON employees.id=overtime_requests.employees_id ORDER BY overtime_requests.overtime_id DESC";
  $result = $connection->query($query);
  if($result && $result->num_rows > 0){
    $no=0;
    while ($row= $result->fetch_assoc()) {
      $no++;
      $status_class = 'default';
      if ($row['status'] == 'pending') {
        $status_class = 'warning';
      } elseif ($row['status'] == 'approved') {
        $status_class = 'primary';
      } elseif ($row['status'] == 'running') {
        $status_class = 'success';
      } elseif ($row['status'] == 'completed') {
        $status_class = 'info';
      } elseif ($row['status'] == 'rejected' || $row['status'] == 'cancelled') {
        $status_class = 'danger';
      }
      $actual_minutes = $row['status'] == 'running' ? overtime_effective_actual_minutes($row['started_at'], '', $row['approved_minutes']) : $row['actual_minutes'];
      echo'
                <tr>
                  <td class="text-center">'.$no.'</td>
                  <td>'.$row['employees_name'].'</td>
                  <td>'.tgl_ind($row['overtime_date']).'</td>
                  <td>'.overtime_format_minutes($row['requested_minutes']).'</td>
                  <td>'.overtime_format_minutes($row['approved_minutes']).'</td>
                  <td>'.overtime_format_minutes($actual_minutes).'</td>
                  <td>'.nl2br(htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8')).'</td>
                  <td>'.(!empty($row['result_note']) ? nl2br(htmlspecialchars($row['result_note'], ENT_QUOTES, 'UTF-8')) : '-').'</td>
                  <td><span class="label label-'.$status_class.'">'.overtime_status_label($row['status']).'</span></td>
                  <td class="text-center">
                    <div class="btn-group">';
      if($level_user==1 || $level_user==2){
        echo'
                      <div class="btn-group">
                        <button type="button" class="btn btn-warning btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Proses
                          <span class="caret"></span>
                          <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">';
        if($row['status'] == 'pending'){
          echo'
                          <li><a href="javascript:void(0);" class="approve-overtime" data-id="'.$row['overtime_id'].'" data-requested-hours="'.(((int)$row['requested_minutes']) / 60).'" data-approved-hours="'.(((int)$row['requested_minutes']) / 60).'">Setujui</a></li>
                          <li><a href="javascript:void(0);" class="reject-overtime" data-id="'.$row['overtime_id'].'">Tolak</a></li>';
        }
        if(in_array($row['status'], array('approved','running','completed'))){
          echo'
                          <li><a href="javascript:void(0);" class="adjust-overtime" data-id="'.$row['overtime_id'].'" data-requested-hours="'.(((int)$row['requested_minutes']) / 60).'" data-approved-hours="'.(((int)$row['approved_minutes']) / 60).'">Sesuaikan Ulang Waktu</a></li>';
        }
        echo'
                        </ul>
                      </div>
                      <button type="button" data-id="'.$row['overtime_id'].'" class="btn btn-xs btn-danger delete-overtime" title="Hapus"><i class="fa fa-trash-o"></i> Hapus</button>';
      } else {
        echo'
                      <button type="button" class="btn btn-warning btn-xs access-failed">Aksi</button>';
      }
      echo'
                    </div>
                  </td>
                </tr>';
    }
  }
  echo'
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</div>';
}?>
