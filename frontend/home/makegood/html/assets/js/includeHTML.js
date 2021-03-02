function includeHTML(cb) { 
    var z, i, elmnt, file, xhttp; 
    z = document.getElementsByTagName("*"); 
    for (i = 0; i < z.length; i++) { 
        elmnt = z[i]; file = elmnt.getAttribute("include-html"); 
        if (file) { 
            xhttp = new XMLHttpRequest(); 
            xhttp.onreadystatechange = function() { 
                if (this.readyState == 4 && this.status == 200) { 
                    elmnt.innerHTML = this.responseText;
                    elmnt.removeAttribute("include-html"); 
                    includeHTML(cb);
                }

            } 
            xhttp.open("GET", file, true); xhttp.send(); 
            return; 
        } 
    }
    if (cb) cb();


    $('[data-popup-open]').on('click', function(e)  {
        var targeted_popup_class = jQuery(this).attr('data-popup-open');
        $('[data-popup="' + targeted_popup_class + '"]').fadeIn(0);
        $(".pop-bg").show();
        $("html, body").addClass("pop-scroll");
        e.preventDefault();
    });

    $('[data-popup-close]').on('click', function(e)  {
        var targeted_popup_class = jQuery(this).attr('data-popup-close');
        $('[data-popup="' + targeted_popup_class + '"]').fadeOut(0);
        $(".pop-bg").hide();
        $("html, body").removeClass("pop-scroll");
        e.preventDefault();
    });

    $('.pop-bg').on('click', function(e)  {
        $('[data-popup]').fadeOut(0);
        $(".pop-bg").hide();
        $("html, body").removeClass("pop-scroll");
        e.preventDefault();
    });

    $(".gnb").mouseenter(function() {
        $(".lnb").show();
    });

    $(".gnb").mouseleave(function() {
        $(".lnb").hide();
    });

    $('.black-bg').click(function(){
        if($('.side-wrap').hasClass('on')){
          left_menu_onoff();
        }
      });

    $(window).scroll(function() {
      if ($(this).scrollTop() > 30) {
        $('#header .inner').addClass("fixmenu");
      } else {
        $('#header .inner').removeClass("fixmenu");
      }
    });


    var filter = "win16|win32|win64|mac|macintel"; 
    if ( navigator.platform ) { 
        if ( filter.indexOf( navigator.platform.toLowerCase() ) < 0 ) { 
            $("body").addClass("mobile");
            } else { 
            //pc alert('pc 접속'); 
        } 
    }
}