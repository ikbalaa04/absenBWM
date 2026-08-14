<?php
if(empty($connection)){
  header('location:../../');
} else {
  include_once 'sw-mod/sw-panel.php';
echo'
  <div class="content-wrapper">';
echo'
<section class="content-header">
  <h1>Data<small> Perbaikan Absensi</small></h1>
    <ol class="breadcrumb">
      <li><a href="./?mod=home"><i class="fa fa-dashboard"></i> Beranda</a></li>
      <li class="active">Perbaikan Absensi</li>
    </ol>
</section>';
echo'
<section class="content">
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title"><b>Pengajuan Perbaikan Absensi</b></h3>
        </div>
        <div class="box-body">
          <div class="table-responsive">
            <table id="swdatatable" class="table table-bordered">
              <thead>
                <tr>
                  <th style="width:10px">No</th>
                  <th>Nama</th>
                  <th>Tanggal</th>
                  <th>Jenis</th>
                  <th>Jam Masuk</th>
                  <th>Jam Pulang</th>
                  <th>Alasan</th>
                  <th>Foto Bukti</th>
                  <th>Status</th>
                  <th style="width:230px" class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>';
  $query="SELECT attendance_correction_requests.*,employees.employees_name
    FROM attendance_correction_requests
    INNER JOIN employees ON employees.id=attendance_correction_requests.employees_id
    ORDER BY attendance_correction_requests.correction_id DESC";
  $result = $connection->query($query);
  if($result && $result->num_rows > 0){
    $no=0;
    while ($row= $result->fetch_assoc()) {
      $no++;
      $status_class = attendance_correction_status_class($row['status']);
      $proof_file = isset($row['proof_file']) ? $row['proof_file'] : '';
      $proof_url = $proof_file !== '' ? '../sw-content/absent/'.rawurlencode($proof_file) : '';
      $proof_preview = $proof_url !== '' ? '<a class="image-link" href="'.$proof_url.'" target="_blank"><img src="'.$proof_url.'" width="45" height="45" style="object-fit:cover;border-radius:4px;"></a>' : '-';
      $detail_data = array(
        'nama' => $row['employees_name'],
        'tanggal' => tgl_ind($row['correction_date']),
        'jenis' => attendance_correction_type_label($row['correction_type']),
        'jam_masuk' => !empty($row['requested_time_in']) ? substr($row['requested_time_in'],0,5) : '-',
        'jam_pulang' => !empty($row['requested_time_out']) ? substr($row['requested_time_out'],0,5) : '-',
        'alasan' => $row['reason'],
        'status' => attendance_correction_status_label($row['status']),
        'foto' => $proof_url
      );
      $detail_json = htmlspecialchars(json_encode($detail_data, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
      echo'
                <tr>
                  <td class="text-center">'.$no.'</td>
                  <td>'.htmlspecialchars($row['employees_name'], ENT_QUOTES, 'UTF-8').'</td>
                  <td>'.tgl_ind($row['correction_date']).'</td>
                  <td>'.attendance_correction_type_label($row['correction_type']).'</td>
                  <td>'.(!empty($row['requested_time_in']) ? substr($row['requested_time_in'],0,5) : '-').'</td>
                  <td>'.(!empty($row['requested_time_out']) ? substr($row['requested_time_out'],0,5) : '-').'</td>
                  <td>'.nl2br(htmlspecialchars($row['reason'], ENT_QUOTES, 'UTF-8')).'</td>
                  <td class="text-center">'.$proof_preview.'</td>
                  <td><span class="label label-'.$status_class.'">'.attendance_correction_status_label($row['status']).'</span></td>
                  <td class="text-center">
                    <div class="btn-group">';
      if($level_user==1 || $level_user==2){
        echo'
                      <button type="button" class="btn btn-xs btn-info detail-attendance-correction" data-detail="'.$detail_json.'" title="Detail"><i class="fa fa-search"></i> Detail</button>
                      <div class="btn-group">
                        <button type="button" class="btn btn-warning btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Proses
                          <span class="caret"></span>
                          <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">';
        if($row['status'] == 'pending'){
          echo'
                          <li><a href="javascript:void(0);" class="approve-attendance-correction" data-id="'.$row['correction_id'].'">Setujui</a></li>
                          <li><a href="javascript:void(0);" class="reject-attendance-correction" data-id="'.$row['correction_id'].'">Tolak</a></li>';
        }
        echo'
                        </ul>
                      </div>
                      <button type="button" data-id="'.$row['correction_id'].'" class="btn btn-xs btn-danger delete-attendance-correction" title="Hapus"><i class="fa fa-trash-o"></i> Hapus</button>';
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
