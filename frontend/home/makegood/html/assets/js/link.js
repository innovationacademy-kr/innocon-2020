$(document).ready(function() {

    $(".exhibition_link").mouseover(function() {
        $('.exhibition_link > .box').addClass('box_show');
        $('.exhibition_link > .box').show();
    }).mouseleave(function () {
        $('.exhibition_link > .box').hide();
        $('.exhibition_link > .box').removeClass('box_show');
    });
    $(".sponser_link").mouseover(function() {
        $('.sponser_link > .box').addClass('box_show');
        $('.sponser_link > .box').show();
    }).mouseleave(function () {
        $('.sponser_link > .box').hide();
        $('.sponser_link > .box').removeClass('box_show');
    });

    $(".welcome_link").mouseover(function() {
        $('.welcome_link > .box').addClass('box_show');
        $('.welcome_link > .box').show();
    }).mouseleave(function () {
        $('.welcome_link > .box').hide();
        $('.welcome_link > .box').removeClass("box_show");
    });

    $(".info_link").mouseover(function() {
        $('.info_link > .box').addClass('box_show');
        $('.info_link > .box').show();
    }).mouseleave(function () {
        $('.info_link > .box').hide();
        $('.info_link > .box').removeClass('box_show');
    });

    $(".business_link").mouseover(function() {
        $('.business_link > .box').addClass('box_show');
        $('.business_link > .box').show();
    }).mouseleave(function () {
        $('.business_link > .box').hide();
        $('.business_link > .box').removeClass('box_show');
    });

    $(".pitching_link").mouseover(function() {
        $('.pitching_link > .box').addClass('box_show');
        $('.pitching_link > .box').show();
    }).mouseleave(function () {
        $('.pitching_link > .box').hide();
        $('.pitching_link > .box').removeClass('box_show');
    });

    $(".conference_link").mouseover(function() {
        $('.conference_link > .box').addClass('box_show');
        $('.conference_link > .box').show();
    }).mouseleave(function () {
        $('.conference_link > .box').hide();
        $('.conference_link > .box').removeClass('box_show');
    });

    $(".onAir_link").mouseover(function() {
        $('.onAir_link > .box').addClass('box_show');
        $('.onAir_link > .box').show();
    }).mouseleave(function () {
        $('.onAir_link > .box').hide();
        $('.onAir_link > .box').removeClass('box_show');
    });
});