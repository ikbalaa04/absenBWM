$(document).ready(function() {
  $('#swdatatable').dataTable({
    "iDisplayLength": 20,
    "aLengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]]
  });

  function loading(){
    $(".loading").show();
    $(".loading").delay(1500).fadeOut(500);
  }

  function appendDetailRow(table, label, value){
    var row = document.createElement('tr');
    var labelCell = document.createElement('th');
    var valueCell = document.createElement('td');
    labelCell.style.width = '130px';
    labelCell.textContent = label;
    valueCell.textContent = value || '-';
    row.appendChild(labelCell);
    row.appendChild(valueCell);
    table.appendChild(row);
  }

  $(document).on('click', '.detail-attendance-correction', function(){
    var detail = {};
    try {
      detail = JSON.parse($(this).attr('data-detail') || '{}');
    } catch (e) {
      detail = {};
    }

    var wrapper = document.createElement('div');
    wrapper.className = 'text-left';

    if (detail.foto) {
      var imageLink = document.createElement('a');
      imageLink.href = detail.foto;
      imageLink.target = '_blank';
      var image = document.createElement('img');
      image.src = detail.foto;
      image.alt = 'Foto bukti perbaikan absensi';
      image.style.width = '100%';
      image.style.maxHeight = '320px';
      image.style.objectFit = 'contain';
      image.style.borderRadius = '6px';
      image.style.marginBottom = '12px';
      imageLink.appendChild(image);
      wrapper.appendChild(imageLink);
    }

    var table = document.createElement('table');
    table.className = 'table table-bordered table-striped';
    appendDetailRow(table, 'Nama', detail.nama);
    appendDetailRow(table, 'Tanggal', detail.tanggal);
    appendDetailRow(table, 'Jenis', detail.jenis);
    appendDetailRow(table, 'Jam Masuk', detail.jam_masuk);
    appendDetailRow(table, 'Jam Pulang', detail.jam_pulang);
    appendDetailRow(table, 'Status', detail.status);
    appendDetailRow(table, 'Alasan', detail.alasan);
    wrapper.appendChild(table);

    swal({
      title: 'Detail Perbaikan Absensi',
      content: wrapper,
      button: 'Tutup'
    });
  });

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
