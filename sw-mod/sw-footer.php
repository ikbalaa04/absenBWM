<?php if(empty($connection)){
	header('location:./404');
} else {

if(isset($_COOKIE['COOKIES_MEMBER'])){
$active_assignment = assignment_get_active_for_employee($connection, $row_user['id'], $date);
echo'
<div class="appBottomMenu">
        <a href="./?mod=home" class="item">
            <div class="col">
                <ion-icon name="home-outline"></ion-icon>
                <strong>Home</strong>
            </div>
        </a>

        <a href="./?mod=penugasan" class="item">
            <div class="col">
                <ion-icon name="briefcase-outline"></ion-icon>
                <strong>Tugas</strong>
            </div>
        </a>

        <a href="./?mod=cuty" class="item">
            <div class="col">
               <ion-icon name="calendar-outline"></ion-icon>
                <strong>Izin</strong>
            </div>
        </a>

        <a href="./?mod=history" class="item">
            <div class="col">
                 <ion-icon name="document-text-outline"></ion-icon>
                <strong>History</strong>
            </div>
        </a>

        
        <a href="./?mod=profile" class="item">
            <div class="col">
                <ion-icon name="person-outline"></ion-icon>
                <strong>Profil</strong>
            </div>
        </a>
    </div>
<!-- * App Bottom Menu -->';
if($mod =='home' OR $mod=='history'){
echo'
<div class="modal fade action-sheet inset" id="modal-attendance-correction" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="z-index:10000">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajukan Perbaikan Absensi</h5>
        <a href="javascript:void(0);" class="close" style="position:absolute;right:15px;top:10px;" data-dismiss="modal" aria-hidden="true"><ion-icon name="close-outline"></ion-icon></a>
      </div>
      <div class="modal-body">
        <div class="action-sheet-content">
          <form id="form-attendance-correction" autocomplete="off">
            <input type="hidden" name="correction_date" id="correction_date">
            <div class="form-group basic">
              <label class="label">Tanggal</label>
              <input type="text" class="form-control" id="correction_date_label" readonly>
            </div>
            <div class="form-group basic">
              <label class="label">Jenis Absen</label>
              <select class="form-control custom-select" name="correction_type" id="correction_type" required>
                <option value="checkin">Masuk</option>
                <option value="checkout">Pulang</option>
                <option value="checkin_checkout">Masuk & Pulang</option>
                <option value="assignment">Penugasan</option>
              </select>
            </div>
            <div class="form-group basic correction-time-in">
              <label class="label">Jam Masuk / Penugasan</label>
              <input type="time" class="form-control" name="requested_time_in" id="requested_time_in">
            </div>
            <div class="form-group basic correction-time-out">
              <label class="label">Jam Pulang</label>
              <input type="time" class="form-control" name="requested_time_out" id="requested_time_out">
            </div>
            <div class="form-group basic">
              <label class="label">Alasan</label>
              <textarea rows="3" class="form-control" name="reason" required></textarea>
            </div>
            <div class="form-group basic">
              <button type="submit" class="btn btn-danger btn-block btn-lg">Ajukan Perbaikan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>';
}
}
ob_end_flush();
echo'
<footer class="text-muted text-center" style="display:none">
   <p>© 2021 - '.$year.' '.$site_name.'</p>
</footer>
<!-- ///////////// Js Files ////////////////////  -->
<!-- Jquery -->
<script src="'.$base_url.'sw-mod/sw-assets/js/lib/jquery-3.4.1.min.js?v='.filemtime(__DIR__ . '/sw-assets/js/lib/jquery-3.4.1.min.js').'"></script>
<!-- Bootstrap-->
<script src="'.$base_url.'sw-mod/sw-assets/js/lib/popper.min.js?v='.filemtime(__DIR__ . '/sw-assets/js/lib/popper.min.js').'"></script>
<script src="'.$base_url.'sw-mod/sw-assets/js/lib/bootstrap.min.js?v='.filemtime(__DIR__ . '/sw-assets/js/lib/bootstrap.min.js').'"></script>
<!-- Ionicons -->
<script src="https://unpkg.com/ionicons@5.4.0/dist/ionicons.js"></script>
<script src="https://kit.fontawesome.com/0ccb04165b.js" crossorigin="anonymous"></script>
<!-- Base Js File -->
<script src="'.$base_url.'sw-mod/sw-assets/js/base.js?v='.filemtime(__DIR__ . '/sw-assets/js/base.js').'"></script>
<script src="'.$base_url.'sw-mod/sw-assets/js/sweetalert.min.js?v='.filemtime(__DIR__ . '/sw-assets/js/sweetalert.min.js').'"></script>
<script src="'.$base_url.'sw-mod/sw-assets/js/webcamjs/webcam.min.js?v='.filemtime(__DIR__ . '/sw-assets/js/webcamjs/webcam.min.js').'"></script>
<script>window.swBaseUrl = "'.$base_url.'";</script>';
if($mod =='history' OR $mod=='cuty' OR $mod=='overtime' OR $mod=='home'){
echo'
<script src="'.$base_url.'sw-mod/sw-assets/js/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="'.$base_url.'sw-mod/sw-assets/js/plugins/datatables/dataTables.bootstrap.min.js"></script>
<script src="'.$base_url.'sw-mod/sw-assets/js/plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="'.$base_url.'sw-mod/sw-assets/js/plugins/magnific-popup/jquery.magnific-popup.min.js"></script>
<script>
    $(".datepicker").datepicker({
        format: "dd-mm-yyyy",
        "autoclose": true
    }); 
    
</script>';
}
echo'
<script src="'.$base_url.'sw-mod/sw-assets/js/sw-script.js?v='.filemtime(__DIR__ . '/sw-assets/js/sw-script.js').'"></script>';
if($mod =='home' OR $mod=='history'){
echo'
<script>
function refreshCorrectionTimeFields(){
  var type = $("#correction_type").val();
  $(".correction-time-in").toggle(type === "checkin" || type === "checkin_checkout" || type === "assignment");
  $(".correction-time-out").toggle(type === "checkout" || type === "checkin_checkout");
}
$(document).on("change", "#correction_type", refreshCorrectionTimeFields);
$(document).on("click", ".btn-attendance-correction", function(){
  var button = $(this);
  var type = button.data("correction-type") || "checkin_checkout";
  $("#form-attendance-correction")[0].reset();
  $("#correction_date").val(button.data("date"));
  $("#correction_date_label").val(button.data("date-label") || button.data("date"));
  $("#requested_time_in").val(button.data("time-in") && button.data("time-in") !== "00:00:00" ? String(button.data("time-in")).substring(0,5) : "");
  $("#requested_time_out").val(button.data("time-out") && button.data("time-out") !== "00:00:00" ? String(button.data("time-out")).substring(0,5) : "");
  $("#correction_type").val(type);
  if (button.data("record-type") === "assignment") {
    $("#correction_type").val("assignment");
  }
  refreshCorrectionTimeFields();
  $("#modal-attendance-correction").modal("show");
});
$("#form-attendance-correction").on("submit", function(e){
  e.preventDefault();
  $.ajax({
    url: window.swBaseUrl+"action/sw-proses.php?action=add-attendance-correction",
    type: "POST",
    data: $(this).serialize(),
    beforeSend:function(){ $(".loading").show(); },
    success:function(data){
      if(data === "success"){
        $("#modal-attendance-correction").modal("hide");
        swal({title:"Berhasil!", text:"Pengajuan perbaikan absensi dikirim.", icon:"success", timer:1800});
        setTimeout(function(){ location.reload(); }, 1800);
      } else {
        swal({title:"Oops!", text:data, icon:"error", timer:3000});
      }
    },
    complete:function(){ $(".loading").hide(); }
  });
});
</script>';
}
if($mod =='overtime'){
echo'
<script>
function loadOvertime(){
  $(".loaddataovertime-status").html("<div class=\"text-center p-2 text-muted\">Memuat status...</div>");
  $(".loaddataovertime-history").html("<div class=\"text-center p-3 text-muted\">Memuat history...</div>");
  $.post(window.swBaseUrl+"action/sw-proses.php?action=overtime-status", {}, function(data){
    $(".loaddataovertime-status").html(data);
    refreshOvertimeTimers();
  });
  $.post(window.swBaseUrl+"action/sw-proses.php?action=overtime-history", {}, function(data){
    $(".loaddataovertime-history").html(data);
    if ($("#overtimeHistoryTable").length) {
      $("#overtimeHistoryTable").DataTable({
        destroy: true,
        pageLength: 10,
        lengthChange: false,
        ordering: false
      });
    }
  });
}
function formatOvertimeSeconds(seconds){
  seconds = Math.max(0, parseInt(seconds || 0, 10));
  var h = Math.floor(seconds / 3600);
  var m = Math.floor((seconds % 3600) / 60);
  var s = seconds % 60;
  return String(h).padStart(2, "0")+":"+String(m).padStart(2, "0")+":"+String(s).padStart(2, "0");
}
function formatOvertimeMinuteLabel(seconds){
  var minutes = Math.max(0, Math.floor((parseInt(seconds || 0, 10)) / 60));
  var hours = Math.floor(minutes / 60);
  var rest = minutes % 60;
  if(rest === 0){ return hours+" jam"; }
  if(hours <= 0){ return rest+" menit"; }
  return hours+" jam "+rest+" menit";
}
function refreshOvertimeTimers(){
  $(".overtime-item[data-status=\"running\"]").each(function(){
    var item = $(this);
    var startedAt = item.data("started-at");
    var approvedMinutes = parseInt(item.data("approved-minutes") || 0, 10);
    if(!startedAt || approvedMinutes <= 0){ return; }
    var startTime = new Date(String(startedAt).replace(" ", "T")).getTime();
    var maxSeconds = approvedMinutes * 60;
    var elapsed = Math.floor((Date.now() - startTime) / 1000);
    if (elapsed >= maxSeconds) {
      elapsed = maxSeconds;
      var id = item.data("overtime-id");
      $.post(window.swBaseUrl+"action/sw-proses.php?action=stop-overtime", {id:id, result_note:"Selesai otomatis sesuai batas waktu disetujui."}, function(){
        loadOvertime();
      });
    }
    item.find(".overtime-timer").text(formatOvertimeSeconds(elapsed));
    item.find(".overtime-remaining").text(formatOvertimeSeconds(Math.max(0, maxSeconds - elapsed)));
    item.find(".overtime-actual-label").text(formatOvertimeMinuteLabel(elapsed));
    var percent = maxSeconds > 0 ? Math.min(100, Math.max(0, (elapsed / maxSeconds) * 100)) : 0;
    var circumference = 314;
    item.find(".ring-value").css("stroke-dashoffset", circumference - (circumference * percent / 100));
  });
}
$(document).ready(function(){
  loadOvertime();
  setInterval(refreshOvertimeTimers, 1000);
  $("#form-add-overtime").on("submit", function(e){
    e.preventDefault();
    $.ajax({
      url: window.swBaseUrl+"action/sw-proses.php?action=add-overtime",
      type: "POST",
      data: $(this).serialize(),
      success: function(data){
        if(data === "success"){
          swal({title:"Berhasil", text:"Pengajuan lembur berhasil dikirim.", icon:"success", timer:2000});
          $("#form-add-overtime")[0].reset();
          loadOvertime();
        } else {
          swal({title:"Oops!", text:data, icon:"error", timer:3500});
        }
      }
    });
  });
  $(document).on("click", ".btn-overtime-start", function(){
    var id = $(this).data("id");
    $.post(window.swBaseUrl+"action/sw-proses.php?action=start-overtime", {id:id}, function(data){
      if(data === "success"){
        swal({title:"Berhasil", text:"Stopwatch lembur dimulai.", icon:"success", timer:1800});
        loadOvertime();
      } else {
        swal({title:"Oops!", text:data, icon:"error", timer:3000});
      }
    });
  });
  $(document).on("click", ".btn-overtime-stop", function(){
    var id = $(this).data("id");
    swal({
      title:"Selesaikan lembur?",
      text:"Isi catatan hasil pekerjaan lembur.",
      content:{
        element:"textarea",
        attributes:{
          placeholder:"Catatan hasil pekerjaan",
          rows:"4"
        }
      },
      buttons:["Batal","Selesai"]
    }).then(function(note){
      if(note === null){ return; }
      $.post(window.swBaseUrl+"action/sw-proses.php?action=stop-overtime", {id:id, result_note:note}, function(data){
        if(data === "success"){
          swal({title:"Berhasil", text:"Lembur selesai dan waktu aktual tercatat.", icon:"success", timer:2200});
          loadOvertime();
        } else {
          swal({title:"Oops!", text:data, icon:"error", timer:3000});
        }
      });
    });
  });
  $(document).on("click", ".btn-overtime-cancel", function(){
    var id = $(this).data("id");
    swal({title:"Batalkan pengajuan?", text:"Pengajuan lembur yang masih menunggu akan dibatalkan.", icon:"warning", buttons:["Tidak","Ya"]}).then(function(ok){
      if(!ok){ return; }
      $.post(window.swBaseUrl+"action/sw-proses.php?action=cancel-overtime", {id:id}, function(data){
        if(data === "success"){
          loadOvertime();
        } else {
          swal({title:"Oops!", text:data, icon:"error", timer:3000});
        }
      });
    });
  });
});
</script>';
}
echo'
<script>
  $(function(){ $("#loader").hide(); $(".loading").hide(); });
  window.addEventListener("load", function(){ $("#loader").hide(); $(".loading").hide(); });
</script>';
if ($mod =='absent' OR $mod =='penugasan'){?>
<script type="text/javascript">
    var result;
    var geoOptions = {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 0
    };

    $(document).ready(function() {
        result = document.getElementById("latitude");
        requestLocation();
    });
    
    function requestLocation(callback) {
        if (!window.isSecureContext && location.hostname !== "localhost" && location.hostname !== "127.0.0.1") {
            swal({title: 'Oops!', text:'Geolokasi hanya aktif di HTTPS. Pastikan domain dibuka dengan https://', icon: 'error', timer: 3500,});
            return;
        }
        if (!navigator.geolocation) {
            swal({title: 'Oops!', text:'Maaf, browser Anda tidak mendukung geolokasi HTML5.', icon: 'error', timer: 3000,});
            return;
        }
        navigator.geolocation.getCurrentPosition(function(position) {
            successCallback(position);
            if (typeof callback === "function") {
                callback();
            }
        }, errorCallback, geoOptions);
    }

    function successCallback(position) {
       result.innerHTML = position.coords.latitude + "," + position.coords.longitude;
    }

    function errorCallback(error) {
        if(error.code == 1) {
            swal({title: 'Oops!', text:'Izin lokasi ditolak. Aktifkan izin lokasi browser untuk melakukan absensi.', icon: 'error', timer: 3500,});
        } else if(error.code == 2) {
            swal({title: 'Oops!', text:'Jaringan tidak aktif atau layanan penentuan posisi tidak dapat dijangkau.', icon: 'error', timer: 3000,});
        } else {
            swal({title: 'Oops!', text:'Waktu percobaan habis sebelum bisa mendapatkan data lokasi.', icon: 'error', timer: 3000,});
        }
    }
    // start webcame
    Webcam.set({
        width: 590,height: 460,
        image_format: 'jpeg',
        jpeg_quality:80,
    });

        var i = 0;
    var cameras = new Array(); //create empty array to later insert available devices
    if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
        navigator.mediaDevices.enumerateDevices() // get the available devices found in the machine
        .then(function(devices) {
            devices.forEach(function(device) {
                if(device.kind=== "videoinput"){ //filter video devices only
                    cameras[i]= device.deviceId; // save the camera id's in the camera array
                    i++;
                }
            });
        });
    }

    // Set Camera Depan =========
    Webcam.set('constraints',{
        width: 590,
        height: 460,
        image_format: 'jpeg',
        jpeg_quality:80,
        sourceId: cameras[0]
    });

    if (document.querySelector('.webcam-capture')) {
        Webcam.attach('.webcam-capture');
    }
    // preload shutter audio clip
    var shutter = new Audio();
    //shutter.autoplay = true;
    shutter.src = navigator.userAgent.match(/Firefox/) ? './sw-mod/sw-assets/js/webcamjs/shutter.ogg' : './sw-mod/sw-assets/js/webcamjs/shutter.mp3';
    function captureimage(locationType, attendanceAction) {
    var latitude = $('.latitude').html();
        if (!locationType) {
            var locationInput = document.getElementById("attendance_location_type");
            locationType = locationInput ? locationInput.value : "";
        }
        if (!attendanceAction) {
            var actionInput = document.getElementById("attendance_action");
            attendanceAction = actionInput ? actionInput.value : "in";
        }
        if (!latitude) {
            requestLocation(function() { captureimage(locationType, attendanceAction); });
            return;
        }
        // play sound effect
        shutter.play();
        // take snapshot and get image data
        Webcam.snap( function(data_uri) {
            // display results in page
            Webcam.upload(data_uri, window.swBaseUrl+'action/sw-proses.php?action=absent&latitude='+encodeURIComponent(latitude)+'&location_type='+encodeURIComponent(locationType)+'&attendance_action='+encodeURIComponent(attendanceAction)+'',
                function(code,text) {
                    $data       =''+text+'';
                    var results = $data.split("/");
                    $results = results[0];
                    $results2 = results[1];
                    if($results =='success'){
                        swal({title: 'Berhasil!', text:$results2, icon: 'success', timer: 3500,});
                        setTimeout("location.href = './?mod=home';",3600);
                    }else{
                        swal({title: 'Oops!', text:text, icon: 'error', timer: 3500,});
                    }
            });    
        } );
    }
    function captureassignment() {
    var latitude = $('.latitude').html();
        if (!latitude) {
            requestLocation(captureassignment);
            return;
        }
        shutter.play();
        Webcam.snap( function(data_uri) {
            Webcam.upload(data_uri, window.swBaseUrl+'action/sw-proses.php?action=assignment-attendance&latitude='+encodeURIComponent(latitude)+'',
                function(code,text) {
                    $data       =''+text+'';
                    var results = $data.split("/");
                    $results = results[0];
                    $results2 = results[1];
                    if($results =='success'){
                        swal({title: 'Berhasil!', text:$results2, icon: 'success', timer: 3500,});
                        setTimeout("location.href = './?mod=home';",3600);
                    }else{
                        swal({title: 'Oops!', text:text, icon: 'error', timer: 3500,});
                    }
            });
        } );
    }
</script>
<?php }?>
  <!-- </body></html> -->
  </body>
</html><?php }?>
