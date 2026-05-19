<?php 
if ($mod ==''){
    header('location:../404');
    echo'kosong';
}else{
    include_once 'sw-mod/sw-header.php';
if(!isset($_COOKIE['COOKIES_MEMBER']) && !isset($_COOKIE['COOKIES_COOKIES'])){
        setcookie('COOKIES_MEMBER', '', 0, '/');
        setcookie('COOKIES_COOKIES', '', 0, '/');
        // Login tidak ditemukan
        setcookie("COOKIES_MEMBER", "", time()-$expired_cookie);
        setcookie("COOKIES_COOKIES", "", time()-$expired_cookie);
        session_destroy();
        header("location:./");
}else{
  echo'<!-- App Capsule -->
    <div id="appCapsule">
    <div class="section mt-2">
    <div class="card">
    <div class="card-body">
        <div class="row text-center">
        <div class="col-sm-4 col-md-4">
            <div class="form-group basic">
                <div class="input-wrapper">
                    <div class="input-group">
                        <input type="text" class="form-control datepicker start_date" name="start_date" placeholder="Tanggal Awal" required>
                        <div class="input-group-addon">
                            <ion-icon name="calendar-outline"></ion-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4  col-md-4">
            <div class="form-group basic">
                <div class="input-wrapper">
                    <div class="input-group">
                        <input type="text" name="end_date" class="form-control datepicker end_date" value="'.tanggal_ind($date).'">
                        <div class="input-group-addon">
                            <ion-icon name="calendar-outline"></ion-icon>
                        </div>
                    </div>

                </div>
            </div> 
        </div>
        <div class="col-sm-4 col-md-4 justify-content-between">
           <button type="button" class="btn btn-danger mt-1 btn-sortir-cuty"><ion-icon name="checkmark-outline"></ion-icon>Tampilkan</button>
           <button type="button" class="btn btn-success mt-1 btn-clear-cuty"><ion-icon name="repeat-outline"></ion-icon> Clear</button>
           <button type="button" class="btn btn-warning mt-1" data-toggle="modal" data-target="#modal-add"><ion-icon name="add-circle-outline"></ion-icon> Tambah Izin</button>
           
        </div>

        </div>       
    </div>
    </div>
    </div>

        <div class="section mt-2">
            <div class="section-title">Data Permohonan Izin</div>
            <div class="card">
                <div class="transactions">
                    <div class="loaddatacuty"></div>
                </div>
            </div>
        </div>
    

        <!-- MODAL ADD -->
        <div class="modal fade modalbox" id="modal-add" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Permohonan Izin</h5>
                        <a href="javascript:;" data-dismiss="modal">Close</a>
                    </div>
                    <div class="modal-body">
                        <form id="form-add-cuty" autocomplete="off">
                            <div class="form-group basic">
                                <div class="input-wrapper">
                                    <label class="label">Nama</label>
                                    <input type="text" class="form-control" name="name" value="'.$row_user['employees_name'].'" style="background:#eee" readonly required>
                                    <i class="clear-input"><ion-icon name="close-circle"></ion-icon></i>
                                </div>
                            </div>

                            <div class="form-group basic">
                                <div class="input-wrapper">
                                    <label class="label">Alasan</label>
                                    <select class="form-control cuty-type" name="cuty_type" required>
                                        <option value="cuti">Cuti</option>
                                        <option value="sakit">Sakit</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group basic cuty-date-field">
                                <div class="input-wrapper">
                                    <label class="label">Mulai Cuti</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control datepicker" id="cutystart" name="cuty_start" placeholder="'.tanggal_ind($date).'" value="'.tanggal_ind($date).'" required>
                                            <div class="input-group-addon">
                                                <ion-icon name="calendar-outline"></ion-icon>
                                            </div>
                                        </div>
                                    </div>
                            </div>

                            <div class="form-group basic cuty-date-field">
                                <div class="input-wrapper">
                                    <label class="label">Berakhir Cuti</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control datepicker" id="cutyend" name="cuty_end" placeholder="'.tanggal_ind($date).'" value="" required>
                                            <div class="input-group-addon">
                                                <ion-icon name="calendar-outline"></ion-icon>
                                            </div>
                                        </div>
                                    </div>
                            </div>

                            <div class="form-group basic">
                                <div class="input-wrapper">
                                    <label class="label">Keterangan</label>
                                    <div class="cuty-editor-toolbar">
                                        <button type="button" class="btn btn-sm btn-light" data-command="bold"><strong>B</strong></button>
                                        <button type="button" class="btn btn-sm btn-light" data-command="italic"><em>I</em></button>
                                        <button type="button" class="btn btn-sm btn-light" data-command="insertUnorderedList">List</button>
                                    </div>
                                    <div class="form-control cuty-rich-editor" contenteditable="true"></div>
                                    <textarea class="form-control cuty_description" name="cuty_description" style="display:none" required></textarea>
                                </div>
                            </div>

                            <div class="form-group basic">
                                <button type="submit" class="btn btn-danger btn-block btn-lg mt-2">Simpan</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>


        <!-- UPDATE DATA CUTY  -->
        <div class="modal fade modalbox" id="modal-update" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Permohonan Izin</h5>
                        <a href="javascript:;" data-dismiss="modal">Close</a>
                    </div>
                    <div class="modal-body">
                        <form id="form-update-cuty" autocomplete="off">
                            <input type="hidden" id="city-id" name="cuty_id" value="" readonly required>
                            <div class="form-group basic">
                                <div class="input-wrapper">
                                    <label class="label">Nama</label>
                                    <input type="text" class="form-control" name="name" value="'.$row_user['employees_name'].'" style="background:#eee" readonly required>
                                    <i class="clear-input"><ion-icon name="close-circle"></ion-icon></i>
                                </div>
                            </div>

                            <div class="form-group basic">
                                <div class="input-wrapper">
                                    <label class="label">Alasan</label>
                                    <select class="form-control cuty-type" id="cuty-type" name="cuty_type" required>
                                        <option value="cuti">Cuti</option>
                                        <option value="sakit">Sakit</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group basic cuty-date-field">
                                <div class="input-wrapper">
                                    <label class="label">Mulai Cuti</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control datepicker" id="cuty-start" name="cuty_start" placeholder="'.tanggal_ind($date).'" value="" required>
                                            <div class="input-group-addon">
                                                <ion-icon name="calendar-outline"></ion-icon>
                                            </div>
                                        </div>
                                    </div>
                            </div>

                            <div class="form-group basic cuty-date-field">
                                <div class="input-wrapper">
                                    <label class="label">Berakhir Cuti</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control datepicker" id="cuty-end" name="cuty_end" placeholder="'.tanggal_ind($date).'" value="" required>
                                            <div class="input-group-addon">
                                                <ion-icon name="calendar-outline"></ion-icon>
                                            </div>
                                        </div>
                                    </div>
                            </div>

                            <div class="form-group basic">
                                <div class="input-wrapper">
                                    <label class="label">Keterangan</label>
                                    <div class="cuty-editor-toolbar">
                                        <button type="button" class="btn btn-sm btn-light" data-command="bold"><strong>B</strong></button>
                                        <button type="button" class="btn btn-sm btn-light" data-command="italic"><em>I</em></button>
                                        <button type="button" class="btn btn-sm btn-light" data-command="insertUnorderedList">List</button>
                                    </div>
                                    <div class="form-control cuty-rich-editor" id="cuty-description-editor" contenteditable="true"></div>
                                    <textarea class="form-control cuty_description" id="cuty_description" name="cuty_description" style="display:none" required></textarea>
                                </div>
                            </div>

                            <div class="form-group basic">
                                <button type="submit" class="btn btn-danger btn-block btn-lg mt-2">Simpan</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    <!-- * END UPDATE -->

</div>';

  }
  include_once 'sw-mod/sw-footer.php';
} ?>
