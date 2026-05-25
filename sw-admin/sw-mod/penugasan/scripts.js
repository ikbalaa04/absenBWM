$(document).ready(function() {
$('#swdatatable').dataTable({
    "iDisplayLength": 20,
    "aLengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]]
});

function loading(){
    $(".loading").show();
    $(".loading").delay(1500).fadeOut(500);
}

$('.add-penugasan').submit(function (e) {
    e.preventDefault();
    loading();
    $.ajax({
        url:"sw-mod/penugasan/proses.php?action=add",
        type: "POST",
        data: new FormData(this),
        processData: false,
        contentType: false,
        cache: false,
        success: function (data) {
            if (data == 'success') {
                swal({title: 'Berhasil!', text: 'Penugasan berhasil dibuat.', icon: 'success', timer: 1500,});
                $('#modalAdd').modal('hide');
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

$(document).on('click', '.btn-extend', function(){
    $('#extend-assignment-id').val($(this).attr('data-id'));
    $('#extend-assignment-end').val($(this).attr('data-end'));
});

$(document).on('click', '.btn-edit', function(){
    $('#edit-assignment-id').val($(this).attr('data-id'));
    $('#edit-employees-id').val($(this).attr('data-employees'));
    $('#edit-assignment-signer-id').val($(this).attr('data-signer'));
    $('#edit-assignment-start').val($(this).attr('data-start'));
    $('#edit-assignment-end').val($(this).attr('data-end'));
    $('#edit-assignment-location').val($(this).attr('data-location'));
    $('#edit-assignment-description').val($(this).attr('data-description'));
    $('#edit-assignment-status').val($(this).attr('data-status'));
});

$('.update-penugasan').submit(function (e) {
    e.preventDefault();
    loading();
    $.ajax({
        url:"sw-mod/penugasan/proses.php?action=update",
        type: "POST",
        data: new FormData(this),
        processData: false,
        contentType: false,
        cache: false,
        success: function (data) {
            if (data == 'success') {
                swal({title: 'Berhasil!', text: 'Penugasan berhasil diperbarui.', icon: 'success', timer: 1500,});
                $('#modalEdit').modal('hide');
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

$('.extend-penugasan').submit(function (e) {
    e.preventDefault();
    loading();
    $.ajax({
        url:"sw-mod/penugasan/proses.php?action=extend",
        type: "POST",
        data: new FormData(this),
        processData: false,
        contentType: false,
        cache: false,
        success: function (data) {
            if (data == 'success') {
                swal({title: 'Berhasil!', text: 'Waktu penugasan berhasil diperbarui.', icon: 'success', timer: 1500,});
                $('#modalExtend').modal('hide');
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

$(document).on('click', '.update-status', function(){
    var id = $(this).attr("data-id");
    var status = $(this).attr("data-status");
    $.ajax({
        url:"sw-mod/penugasan/proses.php?action=update-status&status="+status,
        type:'POST',
        data:{id:id},
        beforeSend: function () {
            loading();
        },
        success: function (data) {
            if (data == 'success') {
                swal({title: 'Berhasil!', text: 'Status penugasan berhasil diperbarui.', icon: 'success', timer: 1500,});
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
