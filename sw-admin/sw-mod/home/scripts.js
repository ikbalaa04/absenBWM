$(document).ready(function() {
  function weeklyWorkLoading(show) {
    if (show) {
      $(".loading").show();
      $("#weekly-work-box").css("opacity", "0.55");
    } else {
      $(".loading").hide();
      $("#weekly-work-box").css("opacity", "1");
    }
  }

  function loadWeeklyWork(url) {
    weeklyWorkLoading(true);
    $.ajax({
      url: url,
      type: "GET",
      cache: false,
      success: function(data) {
        var parsed = $("<div>").append($.parseHTML(data, document, true));
        var nextBox = parsed.find("#weekly-work-box");
        if (nextBox.length) {
          $("#weekly-work-box").replaceWith(nextBox);
          window.history.pushState({}, "", url);
        } else {
          swal({title:"Oops!", text:"Data pemenuhan jam tidak dapat dimuat.", icon:"error", timer:2500});
        }
      },
      error: function() {
        swal({title:"Oops!", text:"Gagal memuat data pemenuhan jam.", icon:"error", timer:2500});
      },
      complete: function() {
        weeklyWorkLoading(false);
      }
    });
  }

  $(document).on("submit", ".weekly-work-filter-form", function(e) {
    e.preventDefault();
    loadWeeklyWork("./?" + $(this).serialize());
  });

  $(document).on("click", "a.weekly-work-preset", function(e) {
    e.preventDefault();
    loadWeeklyWork($(this).attr("href"));
  });

  $(document).on("click", "#weekly-work-box .pagination a", function(e) {
    e.preventDefault();
    loadWeeklyWork($(this).attr("href"));
  });
});
