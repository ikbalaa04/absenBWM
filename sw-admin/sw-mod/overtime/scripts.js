$(document).ready(function() {
  $('#swdatatable').dataTable({
    "iDisplayLength": 20,
    "aLengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]]
  });

  function loading(){
    $(".loading").show();
    $(".loading").delay(1500).fadeOut(500);
  }

  function askApprovedHours(defaultValue, maxValue, callback) {
    swal({
      title: 'Durasi lembur disetujui',
      text: 'Masukkan durasi dalam jam. Maksimal '+maxValue+' jam.',
      content: {
        element: 'input',
        attributes: {
          type: 'number',
          min: '0.5',
          max: String(maxValue),
          step: '0.5',
          value: String(defaultValue),
          placeholder: 'Contoh: 2'
        }
      },
      buttons: ['Batal', 'Lanjut']
    }).then(function(value){
      if (value === null) {
        return;
      }
      value = String(value).replace(',', '.');
      if (value === '' || isNaN(value) || parseFloat(value) <= 0) {
        swal({title:'Oops!', text:'Durasi harus berupa angka lebih dari 0.', icon:'error', timer:2500});
        return;
      }
      if (parseFloat(value) > parseFloat(maxValue)) {
        swal({title:'Oops!', text:'Durasi tidak boleh melebihi durasi pengajuan.', icon:'error', timer:2500});
        return;
      }
      callback(value);
    });
  }

  $(document).on('click', '.approve-overtime', function(){
    var button = $(this);
    var id = button.data('id');
    var requestedHours = button.data('requested-hours');
    var approvedHours = button.data('approved-hours') || requestedHours;
    askApprovedHours(approvedHours, requestedHours, function(value){
      $.ajax({
        url:'sw-mod/overtime/proses.php?action=approve',
        type:'POST',
        data:{id:id, approved_hours:value},
        beforeSend: function () {
          loading();
        },
        success:function(data){
          if(data === 'success'){
            swal({title:'Berhasil!', text:'Pengajuan lembur disetujui.', icon:'success', timer:1800});
            setTimeout(function(){ location.reload(); }, 1900);
          }else{
            swal({title:'Oops!', text:data, icon:'error', timer:2500});
          }
        },
        complete: function () {
          $(".loading").hide();
        }
      });
    });
  });

  $(document).on('click', '.adjust-overtime', function(){
    var button = $(this);
    var id = button.data('id');
    var requestedHours = button.data('requested-hours');
    var approvedHours = button.data('approved-hours') || requestedHours;
    askApprovedHours(approvedHours, requestedHours, function(value){
      swal({
        title:'Sesuaikan ulang waktu?',
        text:'Durasi disetujui akan diperbarui. Jika aktual sudah lebih besar, aktual akan dibatasi ke durasi baru.',
        icon:'warning',
        buttons:['Batal','Simpan']
      }).then(function(ok){
        if(!ok){ return; }
        $.ajax({
          url:'sw-mod/overtime/proses.php?action=adjust-time',
          type:'POST',
          data:{id:id, approved_hours:value},
          beforeSend: function () {
            loading();
          },
          success:function(data){
            if(data === 'success'){
              swal({title:'Berhasil!', text:'Waktu lembur berhasil disesuaikan.', icon:'success', timer:1800});
              setTimeout(function(){ location.reload(); }, 1900);
            }else{
              swal({title:'Oops!', text:data, icon:'error', timer:2500});
            }
          },
          complete: function () {
            $(".loading").hide();
          }
        });
      });
    });
  });

  $(document).on('click', '.reject-overtime', function(){
    var id = $(this).data('id');
    swal({
      title:'Tolak pengajuan?',
      text:'Pengajuan lembur akan ditandai ditolak.',
      icon:'warning',
      buttons:['Batal','Tolak'],
      dangerMode:true
    }).then(function(ok){
      if(!ok){ return; }
      $.ajax({
        url:'sw-mod/overtime/proses.php?action=reject',
        type:'POST',
        data:{id:id},
        beforeSend: function () {
          loading();
        },
        success:function(data){
          if(data === 'success'){
            swal({title:'Berhasil!', text:'Pengajuan lembur ditolak.', icon:'success', timer:1800});
            setTimeout(function(){ location.reload(); }, 1900);
          }else{
            swal({title:'Oops!', text:data, icon:'error', timer:2500});
          }
        },
        complete: function () {
          $(".loading").hide();
        }
      });
    });
  });

  $(document).on('click', '.delete-overtime', function(){
    var id = $(this).data('id');
    swal({
      title:'Hapus data ini?',
      text:'Data pengajuan lembur akan dihapus permanen.',
      icon:'warning',
      buttons:true,
      dangerMode:true
    }).then(function(ok){
      if(!ok){ return; }
      $.ajax({
        url:'sw-mod/overtime/proses.php?action=delete',
        type:'POST',
        data:{id:id},
        beforeSend: function () {
          loading();
        },
        success:function(data){
          if(data === 'success'){
            swal({title:'Berhasil!', text:'Data berhasil dihapus.', icon:'success', timer:1500});
            setTimeout(function(){ location.reload(); }, 1500);
          }else{
            swal({title:'Oops!', text:data, icon:'error', timer:2500});
          }
        },
        complete: function () {
          $(".loading").hide();
        }
      });
    });
  });
});
