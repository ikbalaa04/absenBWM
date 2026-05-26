$(document).ready(function() {
$('#swdatatable').dataTable({
    "iDisplayLength": 20,
    "aLengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]]
});

//Timepicker
$('.timepicker').timepicker({
    showInputs: false,
    showMeridian: false,
    use24hours: true,
    format :'HH:mm'
})

function toggleOutsideRule(scope) {
    var isChecked = $('#' + scope + '_use_outside_rule').is(':checked');
    $('.' + scope + '-outside-rule').toggle(isChecked);
}

window.setEditOutsideRule = function(isEnabled) {
    $('#edit_use_outside_rule').prop('checked', isEnabled);
    toggleOutsideRule('edit');
};

$('#add_use_outside_rule').on('change', function() {
    toggleOutsideRule('add');
});

$('#edit_use_outside_rule').on('change', function() {
    toggleOutsideRule('edit');
});

toggleOutsideRule('add');
toggleOutsideRule('edit');


function loading(){
    $(".loading").show();
    $(".loading").delay(1500).fadeOut(500);
}

function cleanResponse(data) {
    return $.trim(data || '');
}

/* ----------- Add ------------*/
$('.add-shift').submit(function (e) {
    if($('.add-shift input[name=shift_name]').val()=='' || $('.add-shift input[name=time_in]').val()=='' || ($('.add-shift input[name=checkout_required]').is(':checked') && $('.add-shift input[name=time_out]').val()=='')){    
        swal({title:'Oops!', text: 'Harap bidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
        return false;
        loading();
    }
    if($('#add_use_outside_rule').is(':checked') && ($('.add-shift input[name=outside_time_in]').val()=='' || ($('.add-shift input[name=checkout_required]').is(':checked') && $('.add-shift input[name=outside_time_out]').val()==''))){
        swal({title:'Oops!', text: 'Jam luar kantor wajib diisi jika aturan luar kantor digunakan.!', icon: 'error', timer: 1500,});
        return false;
    }
    if($('#add_use_outside_rule').is(':checked') && parseInt($('.add-shift input[name=outside_min_work_minutes]').val() || '0', 10) <= 0){
        swal({title:'Oops!', text: 'Minimal jam luar kantor mingguan wajib diisi jika aturan luar kantor digunakan.!', icon: 'error', timer: 1500,});
        return false;
    }
    if($('#add_use_outside_rule').is(':checked') && parseInt($('.add-shift input[name=outside_weekly_limit_minutes]').val() || '0', 10) <= 0){
        swal({title:'Oops!', text: 'Maksimal jam luar kantor mingguan wajib diisi jika aturan luar kantor digunakan.!', icon: 'error', timer: 1500,});
        return false;
    }
    if($('#add_use_outside_rule').is(':checked') && parseInt($('.add-shift input[name=outside_min_work_minutes]').val() || '0', 10) > parseInt($('.add-shift input[name=outside_weekly_limit_minutes]').val() || '0', 10)){
        swal({title:'Oops!', text: 'Minimal jam luar kantor tidak boleh lebih besar dari maksimal jam luar kantor.!', icon: 'error', timer: 1500,});
        return false;
    }
    else{
        loading();
        e.preventDefault();
        $.ajax({
            url:"sw-mod/shift/proses.php?action=add",
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
                var response = cleanResponse(data);
                if (response == 'success') {
                    swal({title: 'Berhasil!', text: 'Data Shift  berhasil disimpan.!', icon: 'success', timer: 1500,});
                   $('#modalAdd').modal('hide');
                   setTimeout(function(){ location.reload(); }, 1500);
                } else {
                    swal({title: 'Oops!', text: response || 'Data Shift tidak berhasil disimpan.', icon: 'error', timer: 1500,});
                }

            },
            complete: function () {
                $(".loading").hide();
            },
        });
    }
  });

/* -------------------- Edit ------------------- */
$('.update-shift').submit(function (e) {
    if($('#txtname').val()=='' || $('#txtin').val()=='' || ($('#txtcheckout').is(':checked') && $('#txtout').val()=='')){    
         swal({title: 'Oops!', text: 'Harap bidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
         loading();
        return false;
    }
    if($('#edit_use_outside_rule').is(':checked') && ($('#txtoutsidein').val()=='' || ($('#txtcheckout').is(':checked') && $('#txtoutsideout').val()==''))){
         swal({title: 'Oops!', text: 'Jam luar kantor wajib diisi jika aturan luar kantor digunakan.!', icon: 'error', timer: 1500,});
         loading();
        return false;
    }
    if($('#edit_use_outside_rule').is(':checked') && parseInt($('#txtoutsidemin').val() || '0', 10) <= 0){
         swal({title: 'Oops!', text: 'Minimal jam luar kantor mingguan wajib diisi jika aturan luar kantor digunakan.!', icon: 'error', timer: 1500,});
         loading();
        return false;
    }
    if($('#edit_use_outside_rule').is(':checked') && parseInt($('#txtoutsidelimit').val() || '0', 10) <= 0){
         swal({title: 'Oops!', text: 'Maksimal jam luar kantor mingguan wajib diisi jika aturan luar kantor digunakan.!', icon: 'error', timer: 1500,});
         loading();
        return false;
    }
    if($('#edit_use_outside_rule').is(':checked') && parseInt($('#txtoutsidemin').val() || '0', 10) > parseInt($('#txtoutsidelimit').val() || '0', 10)){
         swal({title: 'Oops!', text: 'Minimal jam luar kantor tidak boleh lebih besar dari maksimal jam luar kantor.!', icon: 'error', timer: 1500,});
         loading();
        return false;
    }
    else{
        loading();
        e.preventDefault();
        $.ajax({
            url:"sw-mod/shift/proses.php?action=update",
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
                var response = cleanResponse(data);
                if (response == 'success') {
                    swal({title: 'Berhasil!', text: 'Data Shift berhasil disimpan.!', icon: 'success', timer: 1500,});
                   $('#modalEdit').modal('hide');
                   setTimeout(function(){ location.reload(); }, 1500);

                } else {
                    swal({title: 'Oops!', text: response || 'Data Shift tidak berhasil disimpan.', icon: 'error', timer: 1500,});
                }

            },
            complete: function () {
                $(".loading").hide();
            },
        });
    }
  });


/*------------ Delete -------------*/
 $(document).on('click', '.delete', function(){ 
        var id = $(this).attr("data-id");
          swal({
            text: "Anda yakin menghapus data ini?",
            icon: "warning",
              buttons: {
                cancel: true,
                confirm: true,
              },
            value: "delete",
          })

          .then((value) => {
            if(value) {
                loading();
                $.ajax({  
                     url:"sw-mod/shift/proses.php?action=delete",
                     type:'POST',    
                     data:{id:id},  
                    success:function(data){ 
                        var response = cleanResponse(data);
                        if (response == 'success') {
                            swal({title: 'Berhasil!', text: 'Data berhasil dihapus.!', icon: 'success', timer: 1500,});
                            setTimeout(function(){ location.reload(); }, 1500);
                        } else {
                            swal({title: 'Gagal!', text: response || 'Data tidak berhasil dihapus.', icon: 'error', timer: 1500,});
                            
                        }
                     }  
                });  
           } else{  
            return false;
        }  
    });
}); 

});
