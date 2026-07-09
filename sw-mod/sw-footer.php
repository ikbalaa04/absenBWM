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
if($mod =='history' OR $mod=='cuty' OR $mod=='overtime'){
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
if($mod =='overtime'){
echo'
<script>
function loadOvertime(){
  $(".loaddataovertime").html("<div class=\"text-center p-3 text-muted\">Memuat data...</div>");
  $.post(window.swBaseUrl+"action/sw-proses.php?action=overtime", {}, function(data){
    $(".loaddataovertime").html(data);
    refreshOvertimeTimers();
  });
}
function formatOvertimeSeconds(seconds){
  seconds = Math.max(0, parseInt(seconds || 0, 10));
  var h = Math.floor(seconds / 3600);
  var m = Math.floor((seconds % 3600) / 60);
  var s = seconds % 60;
  return String(h).padStart(2, "0")+":"+String(m).padStart(2, "0")+":"+String(s).padStart(2, "0");
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
    var note = window.prompt("Catatan hasil pekerjaan lembur:", "");
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
