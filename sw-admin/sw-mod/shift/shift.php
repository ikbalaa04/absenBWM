<?php 
if(empty($connection)){
  header('location:../../');
} else {
  $gotoprocess = "sw-mod/$mod/proses.php";
  include_once 'sw-mod/sw-panel.php';
echo'
  <div class="content-wrapper">';
    switch(@$_GET['op']){ 
    default:
echo'
<section class="content-header">
  <h1>Data<small> Shift</small></h1>
    <ol class="breadcrumb">
      <li><a href="./?mod=home"><i class="fa fa-dashboard"></i> Beranda</a></li>
      <li class="active">Data Shift</li>
    </ol>
</section>';
echo'
<section class="content">
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title"><b>Data Shift</b></h3>
          <div class="box-tools pull-right">';
          if($level_user==1){
            echo'
            <button type="button" class="btn btn-success btn-flat" data-toggle="modal" data-target="#modalAdd"><i class="fa fa-plus"></i> Tambah Baru</button>';}
            else{
            echo'<button type="button" class="btn btn-success btn-flat access-failed"><i class="fa fa-plus"></i> Tambah Baru</button>';
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
                <th class="text-center">ID</th>
	                <th>Nama Shift</th>
	                <th>Aturan Kantor</th>
	                <th>Aturan Luar Kantor</th>
	                <th>Minimal Mingguan</th>
	                <th>Absen Pulang</th>
	                <th class="text-center">Jumlah Pegawai</th>
                <th style="width:100px">Aksi</th>
              </tr>
              </thead>
              <tbody>';
	              $query="SELECT shift_id,shift_name,time_in,time_out,min_work_minutes,checkout_required FROM shift order by shift_id DESC";
              $result = $connection->query($query);
              if($result->num_rows > 0){
              $no=0;
             while ($row= $result->fetch_assoc()) {
              $office_rule = attendance_get_shift_rule($connection, $row['shift_id'], 'office');
              $outside_rule = attendance_get_shift_rule($connection, $row['shift_id'], 'outside');
              $has_outside_rule = ($outside_rule['time_in'] != $office_rule['time_in'] || $outside_rule['time_out'] != $office_rule['time_out']);
              $outside_rule_label = $has_outside_rule ? 'Masuk '.$outside_rule['time_in'].'<br>Pulang '.($row['checkout_required'] == 1 ? $outside_rule['time_out'] : '-').'<br><small>Dipakai untuk Remote/Hybrid</small>' : '<span class="text-muted">Tidak diatur</span>';
              $employees_count ="SELECT id FROM employees WHERE shift_id='$row[shift_id]'";
              $result_count = $connection->query($employees_count);
                $no++;
                echo'
                <tr>
                  <td class="text-center">'.$no.'</td>
                  <td class="text-center">'.$row['shift_id'].'</td>
	                  <td>'.$row['shift_name'].'</td>
	                  <td>Masuk '.$office_rule['time_in'].'<br>Pulang '.($row['checkout_required'] == 1 ? $office_rule['time_out'] : '-').'</td>
	                  <td>'.$outside_rule_label.'</td>
	                  <td>'.$row['min_work_minutes'].' menit</td>
	                  <td>'.($row['checkout_required'] == 1 ? 'Wajib' : 'Tidak wajib').'</td>
                  <td class="text-center"><span class="badge bg-yellow">'.$result_count->num_rows.'</span></td>
                  <td>
                    <div class="btn-group">';
                    if($level_user==1){
                    echo'
		                      <a href="#modalEdit" class="btn btn-warning btn-xs enable-tooltip" title="Edit" data-toggle="modal"';?> onclick="getElementById('txtid').value='<?PHP echo $row['shift_id'];?>';getElementById('txtname').value='<?PHP echo $row['shift_name'];?>';getElementById('txtin').value='<?PHP echo $office_rule['time_in'];?>';getElementById('txtout').value='<?PHP echo $office_rule['time_out'];?>';getElementById('txtmin').value='<?PHP echo $row['min_work_minutes'];?>';getElementById('txtoutsidein').value='<?PHP echo $outside_rule['time_in'];?>';getElementById('txtoutsideout').value='<?PHP echo $outside_rule['time_out'];?>';getElementById('txtcheckout').checked=<?PHP echo $row['checkout_required'] == 1 ? 'true' : 'false';?>;setEditOutsideRule(<?PHP echo ($outside_rule['time_in'] != $office_rule['time_in'] || $outside_rule['time_out'] != $office_rule['time_out']) ? 'true' : 'false';?>);"><i class="fa fa-pencil-square-o"></i> Ubah</a>
                    <?php echo'
                    <buton data-id="'.epm_encode($row['shift_id']).'" class="btn btn-xs btn-danger delete" title="Hapus"><i class="fa fa-trash-o"></i> Hapus</button>';}
                  else{
                echo'
                      <button type="button" class="btn btn-warning btn-xs access-failed enable-tooltip" title="Edit"><i class="fa fa-pencil-square-o"></i> Ubah</button>
                      <buton type="button" class="btn btn-xs btn-danger access-failed" title="Hapus"><i class="fa fa-trash-o"></i> Hapus</button>';
                  }
                  echo'
                  </div>
                  </td>
                </tr>';}}
              echo'
              </tbody>
            </table>
        </div>
      </div>
    </div>
  </div> 
</section>

<!-- Add -->
<div class="modal fade" id="modalAdd" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
    
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Tambah Baru</h4>
      </div>
      <form class="form validate add-shift">
      <div class="modal-body">
        <div class="form-group">
            <label>Nama Shift</label>
            <input type="text" class="form-control" name="shift_name" required>
        </div>

        <div class="form-group">
            <label>Waktu Masuk Kantor</label>
            <div class="input-group">
              <input type="text" name="time_in" class="form-control timepicker" data-date-format="HH:mm:ss" value="07:30:00" required>
              <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
              </div>
            </div>
        </div>

	        <div class="form-group">
	            <label>Waktu Pulang Kantor</label>
            <div class="input-group">
	              <input type="text" name="time_out" class="form-control timepicker">
              <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
              </div>
            </div>
	        </div>

	        <div class="form-group">
	            <label>Minimal Jam Kerja Mingguan (menit)</label>
	            <input type="number" name="min_work_minutes" class="form-control" min="0" value="0">
	            <p class="help-block">Contoh: 40 jam per minggu = 2400 menit.</p>
	        </div>

	        <div class="checkbox">
	            <label>
	                <input type="checkbox" name="use_outside_rule" id="add_use_outside_rule" value="1" onchange="$(&quot;.add-outside-rule&quot;).toggle(this.checked)"> Gunakan aturan luar kantor
	            </label>
	            <p class="help-block">Centang hanya jika shift ini dipakai karyawan Remote/Hybrid dengan jam luar kantor yang berbeda.</p>
	        </div>

	        <div class="form-group add-outside-rule" style="display:none">
	            <label>Waktu Masuk Luar Kantor</label>
	            <p class="help-block">Dipakai hanya untuk karyawan Remote atau Hybrid saat memilih absen luar kantor. Untuk Full Kantor, aturan ini diabaikan.</p>
            <div class="input-group">
	              <input type="text" name="outside_time_in" class="form-control timepicker" value="07:30:00">
              <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
              </div>
            </div>
	        </div>

	        <div class="form-group add-outside-rule" style="display:none">
	            <label>Waktu Pulang Luar Kantor</label>
            <div class="input-group">
	              <input type="text" name="outside_time_out" class="form-control timepicker">
              <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
              </div>
            </div>
	        </div>

	        <div class="checkbox">
	            <label>
	                <input type="checkbox" name="checkout_required" value="1" checked> Wajib absen pulang
	            </label>
	            <p class="help-block">Matikan untuk shift WFH atau staff lapangan yang hanya absen satu kali per hari.</p>
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

<!-- MODAL EDIT -->
<div class="modal fade" id="modalEdit" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Update Data</h4>
      </div>
      <form class="form update-shift" method="post">
       <input type="hidden" name="id" id="txtid" required" value="" readonly>
      <div class="modal-body">
          <div class="form-group">
              <label>Nama Shift</label>
              <input type="text" class="form-control" id="txtname" name="shift_name" required>
          </div>

	          <div class="form-group">
	              <label>Waktu Masuk Kantor</label>
	              <div class="input-group">
	                <input type="text" name="time_in" id="txtin" class="form-control timepicker" data-date-format="HH:mm:ss" value="" required>
	                <div class="input-group-addon">
	                  <i class="fa fa-clock-o"></i>
	                </div>
	              </div>
	          </div>

	          <div class="form-group">
	              <label>Waktu Pulang Kantor</label>
	              <div class="input-group">
	                <input type="text" name="time_out" id="txtout" class="form-control timepicker">
	                <div class="input-group-addon">
	                  <i class="fa fa-clock-o"></i>
	                </div>
	              </div>
	          </div>

	          <div class="form-group">
	              <label>Minimal Jam Kerja Mingguan (menit)</label>
	              <input type="number" name="min_work_minutes" id="txtmin" class="form-control" min="0" value="0">
	              <p class="help-block">Contoh: 40 jam per minggu = 2400 menit.</p>
	          </div>

	          <div class="checkbox">
	              <label>
	                  <input type="checkbox" name="use_outside_rule" id="edit_use_outside_rule" value="1" onchange="$(&quot;.edit-outside-rule&quot;).toggle(this.checked)"> Gunakan aturan luar kantor
	              </label>
	              <p class="help-block">Centang hanya jika shift ini dipakai karyawan Remote/Hybrid dengan jam luar kantor yang berbeda.</p>
	          </div>

	          <div class="form-group edit-outside-rule" style="display:none">
	              <label>Waktu Masuk Luar Kantor</label>
	              <p class="help-block">Dipakai hanya untuk karyawan Remote atau Hybrid saat memilih absen luar kantor. Untuk Full Kantor, aturan ini diabaikan.</p>
	              <div class="input-group">
	                <input type="text" name="outside_time_in" id="txtoutsidein" class="form-control timepicker" value="">
	                <div class="input-group-addon">
	                  <i class="fa fa-clock-o"></i>
	                </div>
	              </div>
	          </div>

	          <div class="form-group edit-outside-rule" style="display:none">
	              <label>Waktu Pulang Luar Kantor</label>
	              <div class="input-group">
	                <input type="text" name="outside_time_out" id="txtoutsideout" class="form-control timepicker">
	                <div class="input-group-addon">
	                  <i class="fa fa-clock-o"></i>
	                </div>
	              </div>
	          </div>

	          <div class="checkbox">
	              <label>
	                  <input type="checkbox" name="checkout_required" id="txtcheckout" value="1"> Wajib absen pulang
	              </label>
	              <p class="help-block">Matikan untuk shift WFH atau staff lapangan yang hanya absen satu kali per hari.</p>
	          </div>
	      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary pull-left"><i class="fa fa-check"></i> Simpan</button>
        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-remove"></i> Batal</button>
      </div>
    </form>
    </div>
  </div>
</div>';
break;
}?>

</div>
<?php }?>
