$(document).ready(function() {
  $('#swdatatable').dataTable({
    "iDisplayLength": 20,
    "aLengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]]
  });

  function loading(){
    $(".loading").show();
    $(".loading").delay(1500).fadeOut(500);
  }

  $(document).on('click', '.approve-attendance-correction', function(){
    var id = $(this).data('id');
    swal({
      title:'Setujui perbaikan?',
      text:'Sistem akan menambahkan data absensi sesuai pengajuan.',
      icon:'warning',
      buttons:['Batal','Setujui']
    }).then(function(ok){
      if(!ok){ return; }
      $.ajax({
        url:'sw-mod/attendance-correction/proses.php?action=approve',
        type:'POST',
        data:{id:id},
        beforeSend: function () { loading(); },
        success:function(data){
          if(data === 'success'){
            swal({title:'Berhasil!', text:'Pengajuan perbaikan disetujui.', icon:'success', timer:1800});
            setTimeout(function(){ location.reload(); }, 1900);
          }else{
            swal({title:'Oops!', text:data, icon:'error', timer:3000});
          }
        },
        complete: function () { $(".loading").hide(); }
      });
    });
  });

  $(document).on('click', '.reject-attendance-correction', function(){
    var id = $(this).data('id');
    swal({
      title:'Tolak pengajuan?',
      text:'Pengajuan perbaikan akan ditandai ditolak.',
      icon:'warning',
      buttons:['Batal','Tolak'],
      dangerMode:true
    }).then(function(ok){
      if(!ok){ return; }
      $.ajax({
        url:'sw-mod/attendance-correction/proses.php?action=reject',
        type:'POST',
        data:{id:id},
        beforeSend: function () { loading(); },
        success:function(data){
          if(data === 'success'){
            swal({title:'Berhasil!', text:'Pengajuan perbaikan ditolak.', icon:'success', timer:1800});
            setTimeout(function(){ location.reload(); }, 1900);
          }else{
            swal({title:'Oops!', text:data, icon:'error', timer:3000});
          }
        },
        complete: function () { $(".loading").hide(); }
      });
    });
  });

  $(document).on('click', '.delete-attendance-correction', function(){
    var id = $(this).data('id');
    swal({
      title:'Hapus data ini?',
      text:'Data pengajuan perbaikan akan dihapus permanen.',
      icon:'warning',
      buttons:true,
      dangerMode:true
    }).then(function(ok){
      if(!ok){ return; }
      $.ajax({
        url:'sw-mod/attendance-correction/proses.php?action=delete',
        type:'POST',
        data:{id:id},
        beforeSend: function () { loading(); },
        success:function(data){
          if(data === 'success'){
            swal({title:'Berhasil!', text:'Data berhasil dihapus.', icon:'success', timer:1500});
            setTimeout(function(){ location.reload(); }, 1500);
          }else{
            swal({title:'Oops!', text:data, icon:'error', timer:3000});
          }
        },
        complete: function () { $(".loading").hide(); }
      });
    });
  });
});
