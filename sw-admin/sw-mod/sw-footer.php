<?php if(empty($connection)){
	header('location:./404');
} else {

$mod = "home";$mod = htmlentities(@$_GET['mod']);
// Get number
function get_numbers() {
  for ($i = 1; $i <= 500; $i++) {yield $i;}
}
$result = get_numbers();
function convert($size){
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}
echo'
  <footer class="main-footer">
    <div class="pull-right hidden-xs"></div>
  </footer>
</div>
<!-- wrapper -->
<script src="./sw-assets/js/jquery-2.2.3.min.js?v='.filemtime(__DIR__ . '/../sw-assets/js/jquery-2.2.3.min.js').'"></script>
<script src="./sw-assets/js/jquery-ui.min.js?v='.filemtime(__DIR__ . '/../sw-assets/js/jquery-ui.min.js').'"></script>
<script src="./sw-assets/js/bootstrap.min.js?v='.filemtime(__DIR__ . '/../sw-assets/js/bootstrap.min.js').'"></script>
<script src="./sw-assets/js/jquery.slimscroll.min.js?v='.filemtime(__DIR__ . '/../sw-assets/js/jquery.slimscroll.min.js').'"></script>
<script src="./sw-assets/js/adminlte.js?v='.filemtime(__DIR__ . '/../sw-assets/js/adminlte.js').'"></script>
<script src="./sw-assets/js/app.js?v='.filemtime(__DIR__ . '/../sw-assets/js/app.js').'"></script>
<script src="./sw-assets/js/demo.js?v='.filemtime(__DIR__ . '/../sw-assets/js/demo.js').'"></script>
<script src="./sw-assets/js/sweetalert.min.js?v='.filemtime(__DIR__ . '/../sw-assets/js/sweetalert.min.js').'"></script>
<script src="plugins/chart.js/Chart.min.js"></script>
<script src="./sw-assets/js/simple-lightbox.min.js?v='.filemtime(__DIR__ . '/../sw-assets/js/simple-lightbox.min.js').'"></script>
<script src="./sw-assets/js/validasi/jquery.validate.js?v='.filemtime(__DIR__ . '/../sw-assets/js/validasi/jquery.validate.js').'"></script>
<script src="./sw-assets/js/validasi/messages_id.js?v='.filemtime(__DIR__ . '/../sw-assets/js/validasi/messages_id.js').'"></script>';
if($mod =='shift'){echo'
<script src="./sw-assets/plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="./sw-assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>';
}

if($mod=='karyawan' OR $mod =='jabatan' OR $mod=='shift' OR $mod=='lokasi' OR $mod=='libur' OR $mod=='user' OR $mod=='absensi' OR $mod=='cuty' OR $mod=='penugasan'){
echo'
<link rel="stylesheet" href="./sw-assets/plugins/datatables/dataTables.bootstrap.css">
<script src="./sw-assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="./sw-assets/plugins/datatables/dataTables.bootstrap.min.js"></script>';
}
if($mod=='absensi'){
echo'
<script src="../sw-mod/sw-assets/js/plugins/magnific-popup/jquery.magnific-popup.min.js"></script>';
}
if($mod=='lokasi'){
echo'
<script src="./sw-assets/plugins/leatfet/leaflet.js?v='.filemtime(__DIR__ . '/../sw-assets/plugins/leatfet/leaflet.js').'"></script>
<script>
  if (typeof window.L === "undefined") {
    document.write(\'<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"><\\/script>\');
  }
</script>';
}

if(file_exists('sw-mod/'.$mod.'/scripts.js')){
echo'
  <script src="sw-mod/'.$mod.'/scripts.js?v='.filemtime('sw-mod/'.$mod.'/scripts.js').'"></script>';
}
echo'
  <script type="text/javascript">
  	$(document).ready(function() {
  		$(".validate").validate();
  	});
    
    $(document).ready(function() {
      $(".validate2").validate();
    });
    $(document).on("click", ".access-failed", function(){ 
      swal({title:"Error!", text: "Anda tidak memiliki hak akses!", icon:"error",timer:2000,});  
    });

    function initAdminPasswordToggle(scope) {
      var $scope = scope ? $(scope) : $(document);
      $scope.find("input[type=password]").each(function () {
        var $input = $(this);
        if ($input.data("password-toggle-ready")) {
          return;
        }
        var $wrap = $input.parent();
        if (!$wrap.hasClass("password-toggle-wrap")) {
          $input.wrap("<div class=\"password-toggle-wrap\"></div>");
          $wrap = $input.parent();
        }
        $input.addClass("password-toggle-input");
        $input.data("password-toggle-ready", true);
        $input.after("<button type=\"button\" class=\"password-toggle-btn\" aria-label=\"Lihat password\"><i class=\"fa fa-eye\"></i></button>");
      });
    }

    initAdminPasswordToggle(document);
    $(document).ajaxComplete(function () {
      initAdminPasswordToggle(document);
    });
    $(document).on("click", ".password-toggle-btn", function () {
      var $button = $(this);
      var $input = $button.siblings("input").first();
      var isPassword = $input.attr("type") === "password";
      $input.attr("type", isPassword ? "text" : "password");
      $button.attr("aria-label", isPassword ? "Sembunyikan password" : "Lihat password");
      $button.html(isPassword ? "<i class=\"fa fa-eye-slash\"></i>" : "<i class=\"fa fa-eye\"></i>");
    });
  </script>';?>
  <!-- </body></html> -->
  </body>
</html>
<?PHP }?>
