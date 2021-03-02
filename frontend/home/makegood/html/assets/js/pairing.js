$(document).ready(function() {
    var tabW = $(".pairing #tabs .today").width();
    var tabL = $(".pairing #tabs .today").position().left;
    $(".pairing #tabs").css("left",- tabL+"px");

    var winW = $(window).width();
    var winH = $(window).height();
    if(winW > 768) {
        $('.pairing #tabs a').click(function(e) {
            var navW = $(this).width();
            var navL = $(this).position().left;
            console.log(navW, navL);
            $(".pairing #tabs").css("left",- navL+"px");
        });
    }
});
$(window).resize(function() {
    var tabW = $(".pairing #tabs .today").width();
    var tabL = $(".pairing #tabs .today").position().left;
    $(".pairing #tabs").css("left",- tabL+"px");

    var winW = $(window).width();
    var winH = $(window).height();
    if(winW > 768) {
        $('.pairing #tabs a').click(function(e) {
            var navW = $(this).width();
            var navL = $(this).position().left;
            console.log(navW, navL);
            $(".pairing #tabs").css("left",- navL+"px");
        });
    }
});