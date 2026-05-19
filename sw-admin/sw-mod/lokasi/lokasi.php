<?php
if(empty($connection)){
  header('location:../../');
} else {
  include_once 'sw-mod/sw-panel.php';
  require_once'../sw-library/phpqrcode/qrlib.php'; 
echo'
  <div class="content-wrapper">';
    switch(@$_GET['op']){ 
    default:
    $default_latitude = '-6.200000';
    $default_longitude = '106.816666';
    $default_radius = '150';
    $query_default_location = "SELECT latitude,longitude,radius_meter FROM building WHERE latitude IS NOT NULL AND longitude IS NOT NULL ORDER BY building_id ASC LIMIT 1";
    $result_default_location = $connection->query($query_default_location);
    if($result_default_location && $result_default_location->num_rows > 0){
      $row_default_location = $result_default_location->fetch_assoc();
      if(!empty($row_default_location['latitude']) && !empty($row_default_location['longitude'])){
        $default_latitude = $row_default_location['latitude'];
        $default_longitude = $row_default_location['longitude'];
        $default_radius = !empty($row_default_location['radius_meter']) ? $row_default_location['radius_meter'] : '150';
      }
    }
echo'
<style>
  .location-radius-map {
    height: 260px;
    width: 100%;
    border: 1px solid #d2d6de;
    background: #f7f7f7;
  }
</style>
<script>
  window.defaultOfficeLocation = {
    latitude: '.$default_latitude.',
    longitude: '.$default_longitude.',
    radius: '.$default_radius.'
  };
</script>
<section class="content-header">
  <h1>Data<small> Lokasi</small></h1>
    <ol class="breadcrumb">
      <li><a href="./?mod=home"><i class="fa fa-dashboard"></i> Beranda</a></li>
      <li class="active">Data Lokasi</li>
    </ol>
</section>';
echo'
<section class="content">
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title"><b>Data Lokasi</b></h3>
          <div class="box-tools pull-right">';
          if($level_user == 1){
            echo'
            <button type="button" class="btn btn-success btn-flat" data-toggle="modal" data-target="#modalAdd"><i class="fa fa-plus"></i> Tambah Baru</button>';}
          else{
            echo'
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
                <th>ID</th>
                <th>Nama Lokasi</th>
                <th>Alamat</th>
                <th class="text-center">Radius</th>
                <th class="text-center">Jumlah Karyawan</th>
                <th style="width:150px" class="text-center">Aksi</th>
              </tr>
              </thead>
              <tbody>';
              $query="SELECT building_id,name,address,latitude,longitude,radius_meter FROM building order by building_id  DESC";
              $result = $connection->query($query);
              if($result->num_rows > 0){
              $no=0;
             while ($row= $result->fetch_assoc()) {
              $employees_count ="SELECT id FROM employees WHERE building_id='$row[building_id]'";
              $result_count = $connection->query($employees_count);
                $no++;
                echo'
                <tr>
                  <td class="text-center">'.$no.'</td>
                  <td>'.$row['building_id'].'</td>
                  <td>'.$row['name'].'</td>
                  <td>'.$row['address'].'</td>
                  <td class="text-center">'.(!empty($row['radius_meter']) ? $row['radius_meter'] : 150).' m</td>
                  <td class="text-center"><span class="badge bg-yellow">'.$result_count->num_rows.'</span></td>
                  <td class="text-right">
                    <div class="btn-group">';
                      if($level_user == 1){
                      echo'
                      <a href="#modalEdit" class="btn btn-warning btn-xs enable-tooltip" title="Edit" data-toggle="modal"';?> onclick="getElementById('txtid').value='<?PHP echo $row['building_id'];?>';getElementById('txtname').value='<?PHP echo $row['name'];?>';getElementById('txtaddress').value='<?PHP echo $row['address'];?>';getElementById('txtlatitude').value='<?PHP echo $row['latitude'];?>';getElementById('txtlongitude').value='<?PHP echo $row['longitude'];?>';getElementById('txtradius').value='<?PHP echo $row['radius_meter'];?>';"><i class="fa fa-pencil-square-o"></i> Ubah</a>
                      <?php echo'
                      <button data-id="'.epm_encode($row['building_id']).'" class="btn btn-xs btn-danger delete" title="Hapus"><i class="fa fa-trash-o"></i> Hapus</button>';}
                    else{
                    echo'
                      <button type="button" class="btn btn-warning btn-xs access-failed enable-tooltip" title="Edit"><i class="fa fa-pencil-square-o"></i> Ubah</button>
                      <button type="button" class="btn btn-xs btn-danger access-failed" title="Hapus"><i class="fa fa-trash-o"></i> Hapus</button>';
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
      <form class="form validate add-lokasi">
      <div class="modal-body">
        <div class="form-group">
            <label>Nama Lokasi</label>
            <input type="text" class="form-control" name="name" required>
        </div>

        <div class="form-group">
            <label>Alamat Kantor</label>
            <textarea class="form-control address" name="address" rows="3" required></textarea>
        </div>
        <div class="row">
          <div class="col-sm-4">
            <div class="form-group">
              <label>Latitude</label>
              <input type="text" class="form-control location-latitude" name="latitude" placeholder="-6.223456">
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label>Longitude</label>
              <input type="text" class="form-control location-longitude" name="longitude" placeholder="106.812345">
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label>Radius Meter</label>
              <input type="number" class="form-control location-radius" name="radius_meter" value="150" min="10">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Preview Radius Lokasi</label>
          <div class="clearfix" style="margin-bottom:8px">
            <button type="button" class="btn btn-default btn-xs pull-right use-current-location"><i class="fa fa-crosshairs"></i> Gunakan Lokasi Saat Ini</button>
          </div>
          <div id="location-map-add" class="location-radius-map"></div>
          <p class="help-block">Klik peta untuk mengatur titik kantor, lalu isi radius dalam meter.</p>
          <p class="help-block">Lokasi kantor ini menjadi default validasi absensi. Untuk staff lapangan, tambahkan lokasi baru lalu pilih lokasi tersebut di data karyawan.</p>
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
      <form class="form update-lokasi" method="post">
       <input type="hidden" name="id" id="txtid" required value="" readonly>
      <div class="modal-body">
          <div class="form-group">
              <label>Nama Lokasi</label>
              <input type="text" class="form-control" id="txtname" name="name" required>
          </div>

          <div class="form-group">
            <label>Alamat Kantor</label>
            <textarea class="form-control address" id="txtaddress" name="address" rows="3" required></textarea>
        </div>
        <div class="row">
          <div class="col-sm-4">
            <div class="form-group">
              <label>Latitude</label>
              <input type="text" class="form-control location-latitude" id="txtlatitude" name="latitude">
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label>Longitude</label>
              <input type="text" class="form-control location-longitude" id="txtlongitude" name="longitude">
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label>Radius Meter</label>
              <input type="number" class="form-control location-radius" id="txtradius" name="radius_meter" value="150" min="10">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Preview Radius Lokasi</label>
          <div class="clearfix" style="margin-bottom:8px">
            <button type="button" class="btn btn-default btn-xs pull-right use-current-location"><i class="fa fa-crosshairs"></i> Gunakan Lokasi Saat Ini</button>
          </div>
          <div id="location-map-edit" class="location-radius-map"></div>
          <p class="help-block">Klik peta untuk memperbarui titik kantor, lalu isi radius dalam meter.</p>
          <p class="help-block">Lokasi kantor ini menjadi default validasi absensi. Untuk staff lapangan, tambahkan lokasi baru lalu pilih lokasi tersebut di data karyawan.</p>
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
