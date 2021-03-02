$(document).ready(function() {
    // 글자 효과

    var pitchingContainer = $(".pitching_showcase")
    var businessContainer = $(".business_lounge")
    var sponsorContainer = $(".sponsor_text")
    var DMC_text_left_Container = $(".DMC_text_left")
    var DMC_text_right_Container = $(".DMC_text_right")


    pitchingContainer.textillate({
        in: {
            effect: 'fadeInLeft',
            reverse: false,
            shuffle: false,
            sync: false,
        }
    });

    businessContainer.textillate({
        in: {
            effect: 'fadeInLeft',
            reverse: false,
            shuffle: false,
            sync: false
        }
    });

    sponsorContainer.textillate({
        in: {
            effect: 'fadeInLeft',
            reverse: false,
            shuffle: false,
            sync: false,
        }
    });
    DMC_text_left_Container.textillate({
        in: {
            effect: 'fadeInLeft',
            delay: 80,
            reverse: false,
            shuffle: false,
            sync: false
        }
    });
    DMC_text_right_Container.textillate({
        in: {
            effect: 'fadeInLeft',
            delay: 80,
            reverse: false,
            shuffle: false,
            sync: false
        }
    });

    setInterval(function () {
        pitchingContainer.textillate('start');
        businessContainer.textillate('start');
        sponsorContainer.textillate('start');

    }, 8000);
    setInterval(function () {
        DMC_text_left_Container.textillate('start');
        DMC_text_right_Container.textillate('start');

    }, 3000);
});