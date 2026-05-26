$(document).ready(function() {

function loading(){
    $(".loading").show();
    $(".loading").delay(1500).fadeOut(500);
}

function openHolidayModal(cell) {
    var id = cell.attr('data-id') || '';
    $('#holidayid').val(id);
    $('#holidaydate').val(cell.attr('data-date') || '');
    $('#holidayname').val(cell.attr('data-name') || '');
    $('#holidaydescription').val(cell.attr('data-description') || '');
    $('#holidayactive').val(cell.attr('data-active') || '1');
    $('#holidayModalTitle').text(id ? 'Ubah Hari Libur' : 'Tandai Hari Libur');
    $('.holiday-delete-btn').toggle(id !== '').attr('data-id', id);
    $('#modalHoliday').modal('show');
}

$(document).on('click', '.holiday-cell:not(.empty)', function(){
    openHolidayModal($(this));
});

$('.save-libur').submit(function (e) {
    e.preventDefault();
    if($('#holidaydate').val()=='' || $('#holidayname').val()==''){
        swal({title:'Oops!', text: 'Tanggal dan nama libur wajib diisi.', icon: 'error', timer: 1500,});
        return false;
    }
    var action = $('#holidayid').val() ? 'update' : 'add';
    loading();
    $.ajax({
        url:"sw-mod/libur/proses.php?action="+action,
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
            data = $.trim(data);
            if (data == 'success') {
                swal({title: 'Berhasil!', text: 'Kalender libur berhasil disimpan.', icon: 'success', timer: 1500,});
                $('#modalHoliday').modal('hide');
                setTimeout(function(){ location.reload(); }, 1500);
            } else {
                swal({title: 'Oops!', text: data, icon: 'error', timer: 1500,});
            }
        },
        complete: function () {
            $(".loading").hide();
        },
    });
});

$(document).on('click', '.holiday-delete-btn', function(){
    var id = $(this).attr("data-id");
    if (!id) {
        return;
    }
    swal({
        text: "Anda yakin menghapus tanggal libur ini?",
        icon: "warning",
        buttons: {
            cancel: "Batal",
            catch: {text: "Hapus", value: "delete"},
        },
    }).then((value) => {
        if(value == 'delete'){
            loading();
            $.ajax({
                url:"sw-mod/libur/proses.php?action=delete",
                type:'POST',
                data:{id:id},
                success:function(data){
                    data = $.trim(data);
                    if (data == 'success') {
                        swal({title: 'Berhasil!', text: 'Tanggal libur berhasil dihapus.', icon: 'success', timer: 1500,});
                        $('#modalHoliday').modal('hide');
                        setTimeout(function(){ location.reload(); }, 1500);
                    } else {
                        swal({title: 'Gagal!', text: data, icon: 'error', timer: 1500,});
                    }
                }
            });
        }
    });
});

});
