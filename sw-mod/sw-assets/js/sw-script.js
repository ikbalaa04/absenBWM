$(document).ready(function() {
var swBaseUrl = window.swBaseUrl || './';
var swProcessUrl = swBaseUrl + 'action/sw-proses.php';
var swPrintUrl = swBaseUrl + 'action/sw-print.php';
var swAdminLoginUrl = swBaseUrl + 'sw-admin/login/login-proses.php';

function loading(){
    $(".loading").show();
    $(".loading").delay(2000).fadeOut(600);
}

$(document).on('click', '[data-target="#sidebarPanel"]', function(e) {
    e.preventDefault();
    $('#sidebarPanel').modal('show');
});

$(document).on('click', '.logout-link', function(e) {
    e.preventDefault();
    window.location.href = $(this).attr('href') || './?mod=logout';
});

function ensureLoginRoleField() {
    if ($('#form-login').length && $('#role').length === 0) {
        $('#email').attr({
            type: 'text',
            placeholder: 'E-mail atau username Anda'
        });
        $('label[for="email1"], label[for="email"]').first().text('E-mail / Username').attr('for', 'email');
        var roleField = '' +
            '<div class="form-group basic">' +
                '<div class="input-wrapper">' +
                    '<label class="label" for="role">Role</label>' +
                    '<select class="form-control custom-select" id="role" name="role">' +
                        '<option value="user">Staff</option>' +
                        '<option value="admin">Admin</option>' +
                    '</select>' +
                '</div>' +
            '</div>';
        $('#email').closest('.form-group').after(roleField);
        if (new URLSearchParams(window.location.search).get('role') === 'admin') {
            $('#role').val('admin');
        }
    }
}

function syncLoginRole() {
    ensureLoginRoleField();
    var role = $('#role').val() || 'user';
    if (role === 'admin') {
        $('.user-login-links').hide();
    } else {
        $('.user-login-links').show();
    }
}

ensureLoginRoleField();
syncLoginRole();
$(document).on('change', '#role', syncLoginRole);

/* ----------- LOGIN ------------*/
$('#form-login').submit(function (e) {
    e.preventDefault();
    if($('#email').val()=='' || $('#password').val()==''){
         swal({title:'Oops!', text: 'Harap bidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
        return false;
    }
    else{
        var role = $('#role').val() || 'user';
        loading();
        if (role === 'admin') {
            $.ajax({
                url: swAdminLoginUrl,
                type: "POST",
                dataType: "json",
                data: {
                    username: $('#email').val(),
                    password: $('#password').val()
                },
                beforeSend: function () {
                    loading();
                },
                success: function (json) {
                    if (json.response && json.response.error == "1") {
                        swal({title: 'Berhasil!', text: 'Selamat datang admin.!', icon: 'success', timer: 1500,});
                        setTimeout(function(){ location.href = swBaseUrl + 'sw-admin/'; }, 2000);
                    } else {
                        swal({title: 'Oops!', text: 'Periksa username dan password admin Anda.', icon: 'error', timer: 1500,});
                    }
                },
                error: function () {
                    swal({title: 'Oops!', text: 'Login admin tidak dapat diproses.', icon: 'error', timer: 1500,});
                },
                complete: function () {
                    $(".loading").hide();
                }
            });
            return false;
        }
        $.ajax({
            url: swProcessUrl+"?action=login",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            cache: false,
            async: false,
            beforeSend: function () {
              loading();
            },
            success: function (data) {
                if (data == 'success') {
                    swal({title: 'Berhasil!', text: 'Selamat datang.!', icon: 'success', timer: 1500,});
                    //setTimeout(function(){location.reload(); }, 1500);
                     setTimeout("location.href = './?mod=home';",2000);
                } else {
                    swal({title: 'Oops!', text: data, icon: 'error', timer: 1500,});
                }

            },
            complete: function () {
                $(".loading").hide();
            },
        });
    }
});


/* ----------- REGISTRASI ------------*/
$('#form-registrasi').submit(function (e) {
    e.preventDefault();
    if($('#email').val()=='' && $('#password').val()=='' && $('#position_id').val()=='' && $('#shift_id').val()=='' && $('#building').val()==''){
         swal({title:'Oops!', text: 'Harap bidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
        return false;
        loading();
    }
    else{
        loading();
        $.ajax({
            url: swProcessUrl+"?action=registrasi",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            cache: false,
            async: false,
            beforeSend: function () {
              loading();
            },
            success: function (data) {
                if (data == 'success') {
                    swal({title: 'Berhasil!', text: 'Selamat Anda berhasil mendaftar!', icon: 'success', timer: 2500,});
                    setTimeout("location.href = './?mod=home';",2600);
                } else {
                    swal({title: 'Oops!', text: data, icon: 'error', timer: 1500,});
                }

            },
            complete: function () {
                $(".loading").hide();
            },
        });
    }
});


/* ----------- FORGOT ------------*/
$('#form-forgot').submit(function (e) {
    e.preventDefault();
    if($('#email').val()==''){
         swal({title:'Oops!', text: 'Harap bidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
        return false;
        loading();
    }
    else{
        loading();
        $.ajax({
            url: swProcessUrl+"?action=forgot",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            cache: false,
            async: false,
            beforeSend: function () {
              loading();
            },
            success: function (data) {
                if (data == 'success') {
                    swal({title: 'Berhasil!', text: 'Password baru berhasil disetel ulang, silahkan cek email masuk/spam!', icon: 'success', timer: 2000,});
                    //setTimeout(function(){ location.reload(); }, 1500);
                    setTimeout("location.href = './?mod=home';",3000);
                } else {
                    swal({title: 'Oops!', text: data, icon: 'error', timer: 1500,});
                }

            },
            complete: function () {
                $(".loading").hide();
            },
        });
    }
});


/* ---------- UPDATE PROFILE -----------------*/
$('#update-profile').submit(function (e) {
    e.preventDefault();
    if($('#name').val()==''){
         swal({title:'Oops!', text: 'Harap bidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
        return false;
        loading();
    }
    else{
        loading();
        $.ajax({
            url: swProcessUrl+"?action=profile",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            cache: false,
            async: false,
            beforeSend: function () {
              loading();
            },
            success: function (data) {
                if (data == 'success') {
                    swal({title: 'Berhasil!', text: 'Profil berhasil di perbaharui!', icon: 'success', timer: 2000,});
                    setTimeout(function(){ location.reload(); }, 2500);
                    $(".btn-profile").text('Simpan');
                } else {
                    swal({title: 'Oops!', text: data, icon: 'error', timer: 2000,});
                    $(".btn-profile").text('Simpan');
                }

            },
            complete: function () {
                $(".loading").hide();
            },
        });
    }
});

$(document).on('click', '.telegram-connect', function () {
    loading();
    $.ajax({
        url: swProcessUrl+"?action=telegram-connect",
        type: "POST",
        success: function (data) {
            if (data == 'success') {
                swal({title: 'Berhasil!', text: 'Kode koneksi Telegram dibuat.', icon: 'success', timer: 1500,});
                setTimeout(function(){ location.reload(); }, 1500);
            } else {
                swal({title: 'Oops!', text: data, icon: 'error', timer: 2000,});
            }
        },
        complete: function () {
            $(".loading").hide();
        }
    });
});

$(document).on('click', '.telegram-test', function () {
    loading();
    $.ajax({
        url: swProcessUrl+"?action=telegram-test",
        type: "POST",
        success: function (data) {
            if (data == 'success') {
                swal({title: 'Berhasil!', text: 'Test Telegram berhasil dikirim.', icon: 'success', timer: 1500,});
            } else {
                swal({title: 'Oops!', text: data, icon: 'error', timer: 2000,});
            }
        },
        complete: function () {
            $(".loading").hide();
        }
    });
});

$(document).on('click', '.telegram-disconnect', function () {
    swal({
        title: "Putuskan Telegram?",
        text: "Notifikasi Telegram ke akun ini akan berhenti.",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then(function(willDisconnect) {
        if (!willDisconnect) {
            return;
        }
        loading();
        $.ajax({
            url: swProcessUrl+"?action=telegram-disconnect",
            type: "POST",
            success: function (data) {
                if (data == 'success') {
                    swal({title: 'Berhasil!', text: 'Telegram berhasil diputuskan.', icon: 'success', timer: 1500,});
                    setTimeout(function(){ location.reload(); }, 1500);
                } else {
                    swal({title: 'Oops!', text: data, icon: 'error', timer: 2000,});
                }
            },
            complete: function () {
                $(".loading").hide();
            }
        });
    });
});



/* ---------- UPDATE PASSWORD-----------------*/
$('#update-password').submit(function (e) {
    e.preventDefault();
    if($('#employees_password').val()==''){
         swal({title:'Oops!', text: 'Harap bidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
        return false;
        loading();
    }
    else{
        loading();
        $.ajax({
            url: swProcessUrl+"?action=update-password",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            cache: false,
            async: false,
            beforeSend: function () {
              loading();
            },
            success: function (data) {
                if (data == 'success') {
                    swal({title: 'Berhasil!', text: 'Password berhasil di perbaharui!', icon: 'success', timer: 2000,});
                    setTimeout(function(){ location.reload(); }, 2500);

                } else {
                    swal({title: 'Oops!', text: data, icon: 'error', timer: 2000,});

                }
            },

            complete: function () {
                 $(".loading").hide();
            },
        });
    }
});

/* --------- UPDATE PHOTO PROFILE ---------------*/
 $(document).on('change','#avatar',function(){
        var file_data = $('#avatar').prop('files')[0];
        if (!file_data) {
          return false;
        }
        var image_name = file_data.name;
        var image_extension = image_name.split('.').pop().toLowerCase();

        if(jQuery.inArray(image_extension,['gif','jpg','jpeg','png']) == -1){
          swal({title: 'Oops!', text: 'File yang di unggah tidak sesuai dengan format, File harus jpg, jpeg, gif, png.!', icon: 'error', timer: 2000,});
          $('#avatar').val('');
          return false;
        }

        if(file_data.size > 5000000){
          swal({title: 'Oops!', text: 'File terlalu besar maksimal 5MB.!', icon: 'error', timer: 2000,});
          $('#avatar').val('');
          return false;
        }

        var form_data = new FormData();
        form_data.append("file",file_data);
        $.ajax({
          url: swProcessUrl+'?action=update-photo',
          method:'POST',
          data:form_data,
          contentType:false,
          cache:false,
          processData:false,
          beforeSend:function(){
            loading();
          },
          success:function(data){
                if (data == 'success') {
                    swal({title: 'Behasil!', text:'Photo Profil berhasil diperbaharui.!', icon: 'success', timer: 1500,});
                    setTimeout(function(){location.reload(); }, 1600);
                } else {
                    swal({title: 'Oops!', text: data, icon: 'error', timer: 2000,});
                }
          }
        });
});

/* --------- LOAD DATA HISTORY ---------------*/
function applyHistoryPeriod() {
    if (!$('.history_period').length) {
        return;
    }
    var period = $('.history_period').val() || 'last_week';
    var $period = $('.history_period');
    if (period === 'this_week') {
        $('.start_date').val($period.data('this-week-start'));
        $('.end_date').val($period.data('this-week-end'));
    } else if (period === 'prev_week') {
        $('.start_date').val($period.data('prev-week-start'));
        $('.end_date').val($period.data('prev-week-end'));
    } else if (period === 'last_week') {
        $('.start_date').val($period.data('last-week-start'));
        $('.end_date').val($period.data('last-week-end'));
    }
    $('.history-custom-date').toggle(period === 'custom');
}

function currentHistoryRange() {
    applyHistoryPeriod();
    return {
        from: $('.start_date').val(),
        to: $('.end_date').val()
    };
}

loadData();
function loadData() {
    if (!$('.loaddata').length) {
        return;
    }
    var range = currentHistoryRange();
    $.ajax({
        url: swProcessUrl+'?action=history',
        type: 'POST',
        data: {from: range.from, to: range.to},
        success: function(data) {
          $('.loaddata').html(data);
        }
    });
}

$('.btn-clear').click(function (e) {
    if ($('.history_period').length) {
        $('.history_period').val('last_week');
        applyHistoryPeriod();
    }
    loadData();
});

$('.history_period').change(function () {
    applyHistoryPeriod();
});

$('.btn-sortir').click(function (e) {
        applyHistoryPeriod();
        var from = $('.start_date').val();
        var to   = $('.end_date').val();

       $.ajax({
          url: swProcessUrl+"?action=history",
          method:"POST",
          data:{from:from,to:to},
          dataType:"text",
          cache: false,
          async: false,
            beforeSend: function () {
             loading();
            },
            success: function (data) {
                $('.loaddata').html(data);
            },
        complete: function () {
            $(".loading").hide();
        },
    });
});

$('.btn-print').click(function (e) {
        var from        = $('.start_date').val();
        var to          = $('.end_date').val();
        var type        = $('.type').val();
        if(type =='pdf'){
            // cek berdasarkan bulan
            if(from==''){
                var url = swPrintUrl+"?action=pdf";
            }else{
                var url = swPrintUrl+"?action=pdf&from="+from+"&to="+to+"";
            }
        }else{
            if(from==''){
                var url = swPrintUrl+"?action=excel";
            }else{
                var url = swPrintUrl+"?action=excel&from="+from+"&to="+to+"";
            }
        }
        window.open(url, '_blank');
});

/* ------------------- UPDATE DATA HISTORY ------------------------- */
    $(document).on('click', '.modal-update', function(){
        $('#modal-show').modal('show');
        var presence_id = $(this).attr("data-id");
        document.getElementById('presence_id').value = presence_id;

        /*var masuk = $(this).attr("data-masuk");
        document.getElementById('timein').value = masuk;

        var pulang = $(this).attr("data-pulang");
        document.getElementById('timeout').value = pulang;*/

        var status = $(this).attr("data-status");
        document.getElementById('status').value = status;

        var information = $(this).attr("data-information");
        document.getElementById('information').value = information;

        var tanggal = $(this).attr("data-date");
        $('.status-date').html(tanggal);
    });

    /* ---------- UPDATE HISTORY-----------------*/
        $('#update-history').submit(function (e) {
            e.preventDefault();
            if($('#timein').val()=='' && $('#timeout').val()==''){
                 swal({title:'Oops!', text: 'Harap bwidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
                return false;
                loading();
            }
            else{
                loading();
                $.ajax({
                    url: swProcessUrl+"?action=update-history",
                    type: "POST",
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    cache: false,
                    async: false,
                    beforeSend: function () {
                      loading();
                    },
                    success: function (data) {
                        if (data == 'success') {
                            swal({title: 'Berhasil!', text: 'Absensi berhasil di perbaharui!', icon: 'success', timer: 2000,});
                            //setTimeout(function(){ location.reload(); }, 2500);
                            $('#modal-show').modal('hide');
                            loadData();
                        } else {
                            swal({title: 'Oops!', text: data, icon: 'error', timer: 2000,});

                        }
                    },

                    complete: function () {
                         $(".loading").hide();
                         $('#modal-show').modal('hide');
                    },
                });
            }
        });



    /* --------------------------------
        LOAD DATA CUTY
    ----------------------------------*/
    loadDataCuty();
    function loadDataCuty() {
        $.ajax({
            url: swProcessUrl+'?action=cuty',
            type: 'POST',
            success: function(data) {
              $('.loaddatacuty').html(data);
            }
        });
    }

    $('.btn-clear-cuty').click(function (e) {
        loadDataCuty();
        $('.start_date').val();
        $('.start_date').val();
    });


    $('.btn-sortir-cuty').click(function (e) {
            var from = $('.start_date').val();
            var to   = $('.end_date').val();

           $.ajax({
              url: swProcessUrl+"?action=cuty",
              method:"POST",
              data:{from:from,to:to},
              dataType:"text",
              cache: false,
              async: false,
                beforeSend: function () {
                 loading();
                },
                success: function (data) {
                    $('.loaddatacuty').html(data);
                },
            complete: function () {
                $(".loading").hide();
            },
        });
    });

    function isCutyDescriptionEmpty(form) {
        return $(form).find('textarea.cuty_description').val().trim() === '';
    }

    function parseCutyDate(value) {
        if (!value) {
            return null;
        }
        var parts = value.split('-');
        if (parts.length === 3) {
            var day = parseInt(parts[0], 10);
            var month = parseInt(parts[1], 10) - 1;
            var year = parseInt(parts[2], 10);
            if (!isNaN(day) && !isNaN(month) && !isNaN(year)) {
                return new Date(year, month, day);
            }
        }
        var parsed = new Date(value);
        return isNaN(parsed.getTime()) ? null : parsed;
    }

    function cutyDateRangeDays(form) {
        var start = parseCutyDate($(form).find('input[name=cuty_start]').val());
        var end = parseCutyDate($(form).find('input[name=cuty_end]').val());
        if (!start) {
            return 0;
        }
        if (!end) {
            end = start;
        }
        if (end < start) {
            return -1;
        }
        return Math.floor((end - start) / 86400000) + 1;
    }

    function toggleCutyDateFields(form) {
        var type = $(form).find('.cuty-type').val();
        var startField = $(form).find('.cuty-start-field');
        var startInput = startField.find('input');
        var startLabel = startField.find('.cuty-start-label');
        var endField = $(form).find('.cuty-end-field');
        var endInput = endField.find('input');
        var endLabel = endField.find('.cuty-end-label');
        var hourField = $(form).find('.cuty-hour-field');
        var hourInputs = hourField.find('input');
        var quotaInfo = $(form).find('.cuty-quota-info');
        var doctorField = $(form).find('.cuty-doctor-field');
        var doctorInput = doctorField.find('input[type=file]');
        var sickDays = cutyDateRangeDays(form);

        if (type === 'cuti') {
            startLabel.text('Mulai Cuti');
            endLabel.text('Berakhir Cuti');
            startField.show();
            endField.show();
            hourField.hide();
            quotaInfo.show();
            doctorField.hide();
            startInput.prop('required', true);
            endInput.prop('required', true);
            hourInputs.prop('required', false);
            doctorInput.prop('required', false);
        } else if (type === 'sakit') {
            startLabel.text('Mulai Sakit');
            endLabel.text('Berakhir Sakit');
            startField.show();
            endField.show();
            hourField.hide();
            quotaInfo.hide();
            doctorField.show();
            startInput.prop('required', true);
            endInput.prop('required', false);
            hourInputs.prop('required', false);
            doctorInput.prop('required', sickDays > 3);
        } else if (type === 'izin_jam') {
            startLabel.text('Tanggal Izin');
            startField.show();
            endField.hide();
            hourField.show();
            quotaInfo.hide();
            doctorField.hide();
            startInput.prop('required', true);
            endInput.prop('required', false);
            hourInputs.prop('required', true);
            doctorInput.prop('required', false);
        } else {
            startLabel.text('Tanggal Izin');
            startField.show();
            endField.hide();
            hourField.hide();
            quotaInfo.hide();
            doctorField.hide();
            startInput.prop('required', true);
            endInput.prop('required', false);
            hourInputs.prop('required', false);
            doctorInput.prop('required', false);
        }
    }

    $(document).on('change', '.cuty-type', function() {
        toggleCutyDateFields($(this).closest('form'));
    });

    $(document).on('change', 'input[name=cuty_start], input[name=cuty_end]', function() {
        toggleCutyDateFields($(this).closest('form'));
    });

    $('#modal-add, #modal-update').on('shown.bs.modal', function() {
        toggleCutyDateFields($(this).find('form'));
    });

    toggleCutyDateFields($('#form-add-cuty'));
    toggleCutyDateFields($('#form-update-cuty'));

    /* ----------- ADD DATA CUTY ------------*/
    $(document).on('submit', '#form-add-cuty', function (e) {
        e.preventDefault();
        var form = this;
        var type = $("select[name=cuty_type]", form).val();
        if($("#cutystart", form).val()==""){
             swal({title:'Oops!', text: 'Tanggal izin wajib diisi.!', icon: 'error', timer: 1500,});
            return false;
        }
        if(type=="cuti" && $("#cutyend", form).val()==""){
             swal({title:'Oops!', text: 'Tanggal mulai dan tanggal akhir wajib diisi untuk cuti.!', icon: 'error', timer: 1500,});
            return false;
        }
        if((type=="cuti" || type=="sakit") && cutyDateRangeDays(form) < 0){
             swal({title:'Oops!', text: 'Tanggal akhir tidak boleh sebelum tanggal mulai.!', icon: 'error', timer: 1500,});
            return false;
        }
        if(type=="sakit" && cutyDateRangeDays(form) > 3 && $("input[name=cuty_doctor_file]", form).val()==""){
             swal({title:'Oops!', text: 'Surat keterangan dokter wajib dilampirkan untuk sakit lebih dari 3 hari.!', icon: 'error', timer: 1500,});
            return false;
        }
        if(type=="izin_jam" && ($("input[name=cuty_time_start]", form).val()=="" || $("input[name=cuty_time_end]", form).val()=="")){
             swal({title:'Oops!', text: 'Jam mulai dan selesai wajib diisi untuk izin per jam.!', icon: 'error', timer: 1500,});
            return false;
        }
        if(isCutyDescriptionEmpty(form)){
             swal({title:'Oops!', text: 'Harap bidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
            return false;
        }
        else{
            $.ajax({
                url: swProcessUrl+"?action=add-cuty",
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                cache: false,
                async: false,
                beforeSend: function () {
                  loading();
                },
                success: function (data) {
                    if (data == 'success') {
                        swal({title: 'Berhasil!', text: 'Data Izin berhasil ditambah!', icon: 'success', timer: 2500,});
                        loadDataCuty();
                        $('#modal-add').modal('hide');
                        $('#form-add-cuty').trigger("reset");
                        toggleCutyDateFields($('#form-add-cuty'));
                        setTimeout(function(){ location.reload(); }, 1500);
                    } else {
                        swal({title: 'Oops!', text: data, icon: 'error', timer: 1500,});
                    }

                },
                complete: function () {
                    $(".loading").hide();
                },
            });
        }
    });

   $(document).on('click', '.btn-update-cuty', function(){
        $('#modal-update').modal('show');
        var id = $(this).attr("data-id");
        document.getElementById('city-id').value = id;

        var type = $(this).attr("data-type") || 'cuti';
        document.getElementById('cuty-type').value = type;

        var start = $(this).attr("data-start");
        document.getElementById('cuty-start').value = start;

        var end = $(this).attr("data-end");
        document.getElementById('cuty-end').value = end;

        var timeStart = $(this).attr("data-time-start") || "";
        document.getElementById('cuty-time-start').value = timeStart;

        var timeEnd = $(this).attr("data-time-end") || "";
        document.getElementById('cuty-time-end').value = timeEnd;

        var doctorFile = $(this).attr("data-doctor-file") || "";
        document.getElementById('cuty-doctor-file').value = "";
        $('.cuty-doctor-existing').text(doctorFile ? 'Lampiran saat ini: ' + doctorFile + '. Upload file baru untuk mengganti.' : '');

        var cuty_description = $(this).attr("data-description");
        document.getElementById('cuty_description').value = cuty_description;
        toggleCutyDateFields($('#form-update-cuty'));
        /*var cuty_description = $(this).attr("data-date");
        $('.status-date').html(tanggal);*/
    });

    /* ----------- UPDATE DATA CUTY ------------*/
    $(document).on('submit', '#form-update-cuty', function (e) {
        e.preventDefault();
        var form = this;
        var type = $("select[name=cuty_type]", form).val();
        if($("#cuty-start", form).val()==""){
             swal({title:'Oops!', text: 'Tanggal izin wajib diisi.!', icon: 'error', timer: 1500,});
            return false;
        }
        if(type=="cuti" && $("#cuty-end", form).val()==""){
             swal({title:'Oops!', text: 'Tanggal mulai dan tanggal akhir wajib diisi untuk cuti.!', icon: 'error', timer: 1500,});
            return false;
        }
        if((type=="cuti" || type=="sakit") && cutyDateRangeDays(form) < 0){
             swal({title:'Oops!', text: 'Tanggal akhir tidak boleh sebelum tanggal mulai.!', icon: 'error', timer: 1500,});
            return false;
        }
        if(type=="sakit" && cutyDateRangeDays(form) > 3 && $("input[name=cuty_doctor_file]", form).val()=="" && $('.cuty-doctor-existing').text()==""){
             swal({title:'Oops!', text: 'Surat keterangan dokter wajib dilampirkan untuk sakit lebih dari 3 hari.!', icon: 'error', timer: 1500,});
            return false;
        }
        if(type=="izin_jam" && ($("input[name=cuty_time_start]", form).val()=="" || $("input[name=cuty_time_end]", form).val()=="")){
             swal({title:'Oops!', text: 'Jam mulai dan selesai wajib diisi untuk izin per jam.!', icon: 'error', timer: 1500,});
            return false;
        }
        if(isCutyDescriptionEmpty(form)){
             swal({title:'Oops!', text: 'Harap bidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
            return false;
        }
        else{
            $.ajax({
                url: swProcessUrl+"?action=update-cuty",
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                cache: false,
                async: false,
                beforeSend: function () {
                  loading();
                },
                success: function (data) {
                    if (data == 'success') {
                        swal({title: 'Berhasil!', text: 'Data Izin berhasil disimpan!', icon: 'success', timer: 2500,});
                        loadDataCuty();
                        $('#modal-update').modal('hide');
                        $('#form-update-cuty').trigger("reset");
                        setTimeout(function(){ location.reload(); }, 1500);
                    } else {
                        swal({title: 'Oops!', text: data, icon: 'error', timer: 1500,});
                    }

                },
                complete: function () {
                    $(".loading").hide();
                },
            });
        }
    });


    /* ------------------ AJUKAN PENUGASAN ------------------*/
    $(document).on('submit', '#form-assignment-request', function (e) {
        e.preventDefault();
        var form = this;
        if($("select[name=assignment_signer_id]", form).val()=="" || $("input[name=assignment_start]", form).val()=="" || $("input[name=assignment_end]", form).val()=="" || $("input[name=assignment_location]", form).val()=="" || $("textarea[name=assignment_description]", form).val()==""){
             swal({title:'Oops!', text: 'Harap bidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
            return false;
        }
        $.ajax({
            url: swProcessUrl+"?action=assignment-request",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            cache: false,
            beforeSend: function () {
              loading();
            },
            success: function (data) {
                if (data == 'success') {
                    swal({title: 'Berhasil!', text: 'Ajuan penugasan berhasil dikirim.', icon: 'success', timer: 1800,});
                    $('#modal-assignment-request').modal('hide');
                    setTimeout(function(){ location.reload(); }, 1800);
                } else {
                    swal({title: 'Oops!', text: data, icon: 'error', timer: 2000,});
                }
            },
            complete: function () {
                $(".loading").hide();
            },
        });
    });

    /* ------------------ LOAD DATA COUNT ABSENSI HOME ------------------*/
    function loadDataCounter() {
        $.ajax({
            url: swProcessUrl+'?action=load-home-counter',
            type: 'POST',
            success: function(data) {
              $('.load-home').html(data);
            }
        });
    }
    loadDataCounter();
    $('.select-change').on('change', function() {
            var month_filter = this.value;
           $.ajax({
              url: swProcessUrl+"?action=load-home-counter",
              method:"POST",
              data:{month_filter:month_filter},
              dataType:"text",
              cache: false,
              async: false,
                beforeSend: function () {
                 loading();
                },
                success: function (data) {
                    $('.load-home').html(data);
                },
            complete: function () {
                $(".loading").hide();
            },
        });
    });

    /* ------------------ FAILED ACCESS ------------------*/
   $(document).on("click", ".access-failed", function(){
      swal({title:"Error!", text: "Anda tidak memiliki hak akses lagi!", icon:"error",timer:2500,});
    });



});





jQuery(function($) {
  setInterval(function() {
    var date = new Date(),
        time = date.toLocaleTimeString();
    $(".clock").html(time);
  }, 1000);
});


/* ---------- Print -----------------*/
function nWin(context,title) {
    var printWindow = window.open('', '');
    var doc = printWindow.document;
    doc.write("<html><head>");
    doc.write("<title>"+title+" - Print Mode</title>");
    doc.write("<link href='sw-mod/sw-assets/css/sw-print.css' rel='stylesheet' type='text/css' media='print'>");
    doc.write("</head><body>");
    doc.write(context);
    doc.write("</body></html>");
    doc.close();
    function show() {
        if (doc.readyState === "complete") {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        } else {
            setTimeout(show, 100);
        }
    };
    show();
};

$(function() {
    $(".print").click(function(){nWin($("#divToPrint").html(),$("#pagename").html());});
});

function printData(){
   var divToPrint=document.getElementById("printArea");
   newWin= window.open("");
   newWin.document.write(divToPrint.outerHTML);
   newWin.print();
   newWin.close();
}

/*$('.btn-print').on('click',function(){
    printData();
})*/
