<?php if(empty($connection)){
  header('location:./404');
} else {

echo'<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <div class="slimScrollDiv">
    <section class="sidebar">
      <!-- Sidebar user panel -->
    
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
        <li class="header">MAIN NAVIGATION</li>';
      if($mod =='home'){echo'<li class="active">'; }else{echo'<li>';}
      echo'<a href="./?mod=home"><i class="fa fa-home"></i><span>Dashboard</span></a></li>';
      
      if($mod =='karyawan' OR $mod=='jabatan' OR $mod=='shift' OR $mod=='lokasi'){echo'<li class="active treeview">'; }else{
      echo'<li class="treeview">';}
      echo'
          <a href="#">
            <i class="fa fa-database"></i> <span>Master Data</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">';
             if($mod =='karyawan'){echo'<li class="active">'; }else{echo'<li>';}
             echo'<a href="./?mod=karyawan"><i class="fa fa-circle-o"></i> Data Karyawan</a></li>';
            if($mod =='jabatan'){echo'<li class="active">'; }else{echo'<li>';}
             echo'<a href="./?mod=jabatan"><i class="fa fa-circle-o"></i> Data Jabatan</a></li>';
            if($mod =='shift'){echo'<li class="active">'; }else{echo'<li>';}
             echo'<a href="./?mod=shift"><i class="fa fa-circle-o"></i> Data Jam Kerja</a></li>';
             if($mod =='lokasi'){echo'<li class="active">'; }else{echo'<li>';}
             echo'<a href="./?mod=lokasi"><i class="fa fa-circle-o"></i> Data Lokasi</a></li>
          </ul>
        </li>';

      if($mod =='cuty'){echo'<li class="active">'; }else{echo'<li>';}
      echo'<a href="./?mod=cuty"><i class="fa fa-calendar" aria-hidden="true"></i> <span>Data Permohonan Izin</span></a></li>';


      if($mod =='absensi'){echo'<li class="active">'; }else{echo'<li>';}
      echo'<a href="./?mod=absensi"><i class="fa fa-list-alt" aria-hidden="true"></i> <span>Data Absensi</span></a></li>';

      if($mod =='setting'){echo'<li class="active">'; }else{echo'<li>';}
      echo'<a href="./?mod=setting"><i class="fa fa-cogs" aria-hidden="true"></i> <span>Pengaturan Web</span></a></li>';

      if($mod =='user'){echo'<li class="active">'; }else{echo'<li>';}
      echo'<a href="./?mod=user"><i class="fa fa-user"></i> <span>Admin</span></a></li>';?>
      <li><a href="./login/logout.php"><i class="fa fa-sign-out text-red"></i>  <span>Keluar</span></a></li>
  <?php echo'
      </ul>
    </section>
  </div>
    <!-- /.sidebar -->
  </aside>';
  }?>
