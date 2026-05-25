<?php
if(empty($connection)){
  header('location:../../');
} else {
  include_once 'sw-mod/sw-panel.php';
  assignment_refresh_status($connection);
  $management_options = '';
  $query_management="SELECT employees.id,employees.employees_code,employees.employees_name,position.position_name FROM employees INNER JOIN position ON position.position_id=employees.position_id WHERE position.position_name LIKE '%Manajemen%' ORDER BY employees.employees_name ASC";
  $result_management = $connection->query($query_management);
  if($result_management && $result_management->num_rows > 0){
    while($manager = $result_management->fetch_assoc()) {
      $management_options .= '<option value="'.$manager['id'].'">'.$manager['employees_name'].' - '.$manager['position_name'].'</option>';
    }
  } else {
    $management_options = '<option value="" disabled>Tidak ada user dengan jabatan Manajemen</option>';
  }
echo'
  <div class="content-wrapper">
<section class="content-header">
  <h1>Data<small> Penugasan</small></h1>
    <ol class="breadcrumb">
      <li><a href="./?mod=home"><i class="fa fa-dashboard"></i> Beranda</a></li>
      <li class="active">Data Penugasan</li>
    </ol>
</section>

<section class="content">
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title"><b>Data Penugasan</b></h3>
          <div class="box-tools pull-right">';
          if($level_user == 1){
            echo'<button type="button" class="btn btn-success btn-flat" data-toggle="modal" data-target="#modalAdd"><i class="fa fa-plus"></i> Tambah Penugasan</button>';
          } else {
            echo'<button type="button" class="btn btn-success btn-flat access-failed"><i class="fa fa-plus"></i> Tambah Penugasan</button>';
          }
          echo'
          </div>
        </div>
        <div class="box-body">
          <div class="table-responsive">
            <table id="swdatatable" class="table table-bordered">
              <thead>
              <tr>
                <th style="width:20px" class="text-center">No</th>
                <th>No. Surat</th>
                <th>Nama Staff</th>
                <th>Pemberi Tugas</th>
                <th>Waktu Penugasan</th>
                <th>Lokasi/Tujuan</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th style="width:210px" class="text-center">Aksi</th>
              </tr>
              </thead>
              <tbody>';
              $query="SELECT assignments.*,employees.employees_name,employees.employees_code,signer.employees_name AS signer_name FROM assignments INNER JOIN employees ON employees.id=assignments.employees_id LEFT JOIN employees AS signer ON signer.id=assignments.assignment_signer_id ORDER BY assignments.assignment_id DESC";
              $result = $connection->query($query);
              if($result && $result->num_rows > 0){
                $no=0;
                while ($row= $result->fetch_assoc()) {
                  $no++;
                  if($row['assignment_status'] == 'active'){
                    $status = '<span class="label label-success">Aktif</span>';
                  } elseif($row['assignment_status'] == 'completed'){
                    $status = '<span class="label label-default">Selesai</span>';
                  } else {
                    $status = '<span class="label label-danger">Dibatalkan</span>';
                  }
                  $description = htmlspecialchars($row['assignment_description'], ENT_QUOTES, 'UTF-8');
                  $description_attr = htmlspecialchars($row['assignment_description'], ENT_QUOTES, 'UTF-8');
                  $location_attr = htmlspecialchars($row['assignment_location'], ENT_QUOTES, 'UTF-8');
                  echo'
                  <tr>
                    <td class="text-center">'.$no.'</td>
                    <td>'.$row['assignment_number'].'</td>
                    <td>'.$row['employees_name'].'<br><small>'.$row['employees_code'].'</small></td>
                    <td>'.(!empty($row['signer_name']) ? $row['signer_name'] : '<span class="text-muted">-</span>').'</td>
                    <td>'.tgl_ind($row['assignment_start']).' - '.tgl_ind($row['assignment_end']).'</td>
                    <td>'.htmlspecialchars($row['assignment_location'], ENT_QUOTES, 'UTF-8').'</td>
                    <td>'.nl2br($description).'</td>
                    <td>'.$status.'</td>
                    <td class="text-center">
                      <div class="btn-group">';
                      if($level_user == 1){
                        echo'
                        <button type="button" class="btn btn-primary btn-xs btn-edit" data-id="'.$row['assignment_id'].'" data-employees="'.$row['employees_id'].'" data-signer="'.$row['assignment_signer_id'].'" data-start="'.$row['assignment_start'].'" data-end="'.$row['assignment_end'].'" data-location="'.$location_attr.'" data-description="'.$description_attr.'" data-status="'.$row['assignment_status'].'" data-toggle="modal" data-target="#modalEdit"><i class="fa fa-pencil"></i> Edit</button>
                        <button type="button" class="btn btn-warning btn-xs btn-extend" data-id="'.$row['assignment_id'].'" data-end="'.$row['assignment_end'].'" data-toggle="modal" data-target="#modalExtend"><i class="fa fa-calendar"></i> Perpanjang</button>
                        <a href="sw-mod/penugasan/print.php?action=print&id='.epm_encode($row['assignment_id']).'" target="_blank" class="btn btn-danger btn-xs"><i class="fa fa-print"></i> Surat</a>
                        <button type="button" data-id="'.$row['assignment_id'].'" data-status="completed" class="btn btn-default btn-xs update-status"><i class="fa fa-check"></i> Selesai</button>
                        <button type="button" data-id="'.$row['assignment_id'].'" data-status="cancelled" class="btn btn-danger btn-xs update-status"><i class="fa fa-ban"></i></button>';
                      } else {
                        echo'<button type="button" class="btn btn-warning btn-xs access-failed">Aksi</button>';
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

<div class="modal fade" id="modalAdd" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <form class="form validate add-penugasan">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Tambah Penugasan</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nama Staff</label>
            <select class="form-control" name="employees_id" required>
              <option value="">- Pilih Staff -</option>';
              $query_employees="SELECT id,employees_code,employees_name FROM employees ORDER BY employees_name ASC";
              $result_employees = $connection->query($query_employees);
              while($employee = $result_employees->fetch_assoc()) {
                echo'<option value="'.$employee['id'].'">'.$employee['employees_name'].' - '.$employee['employees_code'].'</option>';
              }
              echo'
            </select>
          </div>
          <div class="form-group">
            <label>Pemberi Tugas</label>
            <select class="form-control" name="assignment_signer_id" required>
              <option value="">- Pilih Pemberi Tugas -</option>
              '.$management_options.'
            </select>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label>Mulai Tugas</label>
                <input type="date" class="form-control" name="assignment_start" value="'.$date.'" required>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Selesai Tugas</label>
                <input type="date" class="form-control" name="assignment_end" value="'.$date.'" required>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Lokasi/Tujuan Tugas</label>
            <input type="text" class="form-control" name="assignment_location" required>
          </div>
          <div class="form-group">
            <label>Keterangan Tugas</label>
            <textarea class="form-control" name="assignment_description" rows="4" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary pull-left"><i class="fa fa-check"></i> Simpan</button>
          <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-remove"></i> Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEdit" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <form class="form validate update-penugasan">
        <input type="hidden" name="assignment_id" id="edit-assignment-id" required>
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Edit Penugasan</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nama Staff</label>
            <select class="form-control" name="employees_id" id="edit-employees-id" required>
              <option value="">- Pilih Staff -</option>';
              $query_employees="SELECT id,employees_code,employees_name FROM employees ORDER BY employees_name ASC";
              $result_employees = $connection->query($query_employees);
              while($employee = $result_employees->fetch_assoc()) {
                echo'<option value="'.$employee['id'].'">'.$employee['employees_name'].' - '.$employee['employees_code'].'</option>';
              }
              echo'
            </select>
          </div>
          <div class="form-group">
            <label>Pemberi Tugas</label>
            <select class="form-control" name="assignment_signer_id" id="edit-assignment-signer-id" required>
              <option value="">- Pilih Pemberi Tugas -</option>
              '.$management_options.'
            </select>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label>Mulai Tugas</label>
                <input type="date" class="form-control" name="assignment_start" id="edit-assignment-start" required>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Selesai Tugas</label>
                <input type="date" class="form-control" name="assignment_end" id="edit-assignment-end" required>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Lokasi/Tujuan Tugas</label>
            <input type="text" class="form-control" name="assignment_location" id="edit-assignment-location" required>
          </div>
          <div class="form-group">
            <label>Keterangan Tugas</label>
            <textarea class="form-control" name="assignment_description" id="edit-assignment-description" rows="4" required></textarea>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select class="form-control" name="assignment_status" id="edit-assignment-status" required>
              <option value="active">Aktif</option>
              <option value="completed">Selesai</option>
              <option value="cancelled">Dibatalkan</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary pull-left"><i class="fa fa-check"></i> Simpan</button>
          <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-remove"></i> Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalExtend" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form class="form extend-penugasan">
        <input type="hidden" name="assignment_id" id="extend-assignment-id" required>
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Perpanjang Tugas</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Tanggal Selesai Baru</label>
            <input type="date" class="form-control" name="assignment_end" id="extend-assignment-end" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary pull-left"><i class="fa fa-check"></i> Simpan</button>
          <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-remove"></i> Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>
</div>';
} ?>
