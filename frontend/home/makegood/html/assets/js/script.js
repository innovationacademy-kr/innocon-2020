$(document).ready(function() {
  $('.side-close').click(function(){
    if($('.side-wrap').hasClass('on')){
      left_menu_onoff();
    }
  });

  var mMenuBtn = $(".depth01 a");
  mMenuBtn.click(function(){
    $(this).parent().siblings().removeClass("active");
    if($(this).parent().hasClass("active")){
      $(this).parent().removeClass("active");
    }else{
      $(this).parent().addClass("active");
    }
  });

  

  $("#content .tab-cont").hide();
  $("#tabs li:first").attr("id","current");
  $("#content .tab-cont:first").fadeIn();
 
  $('#tabs a').click(function(e) {
      e.preventDefault();
      if ($(this).closest("li").attr("id") == "current"){
       return      
      }
      else{            
      $("#content .tab-cont").hide();
      $("#tabs li").attr("id","");
      $(this).parent().attr("id","current");
      $('#' + $(this).attr('name')).fadeIn();
      }
  });

  $(".pairing #content .tab-cont").hide();
  $(".pairing #tabs li").attr("id","");
  $(".pairing #tabs li.today").attr("id","current");
  $(".pairing #content .today").fadeIn();
 
  $('.pairing #tabs a').click(function(e) {
      e.preventDefault();
      if ($(this).closest("li").attr("id") == "current"){
       return      
      }
      else{            
      $(".pairing #content .tab-cont").hide();
      $(".pairing #tabs li").attr("id","");
      $(this).parent().attr("id","current");
      $('#' + $(this).attr('name')).fadeIn();
      }
  });


});


$(window).resize(function() {
    var winW = $(window).width();
    var winH = $(window).height();

    if(winW > 1024) {
      if($('.side-wrap').hasClass('on')){
        left_menu_onoff();
      }
    }
});

function left_menu_onoff(){
    var winW = $(window).width();
    var winH = $(window).height();

    console.log("adfs")

    $(".side-wrap").stop().animate({width:'toggle'},350);
    if($('.side-wrap').hasClass('on')){
        $(".side-wrap").stop().animate({right:'-110%'},350);
        $(".side-wrap").hide();
        $(".black-bg").stop().fadeOut(350);
        $('.side-wrap').removeClass('on');
        $('body').css('overflow','visible');
    }else{
        $(".side-wrap").stop().animate({right:'0',width:'70%'},350);
        $(".black-bg").stop().fadeIn(350);
        $('.side-wrap').addClass('on');
        $(".side-wrap").show();
        $('body').css('overflow','hidden');
    }
}
