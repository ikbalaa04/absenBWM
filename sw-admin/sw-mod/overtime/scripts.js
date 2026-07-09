$(document).on('click', '.approve-overtime', function(){
  var button = $(this);
  var row = button.closest('td');
  var id = button.data('id');
  var approvedHours = row.find('.approved-hours').val();
  var maxHours = parseFloat(row.find('.approved-hours').data('max') || 4);
  if (parseFloat(approvedHours) > maxHours) {
    swal({title:'Oops!', text:'Durasi disetujui tidak boleh melebihi durasi pengajuan.', icon:'error', timer:2500});
    return;
  }
  $.ajax({
    url:'sw-mod/overtime/proses.php?action=approve',
    type:'POST',
    data:{id:id, approved_hours:approvedHours},
    success:function(data){
      if(data === 'success'){
        swal({title:'Berhasil!', text:'Pengajuan lembur disetujui.', icon:'success', timer:1800});
        setTimeout(function(){ location.reload(); }, 1900);
      }else{
        swal({title:'Oops!', text:data, icon:'error', timer:2500});
      }
    }
  });
});

$(document).on('click', '.reject-overtime', function(){
  var id = $(this).data('id');
  swal({
    title:'Tolak pengajuan?',
    text:'Pengajuan lembur akan ditandai ditolak.',
    icon:'warning',
    buttons:['Batal','Tolak']
  }).then(function(ok){
    if(!ok){ return; }
    $.ajax({
      url:'sw-mod/overtime/proses.php?action=reject',
      type:'POST',
      data:{id:id},
      success:function(data){
        if(data === 'success'){
          swal({title:'Berhasil!', text:'Pengajuan lembur ditolak.', icon:'success', timer:1800});
          setTimeout(function(){ location.reload(); }, 1900);
        }else{
          swal({title:'Oops!', text:data, icon:'error', timer:2500});
        }
      }
    });
  });
});
