<?php 
if(empty($connection)){
  header('location:../../');
} else {
  $gotoprocess = "sw-mod/$mod/proses.php";
  include_once 'sw-mod/sw-panel.php';
  if (!function_exists('format_shift_minutes')) {
    function format_shift_minutes($minutes) {
      $minutes = max(0, (int)$minutes);
      $hours = floor($minutes / 60);
      $remaining_minutes = $minutes % 60;
      if ($remaining_minutes === 0) {
        return $hours.' jam';
      }
      return $hours.' jam '.$remaining_minutes.' menit';
    }
  }
  if (!function_exists('shift_daily_rule_fields')) {
    function shift_daily_rule_fields($mode) {
      $days = array(1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu');
      $html = '<div class="'.$mode.'-daily-rule" style="display:none">
        <div class="table-responsive">
          <table class="table table-bordered table-condensed">
            <thead>
              <tr>
                <th>Hari</th>
                <th class="text-center" style="width:70px">Wajib</th>
                <th>Masuk Kantor</th>
                <th>Pulang Kantor</th>
                <th>Minimal Harian (menit)</th>
              </tr>
            </thead>
            <tbody>';
      foreach ($days as $day => $label) {
        $id_prefix = $mode === 'add' ? 'adddaily' : 'txtdaily';
        $default_checked = ($mode === 'add' && $day <= 5) ? ' checked' : '';
        $default_time_in = ($mode === 'add' && $day <= 5) ? '07:30:00' : '';
        $html .= '<tr>
          <td>'.$label.'</td>
          <td class="text-center"><input type="checkbox" name="daily_active['.$day.']" id="'.$id_prefix.'active'.$day.'" value="1"'.$default_checked.'></td>
          <td><input type="text" name="daily_time_in['.$day.']" id="'.$id_prefix.'in'.$day.'" class="form-control timepicker" value="'.$default_time_in.'"></td>
          <td><input type="text" name="daily_time_out['.$day.']" id="'.$id_prefix.'out'.$day.'" class="form-control timepicker" value=""></td>
          <td><input type="number" name="daily_min_work_minutes['.$day.']" id="'.$id_prefix.'min'.$day.'" class="form-control" min="0" value="0"></td>
        </tr>';
      }
      $html .= '</tbody></table>
        </div>
        <p class="help-block">Hari yang tidak dicentang atau tidak memiliki jam masuk tidak wajib absen dan tidak dihitung alfa. Minimal mingguan tetap memakai field Minimal Jam Kantor Mingguan.</p>
      </div>';
      return $html;
    }
  }
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
            <div class="btn-group">
              <button type="button" class="btn btn-primary btn-flat dropdown-toggle" data-toggle="dropdown"><i class="fa fa-download"></i> Export <span class="caret"></span></button>
              <ul class="dropdown-menu" role="menu">
                <li><a href="sw-mod/shift/proses.php?action=export&type=xls"><i class="fa fa-file-excel-o"></i> Excel</a></li>
                <li><a href="sw-mod/shift/proses.php?action=export&type=pdf" target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a></li>
              </ul>
            </div>
            <button type="button" class="btn btn-success btn-flat" data-toggle="modal" data-target="#modalAdd"><i class="fa fa-plus"></i> Tambah Baru</button>';}
            else{
            echo'
            <button type="button" class="btn btn-primary btn-flat access-failed"><i class="fa fa-download"></i> Export</button>
            <button type="button" class="btn btn-success btn-flat access-failed"><i class="fa fa-plus"></i> Tambah Baru</button>';
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
	                <th>Jam Kerja</th>
	                <th>Absen Pulang</th>
	                <th class="text-center">Jumlah Pegawai</th>
                <th style="width:100px">Aksi</th>
              </tr>
              </thead>
              <tbody>';
	              $query="SELECT shift_id,shift_name,time_in,time_out,min_work_minutes,checkout_required,custom_daily_rules FROM shift order by shift_id DESC";
              $result = $connection->query($query);
              if($result->num_rows > 0){
              $no=0;
             while ($row= $result->fetch_assoc()) {
              $office_rule = attendance_get_shift_rule($connection, $row['shift_id'], 'office');
              $outside_rule = attendance_get_shift_rule($connection, $row['shift_id'], 'outside');
              $has_outside_rule = ($outside_rule['time_in'] != $office_rule['time_in'] || $outside_rule['time_out'] != $office_rule['time_out'] || (int)$outside_rule['weekly_min_minutes'] > 0 || (int)$outside_rule['weekly_limit_minutes'] > 0);
              $outside_rule_label = $has_outside_rule ? 'Masuk '.$outside_rule['time_in'].'<br>Pulang '.($row['checkout_required'] == 1 ? $outside_rule['time_out'] : '-').'<br>Minimal '.format_shift_minutes($outside_rule['weekly_min_minutes']).'/minggu<br>Maksimal '.format_shift_minutes($outside_rule['weekly_limit_minutes']).'/minggu<br><small>Toleransi '.format_shift_minutes($outside_rule['weekly_tolerance_minutes']).'</small>' : '<span class="text-muted">Tidak diatur</span>';
              $daily_rules = attendance_get_shift_daily_rules($connection, $row['shift_id']);
              $daily_rules_json = htmlspecialchars(json_encode($daily_rules), ENT_QUOTES, 'UTF-8');
              if ((int)$row['custom_daily_rules'] === 1) {
                $office_rule_label = '<span class="label label-info">Custom per hari</span><br><small>Menggunakan jam kerja harian</small><br>Minimal '.format_shift_minutes($row['min_work_minutes']).'/minggu';
              } else {
                $office_rule_label = 'Masuk '.$office_rule['time_in'].'<br>Pulang '.($row['checkout_required'] == 1 ? $office_rule['time_out'] : '-').'<br>Minimal '.format_shift_minutes($row['min_work_minutes']).'/minggu';
              }
              $total_weekly_minimum = (int)$row['min_work_minutes'] + ($has_outside_rule ? (int)$outside_rule['weekly_min_minutes'] : 0);
              $weekly_minimum_label = $has_outside_rule ? 'Kantor '.format_shift_minutes($row['min_work_minutes']).'<br>Luar kantor '.format_shift_minutes($outside_rule['weekly_min_minutes']).'<br><small>Total '.format_shift_minutes($total_weekly_minimum).'</small>' : format_shift_minutes($row['min_work_minutes']);
              $employees_count ="SELECT id FROM employees WHERE shift_id='$row[shift_id]'";
              $result_count = $connection->query($employees_count);
                $no++;
                echo'
                <tr>
                  <td class="text-center">'.$no.'</td>
                  <td class="text-center">'.$row['shift_id'].'</td>
	                  <td>'.$row['shift_name'].'</td>
	                  <td>'.$office_rule_label.'</td>
	                  <td>'.$outside_rule_label.'</td>
	                  <td>'.$weekly_minimum_label.'</td>
	                  <td>'.($row['checkout_required'] == 1 ? 'Wajib' : 'Tidak wajib').'</td>
                  <td class="text-center"><span class="badge bg-yellow">'.$result_count->num_rows.'</span></td>
                  <td>
                    <div class="btn-group">';
                    if($level_user==1){
                    echo'
		                      <a href="#modalEdit" class="btn btn-warning btn-xs enable-tooltip" title="Edit" data-toggle="modal"';?> onclick="getElementById('txtid').value='<?PHP echo $row['shift_id'];?>';getElementById('txtname').value='<?PHP echo $row['shift_name'];?>';getElementById('txtin').value='<?PHP echo $office_rule['time_in'];?>';getElementById('txtout').value='<?PHP echo $office_rule['time_out'];?>';getElementById('txtmin').value='<?PHP echo $row['min_work_minutes'];?>';getElementById('txtoutsidein').value='<?PHP echo $outside_rule['time_in'];?>';getElementById('txtoutsideout').value='<?PHP echo $outside_rule['time_out'];?>';getElementById('txtoutsidemin').value='<?PHP echo $outside_rule['weekly_min_minutes'];?>';getElementById('txtoutsidelimit').value='<?PHP echo $outside_rule['weekly_limit_minutes'];?>';getElementById('txtoutsidetolerance').value='<?PHP echo $outside_rule['weekly_tolerance_minutes'];?>';getElementById('txtcheckout').checked=<?PHP echo $row['checkout_required'] == 1 ? 'true' : 'false';?>;setEditOutsideRule(<?PHP echo $has_outside_rule ? 'true' : 'false';?>);setEditDailyRules(<?PHP echo (int)$row['custom_daily_rules'] === 1 ? 'true' : 'false';?>, <?PHP echo $daily_rules_json;?>);"><i class="fa fa-pencil-square-o"></i> Ubah</a>
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

        <div class="form-group add-default-office-rule">
            <label>Waktu Masuk Kantor</label>
            <div class="input-group">
              <input type="text" name="time_in" class="form-control timepicker" data-date-format="HH:mm:ss" value="07:30:00" required>
              <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
              </div>
            </div>
        </div>

	        <div class="form-group add-default-office-rule">
	            <label>Waktu Pulang Kantor</label>
            <div class="input-group">
	              <input type="text" name="time_out" class="form-control timepicker">
              <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
              </div>
            </div>
	        </div>

	        <div class="checkbox">
	            <label>
	                <input type="checkbox" name="custom_daily_rules" id="add_custom_daily_rules" value="1" onchange="toggleCustomDailyRules(&quot;add&quot;, this.checked)"> Custom jam kerja kantor per hari
	            </label>
	            <p class="help-block">Khusus Full Kantor. Jika aktif, jam default di atas diganti oleh aturan harian. Hari kosong tidak wajib absen dan tidak dihitung alfa.</p>
	        </div>
          '.shift_daily_rule_fields('add').'

	        <div class="form-group">
	            <label>Minimal Jam Kantor Mingguan (menit)</label>
	            <input type="number" name="min_work_minutes" id="addmin" class="form-control" min="0" value="0">
	            <p class="help-block">Target minimal jam kerja di kantor per minggu. Jika custom aktif, nilai ini dihitung dari akumulasi jam harian.</p>
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

	        <div class="form-group add-outside-rule" style="display:none">
	            <label>Minimal Jam Luar Kantor Mingguan (menit)</label>
	            <input type="number" name="outside_min_work_minutes" class="form-control" min="0" value="0">
	            <p class="help-block">Target minimal absen luar kantor per minggu. Ini bukan batas maksimal.</p>
	        </div>

	        <div class="form-group add-outside-rule" style="display:none">
	            <label>Maksimal Jam Luar Kantor Mingguan (menit)</label>
	            <input type="number" name="outside_weekly_limit_minutes" class="form-control" min="0" value="0">
	            <p class="help-block">Batas akumulasi absen luar kantor per minggu.</p>
	        </div>

	        <div class="form-group add-outside-rule" style="display:none">
	            <label>Toleransi Luar Kantor Mingguan (menit)</label>
	            <input type="number" name="outside_weekly_tolerance_minutes" class="form-control" min="0" value="30">
	            <p class="help-block">Ruang toleransi di akhir kuota. Ini bukan kuota normal.</p>
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
       <input type="hidden" name="id" id="txtid" required value="" readonly>
      <div class="modal-body">
          <div class="form-group">
              <label>Nama Shift</label>
              <input type="text" class="form-control" id="txtname" name="shift_name" required>
          </div>

	          <div class="form-group edit-default-office-rule">
	              <label>Waktu Masuk Kantor</label>
	              <div class="input-group">
	                <input type="text" name="time_in" id="txtin" class="form-control timepicker" data-date-format="HH:mm:ss" value="" required>
	                <div class="input-group-addon">
	                  <i class="fa fa-clock-o"></i>
	                </div>
	              </div>
	          </div>

	          <div class="form-group edit-default-office-rule">
	              <label>Waktu Pulang Kantor</label>
	              <div class="input-group">
	                <input type="text" name="time_out" id="txtout" class="form-control timepicker">
	                <div class="input-group-addon">
	                  <i class="fa fa-clock-o"></i>
	                </div>
	              </div>
	          </div>

	          <div class="checkbox">
	              <label>
	                  <input type="checkbox" name="custom_daily_rules" id="edit_custom_daily_rules" value="1" onchange="toggleCustomDailyRules(&quot;edit&quot;, this.checked)"> Custom jam kerja kantor per hari
	              </label>
	              <p class="help-block">Khusus Full Kantor. Jika aktif, jam default di atas diganti oleh aturan harian. Hari kosong tidak wajib absen dan tidak dihitung alfa.</p>
	          </div>
            '.shift_daily_rule_fields('edit').'

	          <div class="form-group">
	              <label>Minimal Jam Kantor Mingguan (menit)</label>
	              <input type="number" name="min_work_minutes" id="txtmin" class="form-control" min="0" value="0">
	              <p class="help-block">Target minimal jam kerja di kantor per minggu. Jika custom aktif, nilai ini dihitung dari akumulasi jam harian.</p>
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

	          <div class="form-group edit-outside-rule" style="display:none">
	              <label>Minimal Jam Luar Kantor Mingguan (menit)</label>
	              <input type="number" name="outside_min_work_minutes" id="txtoutsidemin" class="form-control" min="0" value="0">
	              <p class="help-block">Target minimal absen luar kantor per minggu. Ini bukan batas maksimal.</p>
	          </div>

	          <div class="form-group edit-outside-rule" style="display:none">
	              <label>Maksimal Jam Luar Kantor Mingguan (menit)</label>
	              <input type="number" name="outside_weekly_limit_minutes" id="txtoutsidelimit" class="form-control" min="0" value="0">
	              <p class="help-block">Batas akumulasi absen luar kantor per minggu.</p>
	          </div>

	          <div class="form-group edit-outside-rule" style="display:none">
	              <label>Toleransi Luar Kantor Mingguan (menit)</label>
	              <input type="number" name="outside_weekly_tolerance_minutes" id="txtoutsidetolerance" class="form-control" min="0" value="30">
	              <p class="help-block">Ruang toleransi di akhir kuota. Ini bukan kuota normal.</p>
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
<script>
function setEditDailyRules(enabled, rules) {
  var custom = document.getElementById("edit_custom_daily_rules");
  if (custom) {
    custom.checked = !!enabled;
  }
  toggleCustomDailyRules("edit", !!enabled);
  for (var day = 1; day <= 7; day++) {
    var rule = rules && rules[day] ? rules[day] : {};
    var active = document.getElementById("txtdailyactive" + day);
    var timeIn = document.getElementById("txtdailyin" + day);
    var timeOut = document.getElementById("txtdailyout" + day);
    var min = document.getElementById("txtdailymin" + day);
    if (active) active.checked = parseInt(rule.is_active || 0, 10) === 1;
    if (timeIn) timeIn.value = rule.time_in && rule.time_in !== "00:00:00" ? rule.time_in : "";
    if (timeOut) timeOut.value = rule.time_out && rule.time_out !== "00:00:00" ? rule.time_out : "";
    if (min) min.value = rule.min_work_minutes || 0;
    updateDailyMinutes("txtdaily", day);
  }
  updateDailyWeeklyTotal("edit");
}
function toggleCustomDailyRules(mode, enabled) {
  $("." + mode + "-daily-rule").toggle(!!enabled);
  $("." + mode + "-default-office-rule").toggle(!enabled);
  var timeIn = mode === "add" ? document.querySelector("input[name='time_in']") : document.getElementById("txtin");
  var timeOut = mode === "add" ? document.querySelector("input[name='time_out']") : document.getElementById("txtout");
  var min = mode === "add" ? document.getElementById("addmin") : document.getElementById("txtmin");
  if (timeIn) {
    timeIn.disabled = !!enabled;
    timeIn.required = !enabled;
  }
  if (timeOut) {
    timeOut.disabled = !!enabled;
  }
  if (min) {
    min.readOnly = !!enabled;
    min.title = enabled ? "Otomatis dari akumulasi minimal harian" : "";
  }
  if (enabled) {
    updateDailyWeeklyTotal(mode);
  }
}
function parseDailyTime(value) {
  if (!value) return null;
  var parts = value.split(":");
  if (parts.length < 2) return null;
  var hours = parseInt(parts[0], 10);
  var minutes = parseInt(parts[1], 10);
  if (isNaN(hours) || isNaN(minutes)) return null;
  return (hours * 60) + minutes;
}
function updateDailyMinutes(prefix, day) {
  var timeIn = document.getElementById(prefix + "in" + day);
  var timeOut = document.getElementById(prefix + "out" + day);
  var min = document.getElementById(prefix + "min" + day);
  if (!timeIn || !timeOut || !min) return;
  var start = parseDailyTime(timeIn.value);
  var end = parseDailyTime(timeOut.value);
  if (start === null || end === null) return;
  if (end < start) {
    end += 1440;
  }
  min.value = Math.max(0, end - start);
  updateDailyWeeklyTotal(prefix === "adddaily" ? "add" : "edit");
}
function updateDailyWeeklyTotal(mode) {
  var prefix = mode === "add" ? "adddaily" : "txtdaily";
  var total = 0;
  for (var day = 1; day <= 7; day++) {
    var active = document.getElementById(prefix + "active" + day);
    var min = document.getElementById(prefix + "min" + day);
    if (active && active.checked && min) {
      total += parseInt(min.value || "0", 10) || 0;
    }
  }
  var weeklyMin = mode === "add" ? document.getElementById("addmin") : document.getElementById("txtmin");
  if (weeklyMin) {
    weeklyMin.value = total;
  }
}
$(document).on("change keyup", "[id^=adddailyin], [id^=adddailyout], [id^=txtdailyin], [id^=txtdailyout]", function() {
  var match = this.id.match(/^(adddaily|txtdaily)(in|out)([1-7])$/);
  if (match) {
    updateDailyMinutes(match[1], match[3]);
  }
});
$(document).on("change", "[id^=adddailyactive], [id^=txtdailyactive], [id^=adddailymin], [id^=txtdailymin]", function() {
  var match = this.id.match(/^(adddaily|txtdaily)(active|min)([1-7])$/);
  if (match) {
    updateDailyWeeklyTotal(match[1] === "adddaily" ? "add" : "edit");
  }
});
</script>
<?php }?>
