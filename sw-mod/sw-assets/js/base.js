
///////////////////////////////////////////////////////////////////////////
// Loader
$(document).ready(function () {
    setTimeout(() => {
        $("#loader").fadeOut(250);
    }, 500); // hide delay when page load
});
///////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////
// Go Back
$(".goBack").click(function () {
    window.history.back();
});
///////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////
// Tooltip
$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})
///////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////
// Input
$(".clear-input").click(function () {
    $(this).parent(".input-wrapper").find(".form-control").focus();
    $(this).parent(".input-wrapper").find(".form-control").val("");
    $(this).parent(".input-wrapper").removeClass("not-empty");
});
// active
$(".form-group .form-control").focus(function () {
    $(this).parent(".input-wrapper").addClass("active");
}).blur(function () {
    $(this).parent(".input-wrapper").removeClass("active");
})
// empty check
$(".form-group .form-control").keyup(function () {
    var inputCheck = $(this).val().length;
    if (inputCheck > 0) {
        $(this).parent(".input-wrapper").addClass("not-empty");
    }
    else {
        $(this).parent(".input-wrapper").removeClass("not-empty");
    }
});
///////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////
// Searchbox Toggle
$(".toggle-searchbox").click(function () {
    $("#search").fadeToggle(200);
    $("#search .form-control").focus();
});

///////////////////////////////////////////////////////////////////////////
// Password Toggle
function initPasswordToggle(scope) {
    var $scope = scope ? $(scope) : $(document);
    $scope.find('input[type="password"]').each(function () {
        var $input = $(this);
        if ($input.data("password-toggle-ready")) {
            return;
        }

        var $wrap = $input.parent();
        if (!$wrap.hasClass("input-wrapper") && !$wrap.hasClass("password-toggle-wrap")) {
            $input.wrap('<div class="password-toggle-wrap"></div>');
            $wrap = $input.parent();
        } else {
            $wrap.addClass("password-toggle-wrap");
        }

        $input.addClass("password-toggle-input");
        $input.data("password-toggle-ready", true);
        $input.after('<button type="button" class="password-toggle-btn" aria-label="Lihat password"><ion-icon name="eye-outline"></ion-icon></button>');
    });
}

$(document).ready(function () {
    initPasswordToggle(document);
});

$(document).ajaxComplete(function () {
    initPasswordToggle(document);
});

$(document).on("click", ".password-toggle-btn", function () {
    var $button = $(this);
    var $input = $button.siblings("input").first();
    var isPassword = $input.attr("type") === "password";
    $input.attr("type", isPassword ? "text" : "password");
    $button.attr("aria-label", isPassword ? "Sembunyikan password" : "Lihat password");
    $button.html(isPassword ? '<ion-icon name="eye-off-outline"></ion-icon>' : '<ion-icon name="eye-outline"></ion-icon>');
});
///////////////////////////////////////////////////////////////////////////
