;(function($, exports, undefined){
    "use strict";
    var commonjs = {
        eventClick : function (event) {
            'use strict';
            if(event.preventDefault) {
                event.preventDefault(); //FF
            } else {
                event.returnValue = false; //IE
            }
        },
        resizeModule : function (fn){
            "use strict";
            var setID;
            $(window).resize(function(){
                window.clearTimeout(setID);
                setID = window.setTimeout(function(){
                    if(typeof(fn) == 'function')fn();
                },10);
            });
        },
        mobilecheck : function() {
            var check = false;
            (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
            return check;
        }
    };
    var desktopEvents = ['mousedown', 'mousemove', 'mouseup'];
    var swipeslide = false;
    if(commonjs.mobilecheck() && ('ontouchstart' in window || window.DocumentTouch && document instanceof window.DocumentTouch || navigator.maxTouchPoints > 0 ||  window.navigator.msMaxTouchPoints > 0)){
        var touchsupport = true;
    }else{
        var touchsupport = false;
    }
    var touchEvents = {
        touchStart:  touchsupport ? 'touchstart' : desktopEvents[0],
        touchMove: touchsupport ? 'touchmove' : desktopEvents[1],
        touchEnd: touchsupport ? 'touchend' : desktopEvents[2]
    };
    var sint;
    $.fn.viClear = function(){
          window.clearInterval(sint);
   }
    if(!$.fn.viSimpleSlider){
        var settings = {
            ease : 'swing',
            indicate : true,
            arrow : true,
            autoPlay : false,
            autoTime : 3000,
            speed : 800,
            cut : 0,
            loop : true,
            mobileSwipe : true,
            desktopSwipe : true
        }
    }
    //if

    $.fn.viSimpleSlider = function(option){
        var $this = $(this),
            setVal = $.type(option) == 'object' ? $.extend({},settings,option) : settings;

        $this.find('>ul').addClass('visimpleslider-sliders')
        var $WrapUl = $this.find('>ul.visimpleslider-sliders');
        var thiscurrent = 0,
            defaultW = $this.width(),
            maxSlide = $WrapUl.find('>li').length,
            Xposition = [],
            orgImg = [],
            mImg = [],
            dragging = false,
            motioning = false,
            slideWrapH = 0,
            slideWrapX,
            x_origine,
            y_origine,
            x_new,
            y_new,
            ratio,
            intTime = setVal.autoTime;

        $this.addClass('viSimpleSlider');
        var keypress = false;
        if(setVal.desktopSwipe){
            swipeslide = true;
        }else{
            swipeslide = false;
            if(touchsupport && setVal.mobileSwipe) swipeslide = true;
        }
        var SimpleSlider = {
            /***********************************************************************************         1. 슬라이드 생성          ************/
            mkSlide : function(e){
                $WrapUl.find('>li').each(function(i){
                    var thisSlide = $(this);
                    var links = thisSlide.data('link'),
                        target = thisSlide.data('target');
                        thisSlide.append('<a href="#" class="clickDiv" style="display:block;position:absolute;left:0;top:0;width:100%;height:100%;z-index:10;-webkit-tap-highlight-color:rgba(0,0,0,0);"></a>').addClass('slide');
                    if(setVal.desktopSwipe){
                        thisSlide.find('.clickDiv').mousedown(function(e){
                            if(!dragging && !motioning){
                                keypress = true;
                            }
                            commonjs.eventClick(e);
                        });
                        thisSlide.find('.clickDiv').mouseup(function(e){
                            if(keypress && !dragging && !motioning){
                                if(links && target == "_blank")window.open(links);
                                else if(links && target !== "_blank") location.href = links;
                            }
                            commonjs.eventClick(e);
                        })
                        thisSlide.find('.clickDiv').on('click', function(e){
                            if(!dragging && !motioning){
                                if(links && target == "_blank")window.open(links);
                                else if(links && target !== "_blank") location.href = links;
                            }
                            commonjs.eventClick(e);
                        });
                    }else{
                        thisSlide.find('.clickDiv').on('click', function(e){
                            if(!dragging && !motioning){
                                if(links && target == "_blank")window.open(links);
                                else if(links && target !== "_blank") location.href = links;
                            }
                            commonjs.eventClick(e);
                        });
                    }

                });
            },

            /***********************************************************************************         2. 생성한 슬라이드 정렬          ************/
            sortSlide : function(e){
                function Imgload(i){
                    var thisSlide = $WrapUl.find('>li').eq(i);
                    var thisSlideImg = thisSlide.find('>img');
                    var img = new Image();
                    img.onload = function(){
//                        console.log('load comp');
                        ratio = img.height/img.width;
                        setSlide(thisSlide, i);
                    };
                    img.error = function(){
//                        console.log('load errer');
                        setSlide(thisSlide, i);
                    };
                    img.src = thisSlideImg.attr('src');
                }
                Imgload(0);

                function setSlide(ele, Num){
                    var $thisLi = ele;
                    Xposition[Num] = defaultW * Num;
//                    console.log('Xposition ' + Xposition[Num])
                    $thisLi.css({
                        'position' : 'absolute',
                        'display' : 'block',
                        'width' :  defaultW,
                        'left' : Xposition[Num],
                        'z-index' : '10'
                    });
                    if(slideWrapH <= 0 && Num == 0){
                        slideWrapH = Math.floor(defaultW*ratio-setVal.cut);
                        $WrapUl.height(slideWrapH);
                    }
                    if(Num < maxSlide-1)Imgload(Num+1);
                    //반복 시키기
                }
                //setSlide
            },

            /***********************************************************************************         3. 앞뒤 슬라이드 복사          ************/
            cloneslide : function(){
                Xposition[maxSlide] = defaultW*(maxSlide);
                Xposition[maxSlide+1] = 0-defaultW;
                $WrapUl.find('>li').eq(0).clone().appendTo($WrapUl).addClass('clone last').css({
                    'left' : Xposition[maxSlide],
                    'width' : defaultW,
                    'z-index' : '9'
                });
                $WrapUl.find('>li').eq(maxSlide-1).clone().appendTo($WrapUl).addClass('clone first').css({
                    'left' : Xposition[maxSlide+1],
                    'width' : defaultW,
                    'z-index' : '9'
                });
            },

            /***********************************************************************************         (옵션) 자동 슬라이드          ************/
            autoSlide : function(){

                window.clearInterval(sint);
                sint = window.setInterval(function(){
                    if(thiscurrent < maxSlide && setVal.loop){
                        thiscurrent = thiscurrent + 1;
                        slidefunc.slideMotion(thiscurrent);
                    }else if(thiscurrent >= maxSlide && setVal.loop){
                        //마지막에 도착했으면 이어지게 하려는 코드
                        thiscurrent = maxSlide-1;
                        slidefunc.slideMotion(thiscurrent);
                    }else if(thiscurrent < maxSlide-1 && !setVal.loop){
                        thiscurrent = thiscurrent + 1;
                        slidefunc.slideMotion(thiscurrent);
                    }else if(thiscurrent >= maxSlide-1 && !setVal.loop){
                        window.clearInterval(sint);
                    }
                },intTime);
            },

            /***********************************************************************************         기본 CSS 셋팅          ************/
            setCss : function(e){
                $this.css({
                    'overflow' : 'hidden',
                    'display' : 'block',
                    'position' : 'relative'
                });

                $WrapUl.css({
                    'position': 'relative',
                    'height' : '100%',
                    '-webkit-touch-callout' : 'none',
                    '-webkit-user-select' : 'none',
                    '-khtml-user-select' : 'none',
                    '-moz-user-select' : 'none',
                    '-ms-user-select' : 'none',
                    'user-select' : 'none'
                });

                $WrapUl.find('>li').css({
                    'width' : defaultW,
                    'display': 'block',
                    'position': 'absolute'
                });
                //20201120 이주현 주석처리
                // .find('>img').css({
                //     'width' : '100%'
                // });
            //
            }
        };


        var makeDepth = {
            /***********************************************************************************         (옵션) 인디케이트 생성          ************/
            mkIndicate : function(e){
                $('<div class="indicate"></div>').appendTo($this).css({
                    'position': 'absolute'
                });
                for(var i = 0; i < maxSlide; i++){
                    $('<a href="#"></a>').appendTo($this.find('div.indicate'))
                    .on('click',function(e){
                        var thisIndex = $this.find('div.indicate').find('a').index($(this));
                        thiscurrent = thisIndex;
                        slidefunc.slideMotion(thiscurrent);
                        $(this).addClass('active').siblings().removeClass('active');
                        commonjs.eventClick(e);
                    });
                    $this.find('div.indicate').find('>a').eq(0).addClass('active');
                }
                //for
            },

            /***********************************************************************************         (옵션) 화살표 만들기          ************/
            mkArrow : function(e){
                if($this.find('a.arrowBtn.prev').length == 0)$('<a href="#"></a>').appendTo($this).addClass('arrowBtn prev');
                if($this.find('a.arrowBtn.next').length == 0)$('<a href="#"></a>').appendTo($this).addClass('arrowBtn next');
                $this.find('a.arrowBtn').each(function(BtnIndex){
                    var $thisBtn = $(this);
                    $thisBtn.on('click', function(e){
//                                console.log(thiscurrent)
                        if(!motioning){
                            if($thisBtn.hasClass('prev') && thiscurrent > -1){
                                if(!setVal.loop && thiscurrent == 0){
                                    //반복은 안함 설정 / 맨 처음까지 갔는데 이전 클릭했을 때
                                }else{
                                    thiscurrent = thiscurrent - 1;
                                    slidefunc.slideMotion(thiscurrent);
                                }
                            }else if($thisBtn.hasClass('prev') && thiscurrent == -1){
                                slidefunc.slideMotion(thiscurrent);
                            }
                            //if 이전 버튼 클릭

                            if($thisBtn.hasClass('next') && thiscurrent < maxSlide ){
                                if(!setVal.loop && thiscurrent == maxSlide-1){
                                    //반복은 안함 설정 / 맨 마지막까지 갔는데 다음 클릭했을 때
                                }else{
                                    thiscurrent = thiscurrent + 1;
                                    slidefunc.slideMotion(thiscurrent);
                                }
                            }else if($thisBtn.hasClass('next') && thiscurrent == maxSlide){
                                slidefunc.slideMotion(thiscurrent);
                            }
                            //if 다음 버튼 클릭
                        }
                        commonjs.eventClick(e);
                    });
                });
                //each
            }
        }


        /***********************************************************************************         리사이즈 모듈 실행          ************/
        commonjs.resizeModule(function(){
            defaultW = $this.width();
            Xposition = [];
            SimpleSlider.setCss();
            $WrapUl.find('>li').each(function(index){
                Xposition[index] = defaultW * index;
                $(this).css('left',Xposition[index]);
                if(setVal.loop && index == $WrapUl.find('>li').length-1)$(this).css('left',0-Xposition[1]);
            })
            //each
            motioning = false;
            $WrapUl.stop().css('left',-thiscurrent*defaultW);
            slideWrapH = Math.floor(defaultW*ratio-setVal.cut);
            $WrapUl.height(slideWrapH);
        });



        var slidefunc = {

            /***********************************************************************************         슬라이드 모션          ************/
            slideMotion : function(targetIndex){
                  // console.log('이동 ' + targetIndex)
                /* 이어지기 만드는 중 */
                var _ele = $WrapUl,
                    _elewidth = defaultW;
                if(targetIndex == maxSlide){
                    // 마지막 장면에서 처음으로 돌아가기 위한 모션
                    thiscurrent = 0;
                    motioning = true;
                    $WrapUl.find('>li').eq(thiscurrent).addClass('active').siblings().removeClass('active');
                    $WrapUl.stop().animate({
                        'left' : -(Xposition[maxSlide-1] + defaultW)
                    },setVal.speed,setVal.ease,function(){
                        _ele.css('left',0);
                        motioning = false;
                        dragging = false;
                    });
                }else if(targetIndex == -1){
                    thiscurrent = maxSlide-1;
                    motioning = true;
                    $WrapUl.find('>li').eq(thiscurrent).addClass('active').siblings().removeClass('active');
                    $WrapUl.stop().animate({
                        'left' : $WrapUl.find('li').eq(maxSlide-1).width()
                    },setVal.speed,setVal.ease,function(){
                        _ele.css('left',-Xposition[maxSlide-1]);
                        motioning = false;
                        dragging = false;
                    });
                }else{
                    motioning = true;
                    $WrapUl.find('>li').eq(thiscurrent).addClass('active').siblings().removeClass('active');
                    $WrapUl.stop().animate({
                        'left' : -targetIndex*_elewidth
                    },setVal.speed,setVal.ease,function(){
                        motioning = false;
                        dragging = false;
                    });
                }
                if(settings.indicate && maxSlide > 1){
                      $this.find('.indicate').find('>a').eq(thiscurrent).addClass('active').siblings().removeClass('active');
                }
                if(!setVal.loop && setVal.arrow && thiscurrent == 0){
                    $this.find('a.arrowBtn.prev').addClass('locked');
                }else if(!setVal.loop && setVal.arrow && thiscurrent > 0){
                    $this.find('a.arrowBtn.prev').removeClass('locked');
                }
                if(!setVal.loop && setVal.arrow && thiscurrent == maxSlide-1){
                    $this.find('a.arrowBtn.next').addClass('locked');
                }else if(!setVal.loop && setVal.arrow && thiscurrent < maxSlide-1){
                    $this.find('a.arrowBtn.next').removeClass('locked');
                }

            },
            //slideMotion

            /***********************************************************************************         드래깅 액션          ************/
            dragAction : function(event,num){
                keypress = false;
                window.clearInterval(sint);
                //터치했을 때는 인터벌이 멈춰야함

                if(num > 0){
                    //움직임이 + 발생한다면
                    if(setVal.loop || (!setVal.loop && thiscurrent < maxSlide-1)){
                        $WrapUl.css({
                            'left' : slideWrapX - num
                        });
                    }
                    commonjs.eventClick(event);
                }else if(num < 0){
                    //움직임이 - 발생한다면
                    if(setVal.loop || (!setVal.loop && thiscurrent > 0)){
                        $WrapUl.css({
                            'left' : slideWrapX - num
                        });
                    }
                    commonjs.eventClick(event);
                }else{
                    //commonjs.eventClick(event);
                }
                //if
            },
            /***********************************************************************************         드래그 끝          ************/
            dragEnd : function(num){
//                console.log('드래그엔드')
                $WrapUl.removeClass('keydown keymove');
                if(setVal.autoPlay){
                    SimpleSlider.autoSlide();
                };
                //인터벌 옵션이 되어있다면 다시 인터벌됨
                    if(num !== 0){
                        //이동발생 됨
                        if(num > $this.width()*0.1){
                            if(setVal.loop || (!setVal.loop && thiscurrent < maxSlide-1)){
                                dragging = false;
                                x_new = 0;
                                thiscurrent = thiscurrent + 1;
                                if(thiscurrent <= maxSlide && !motioning){
                                    //모셔닝중이 아니고, 현재 마지막이 아닌 상태
                                    slidefunc.slideMotion(thiscurrent);
                                }
                            }
                            //if 다음 버튼 클릭
                        }else if(num < $this.width()*-0.1){
                            if(setVal.loop || (!setVal.loop && thiscurrent > 0)){
                                dragging = false;
                                x_new = 0;
                                thiscurrent = thiscurrent - 1;
//                                console.log(thiscurrent)
                                if(thiscurrent >= -1 && !motioning){
                                    slidefunc.slideMotion(thiscurrent);
                                }
                            }
                        }else{
                            dragging = true;
                            x_new = 0;
                            slidefunc.slideMotion(thiscurrent);
                        }
                        //if

                    }
                    //if

                }
                //if
        };
        //var slidefunc

        /***********************************************************************************         터치 / 마우스 인터렉션          ************/
        function getHeightNum(){
            if(touchsupport){
                return 10;
            }else{
                return 100;
            }
        }
        function touchStartBind(event){

            var touch = touchsupport ? event.originalEvent.touches[0] || event.originalEvent.changedTouches[0] : event;
            var x_origine = touch.pageX;
            var y_origine = touch.pageY;

            if(!motioning && !dragging && maxSlide > 1){
                window.clearInterval(sint);
                $WrapUl.addClass('keydown');
                slideWrapX = $WrapUl.position().left;

                $this.bind(touchEvents.touchMove, function(event){
                    var touch2 = touchsupport ? event.originalEvent.touches[0] || event.originalEvent.changedTouches[0] : event;
                        x_new = x_origine - touch2.pageX;
                        y_new = y_origine - touch2.pageY;

                    if(Math.abs(y_new) > getHeightNum() || (Math.abs(x_new) < Math.abs(y_new))){
                        $this.trigger(touchEvents.touchEnd);
                        dragging = false;
                        slidefunc.dragEnd(x_new);
                    }else{
                        window.clearInterval(sint);
                        $WrapUl.addClass('keymove');
                        slidefunc.dragAction(event,x_new);
                        commonjs.eventClick(event);
                    }

                });
                //touchmove
            }
            //if
        }
        if(swipeslide){
            $this.bind(touchEvents.touchStart, function(event){
                touchStartBind(event);
            });

            $this.bind(touchEvents.touchEnd, function(event){
                $(document).unbind(touchEvents.touchMove);
                $this.unbind(touchEvents.touchMove);
                if(!dragging)slidefunc.dragEnd(x_new);
            });

            $(document).bind(touchEvents.touchEnd, function(event){
                if($WrapUl.hasClass('keymove')){
                    dragging = false;
                    slidefunc.dragEnd(x_new);
                    commonjs.eventClick(event);
                }else{
//                    console.log('끝')
                }
                $this.unbind(touchEvents.touchMove);
                $(document).unbind(touchEvents.touchMove);
            });

        }
        //swipeslide

        (function init(){
            SimpleSlider.mkSlide();
            //슬라이드 만들기

            SimpleSlider.sortSlide();
            //생성된 슬라이드 정렬

            if(maxSlide > 1 && setVal.loop)SimpleSlider.cloneslide();
            //이어지게 하려고 슬라이드의 앞과 뒤 엘리먼트를 복사해서 양쪽 반대쪽에 배치함

            SimpleSlider.setCss();

            if(setVal.arrow && maxSlide > 1)makeDepth.mkArrow();
            //(옵션) 화살표 만들기 실행

            if(setVal.indicate && maxSlide > 1)makeDepth.mkIndicate();
            //(옵션) 인디케이트 생성 실행

            if(setVal.autoPlay && maxSlide > 1){
                SimpleSlider.autoSlide();
                $this.hover(function(e){
                    window.clearInterval(sint);
                },function(e){
                    SimpleSlider.autoSlide();
                });
            }
            //(옵션) 자동 슬라이드 실행

            $WrapUl.find('>li').eq(0).addClass('active').siblings().removeClass('active');

            if(!setVal.loop)$this.find('a.arrowBtn.prev').addClass('locked');
        })();
    }

})(window.jQuery, window);
