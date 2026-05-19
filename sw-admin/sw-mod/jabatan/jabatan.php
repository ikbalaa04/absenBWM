<?php 
if(empty($connection)){
  header('location:../../');
} else {
  include_once 'sw-mod/sw-panel.php';
echo'
  <div class="content-wrapper">';
    switch(@$_GET['op']){ 
    default:
    $building_options = '<option value="">Pilih Lokasi</option>';
    $query_building_options = "SELECT building_id,name,address FROM building ORDER BY name ASC";
    $result_building_options = $connection->query($query_building_options);
    if($result_building_options && $result_building_options->num_rows > 0){
      while($row_building_option = $result_building_options->fetch_assoc()){
        $building_label = !empty($row_building_option['name']) ? $row_building_option['name'] : $row_building_option['address'];
        $building_options .= '<option value="'.$row_building_option['building_id'].'">'.$building_label.'</option>';
      }
    }
echo'
<section class="content-header">
  <h1>Data<small> Jabatan</small></h1>
    <ol class="breadcrumb">
      <li><a href="./?mod=home"><i class="fa fa-dashboard"></i> Beranda</a></li>
      <li class="active">Data Jabatan</li>
    </ol>
</section>';
echo'
<section class="content">
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title"><b>Data Jabatan</b></h3>
          <div class="box-tools pull-right">';
          if($level_user==1){
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
              <th class="text-center">ID</th>
              <th>Nama Jabatan</th>
              <th class="text-center">Wajib Lokasi</th>
              <th>Lokasi Validasi</th>
              <th class="text-center">Jumlah Karyawan</th>
              <th style="width:100px">Aksi</th>
            </tr>
            </thead>
            <tbody>';
            $query="SELECT position.position_id,position.position_name,position.require_location,position.building_id,building.name AS building_name FROM position LEFT JOIN building ON position.building_id=building.building_id order by position.position_id DESC";
            $result = $connection->query($query);
            if($result->num_rows > 0){
            $no=0;
           while ($row= $result->fetch_assoc()) {
              $employees_count ="SELECT id FROM employees WHERE position_id='$row[position_id]'";
              $result_count = $connection->query($employees_count);
              $no++;
              echo'
              <tr>
                <td class="text-center">'.$no.'</td>
                <td class="text-center">'.$row['position_id'].'</td>
                <td>'.$row['position_name'].'</td>
                <td class="text-center">'.((int)$row['require_location'] === 1 ? '<span class="label label-success">Ya</span>' : '<span class="label label-default">Tidak</span>').'</td>
                <td>'.((int)$row['require_location'] === 1 ? (!empty($row['building_name']) ? $row['building_name'] : '<span class="text-muted">Belum dipilih</span>') : '<span class="text-muted">Bebas lokasi</span>').'</td>
                <td class="text-center"><span class="badge bg-yellow">'.$result_count->num_rows.'</span></td>
                <td>
                  <div class="btn-group">';
                  if($level_user==1){
                    echo'
                    <a href="#modalEdit" class="btn btn-warning btn-xs enable-tooltip" title="Edit" data-toggle="modal"';?> onclick="getElementById('txtid').value='<?PHP echo $row['position_id'];?>';getElementById('txtnama').value='<?PHP echo $row['position_name'];?>';getElementById('txtrequirelocation').value='<?PHP echo $row['require_location'];?>';getElementById('txtbuildingid').value='<?PHP echo $row['building_id'];?>';"><i class="fa fa-pencil-square-o"></i> Ubah</a>
                <?php echo'
                <button data-id="'.epm_encode($row['position_id']).'" class="btn btn-xs btn-danger delete" title="Hapus"><i class="fa fa-trash-o"></i> Hapus</button>';}
                else {
                  echo'
                    <button type="button" class="btn btn-warning btn-xs access-failed enable-tooltip" title="Edit"><i class="fa fa-pencil-square-o"></i> Ubah</button>
                    <button type="button" class="btn btn-xs btn-danger access-failed" title="Hapus"><i class="fa fa-trash-o"></i> Hapus</button>';
                }echo'
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
      <form id="validate" class="form add-jabatan">
      <div class="modal-body">
        <div class="form-group">
            <label>Nama Jabatan</label>
            <input type="text" class="form-control" name="position_name" id="nama" required>
        </div>
        <div class="form-group">
            <label>Wajib Validasi Lokasi</label>
            <select class="form-control require-location-select" name="require_location">
              <option value="1">Ya, harus dalam radius lokasi penempatan</option>
              <option value="0">Tidak, bebas lokasi</option>
            </select>
        </div>
        <div class="form-group location-setting-group">
            <label>Lokasi Validasi</label>
            <select class="form-control" name="building_id">
              '.$building_options.'
            </select>
        </div>
        <div class="alert alert-info location-rule-panel">
          <i class="fa fa-map-marker"></i> Pilihan lokasi diambil dari menu <b>Data Lokasi</b>. Untuk staff lapangan, tambahkan lokasi baru lalu pilih di dropdown ini.
          <br><a href="./?mod=lokasi">Buka pengaturan lokasi</a>
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
      <form class="form update-jabatan" method="post">
       <input type="hidden" name="id" id="txtid" required value="" readonly>
      <div class="modal-body">
        <div class="form-group">
            <label>Nama</label>
            <input type="text" class="form-control" name="position_name" id="txtnama" required>
        </div>
        <div class="form-group">
            <label>Wajib Validasi Lokasi</label>
            <select class="form-control require-location-select" name="require_location" id="txtrequirelocation">
              <option value="1">Ya, harus dalam radius lokasi penempatan</option>
              <option value="0">Tidak, bebas lokasi</option>
            </select>
        </div>
        <div class="form-group location-setting-group">
            <label>Lokasi Validasi</label>
            <select class="form-control" name="building_id" id="txtbuildingid">
              '.$building_options.'
            </select>
        </div>
        <div class="alert alert-info location-rule-panel">
          <i class="fa fa-map-marker"></i> Pilihan lokasi diambil dari menu <b>Data Lokasi</b>. Untuk staff lapangan, tambahkan lokasi baru lalu pilih di dropdown ini.
          <br><a href="./?mod=lokasi">Buka pengaturan lokasi</a>
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
