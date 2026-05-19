<?php
session_start();
if(empty($_SESSION['SESSION_USER']) && empty($_SESSION['SESSION_ID'])){
    header('location:../../login/');
 exit;}
else {
require_once'../../../sw-library/sw-config.php';
require_once'../../login/login_session.php';
include('../../../sw-library/sw-function.php'); 

if (!function_exists('ensure_building_address_capacity')) {
  function ensure_building_address_capacity($connection) {
    $column = $connection->query("SHOW COLUMNS FROM building LIKE 'address'");
    if ($column && $column->num_rows > 0) {
      $row = $column->fetch_assoc();
      if (preg_match('/^varchar\((\d+)\)/i', $row['Type'], $matches) && (int)$matches[1] < 255) {
        return $connection->query("ALTER TABLE building MODIFY address TEXT NOT NULL");
      }
    }
    return true;
  }
}

switch (@$_GET['action']){
case 'add':
function acakangkahuruf($panjang){
        $karakter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $string = '';
        for ($i = 0; $i < $panjang; $i++) {
        $pos = rand(0, strlen($karakter)-1);
        $string .= $karakter[$pos];}
        return $string;
    }
$code   =  'SW'.acakangkahuruf(3).'/'.$year.'';

  $error = array();
  
  if (empty($_POST['name'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $name = mysqli_real_escape_string($connection, $_POST['name']);
  }

  if (empty($_POST['address'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $address= mysqli_real_escape_string($connection, $_POST['address']);
  }
  $latitude = !empty($_POST['latitude']) ? mysqli_real_escape_string($connection, $_POST['latitude']) : 'NULL';
  $longitude = !empty($_POST['longitude']) ? mysqli_real_escape_string($connection, $_POST['longitude']) : 'NULL';
  $radius_meter = !empty($_POST['radius_meter']) ? (int)$_POST['radius_meter'] : 150;
  $latitude_sql = $latitude === 'NULL' ? 'NULL' : "'$latitude'";
  $longitude_sql = $longitude === 'NULL' ? 'NULL' : "'$longitude'";

  if (empty($error)) { 
    if (!ensure_building_address_capacity($connection)) {
        echo'Kolom alamat masih terlalu pendek dan gagal diperbarui: '.$connection->error;
        break;
    }

    $add ="INSERT INTO  building (code,name,address,latitude,longitude,radius_meter,building_scanner) values('$code','$name','$address',$latitude_sql,$longitude_sql,'$radius_meter','')"; 
    if($connection->query($add) === false) { 
        echo'Data tidak berhasil disimpan: '.$connection->error;
    } else{
        echo'success';
    }}
    else{           
        echo'Bidang inputan masih ada yang kosong..!';
    }
break;

/* ------------------------------
    Update
---------------------------------*/
case 'update':
 $error = array();
   if (empty($_POST['id'])) {
      $error[] = 'ID tidak boleh kosong';
    } else {
      $id = mysqli_real_escape_string($connection, $_POST['id']);
  }

  if (empty($_POST['name'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $name= mysqli_real_escape_string($connection, $_POST['name']);
  }

  if (empty($_POST['address'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $address= mysqli_real_escape_string($connection, $_POST['address']);
  }
  $latitude = !empty($_POST['latitude']) ? mysqli_real_escape_string($connection, $_POST['latitude']) : 'NULL';
  $longitude = !empty($_POST['longitude']) ? mysqli_real_escape_string($connection, $_POST['longitude']) : 'NULL';
  $radius_meter = !empty($_POST['radius_meter']) ? (int)$_POST['radius_meter'] : 150;
  $latitude_sql = $latitude === 'NULL' ? 'NULL' : "'$latitude'";
  $longitude_sql = $longitude === 'NULL' ? 'NULL' : "'$longitude'";

  if (empty($error)) { 
    if (!ensure_building_address_capacity($connection)) {
        echo'Kolom alamat masih terlalu pendek dan gagal diperbarui: '.$connection->error;
        break;
    }

    $update="UPDATE building SET name='$name',
            address='$address',
            latitude=$latitude_sql,
            longitude=$longitude_sql,
            radius_meter='$radius_meter' WHERE building_id='$id'"; 
    if($connection->query($update) === false) { 
        echo'Data tidak berhasil disimpan: '.$connection->error;
    } else{
        echo'success';
    }}
    else{           
        echo'Bidang inputan tidak boleh ada yang kosong..!';
    }

break;
/* --------------- Delete ------------*/
case 'delete':
  $id       = mysqli_real_escape_string($connection,epm_decode($_POST['id']));
  $query ="SELECT building.building_id,employees.building_id FROM building,employees WHERE building.building_id=employees.building_id AND employees.building_id='$id'";
  $result = $connection->query($query);
  if(!$result->num_rows > 0){
    $deleted  = "DELETE FROM building WHERE building_id='$id'";
    if($connection->query($deleted) === true) {
        echo'success';
      } else { 
        //tidak berhasil
        echo'Data tidak berhasil dihapus.!';
        die($connection->error.__LINE__);
       
    }
  }else{
      echo'Lokasi digunakan, Data tidak dapat dihapus.!';
    }
break;

}

}
