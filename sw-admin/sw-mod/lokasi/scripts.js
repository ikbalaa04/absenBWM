$(document).ready(function() {
$('#swdatatable').dataTable({
    "iDisplayLength": 20,
    "aLengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]]
});

var locationMaps = {};
var defaultOfficeLocation = window.defaultOfficeLocation || {
    latitude: -6.200000,
    longitude: 106.816666,
    radius: 150
};

function parseCoordinate(value) {
    var number = parseFloat(value);
    return isFinite(number) ? number : null;
}

function getLocationForm(modalSelector) {
    return $(modalSelector).find('form');
}

function getLocationState(modalSelector) {
    var form = getLocationForm(modalSelector);
    var lat = parseCoordinate(form.find('.location-latitude').val());
    var lng = parseCoordinate(form.find('.location-longitude').val());
    var radius = parseInt(form.find('.location-radius').val(), 10);
    if (!radius || radius < 10) {
        radius = 150;
    }
    return {
        lat: lat,
        lng: lng,
        radius: radius
    };
}

function setLocationPoint(modalSelector, latlng) {
    var form = getLocationForm(modalSelector);
    form.find('.location-latitude').val(latlng.lat.toFixed(8));
    form.find('.location-longitude').val(latlng.lng.toFixed(8));
    updateLocationMap(modalSelector);
}

function initLocationMap(modalSelector, mapId) {
    if (locationMaps[mapId] || typeof L === 'undefined') {
        return;
    }

    var defaultPoint = [defaultOfficeLocation.latitude, defaultOfficeLocation.longitude];
    var map = L.map(mapId).setView(defaultPoint, 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: ''
    }).addTo(map);

    map.on('click', function(e) {
        setLocationPoint(modalSelector, e.latlng);
    });

    locationMaps[mapId] = {
        map: map,
        marker: null,
        circle: null
    };
}

function updateLocationMap(modalSelector) {
    var mapId = modalSelector === '#modalEdit' ? 'location-map-edit' : 'location-map-add';
    initLocationMap(modalSelector, mapId);

    var preview = locationMaps[mapId];
    if (!preview) {
        return;
    }

    var state = getLocationState(modalSelector);
    preview.map.invalidateSize();

    if (state.lat === null || state.lng === null) {
        preview.map.setView([defaultOfficeLocation.latitude, defaultOfficeLocation.longitude], 12);
        if (preview.marker) {
            preview.map.removeLayer(preview.marker);
            preview.marker = null;
        }
        if (preview.circle) {
            preview.map.removeLayer(preview.circle);
            preview.circle = null;
        }
        return;
    }

    var point = [state.lat, state.lng];
    if (!preview.marker) {
        preview.marker = L.marker(point).addTo(preview.map);
    } else {
        preview.marker.setLatLng(point);
    }

    if (!preview.circle) {
        preview.circle = L.circle(point, {
            radius: state.radius,
            color: '#dd4b39',
            weight: 2,
            fillColor: '#dd4b39',
            fillOpacity: 0.18
        }).addTo(preview.map);
    } else {
        preview.circle.setLatLng(point);
        preview.circle.setRadius(state.radius);
    }

    preview.map.setView(point, state.radius > 1000 ? 13 : 16);
}

$('#modalAdd').on('shown.bs.modal', function() {
    updateLocationMap('#modalAdd');
});

$('#modalEdit').on('shown.bs.modal', function() {
    updateLocationMap('#modalEdit');
});

$('.location-latitude, .location-longitude, .location-radius').on('input change', function() {
    var modalSelector = $(this).closest('#modalEdit').length ? '#modalEdit' : '#modalAdd';
    updateLocationMap(modalSelector);
});


function loading(){
    $(".loading").show();
    $(".loading").delay(1500).fadeOut(500);
}

/* ----------- Add ------------*/
$('.add-lokasi').submit(function (e) {
    e.preventDefault();
    if($('.add-lokasi input[name="name"]').val()=='' || $('.add-lokasi textarea[name="address"]').val()==''){    
        swal({title:'Oops!', text: 'Harap bidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
        return false;
    }
    if($('.add-lokasi input[name="latitude"]').val()=='' || $('.add-lokasi input[name="longitude"]').val()=='' || $('.add-lokasi input[name="radius_meter"]').val()==''){
        swal({title:'Oops!', text: 'Latitude, longitude, dan radius wajib diisi untuk validasi lokasi.!', icon: 'error', timer: 1500,});
        return false;
    }
    else{
        loading();
        $.ajax({
            url:"sw-mod/lokasi/proses.php?action=add",
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
                    swal({title: 'Berhasil!', text: 'Data lokasi  berhasil disimpan.!', icon: 'success', timer: 1500,});
                   $('#modalAdd').modal('hide');
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

/* -------------------- Edit ------------------- */
$('.update-lokasi').submit(function (e) {
    e.preventDefault();
    if($('#txtname').val()=='' || $('#txtaddress').val()==''){    
         swal({title: 'Oops!', text: 'Harap bidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
        return false;
    }
    if($('#txtlatitude').val()=='' || $('#txtlongitude').val()=='' || $('#txtradius').val()==''){
        swal({title:'Oops!', text: 'Latitude, longitude, dan radius wajib diisi untuk validasi lokasi.!', icon: 'error', timer: 1500,});
        return false;
    }
    else{
        loading();
        $.ajax({
            url:"sw-mod/lokasi/proses.php?action=update",
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
                    swal({title: 'Berhasil!', text: 'Data Lokasi berhasil disimpan.!', icon: 'success', timer: 1500,});
                   $('#modalEdit').modal('hide');
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
                     url:"sw-mod/lokasi/proses.php?action=delete",
                     type:'POST',    
                     data:{id:id},  
                    success:function(data){ 
                        if (data == 'success') {
                            swal({title: 'Berhasil!', text: 'Data berhasil dihapus.!', icon: 'success', timer: 1500,});
                            setTimeout(function(){ location.reload(); }, 1500);
                        } else {
                            swal({title: 'Gagal!', text: data, icon: 'error', timer: 1500,});
                            
                        }
                     }  
                });  
           } else{  
            return false;
        }  
    });
}); 

$(".btn-print").on('click',function () {
    $("#printarea").show();
    window.print();
});


});
