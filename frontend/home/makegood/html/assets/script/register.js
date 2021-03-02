/**
 * Created by Juhyun on 2020. 11. 28..
 */
var Register = function () {

    var newRegister = function () {
        $('#submitBtn').on('click',function(){
            var data = $('#registerForm').serializeObject();
            if(data['email'] == '' || data['name'] == '' ){
                alert("입력 해주세요")
                return false
            }
            if (emailChk(data['email']) == false) {
                alert("이메일형식이 올바르지 않습니다.");
                return false
            }
            if(data['phone'] != '' && (phoneChk(data['phone']) == false)){
                alert("연락처 형식이 올바르지 않습니다.")
                return false
            }
            if($('input:radio[name="acceptance"]').is(":checked") ==  false){
                alert("이메일 수신동의 체크 해주세요")
                return false
            }
            if($('input:checkbox[name="intrs"]').is(":checked") ==  false){
                alert("관심분야에 체크 해주세요")
                return false
            }
            if($('input:checkbox[name="inflowPath"]').is(":checked") ==  false ){
                alert("유입경로에 체크 해주세요.")
                return false
            }
            if($('input:checkbox[id="agree"]').is(":checked") == false){
                alert("개인정보 동의에 체크해주세요")
                return false
            } else{
                $.ajax({
                    url: "/api/newRegister",
                    type: "POST",
                    crossOrigin: true,
                    data: JSON.stringify(data),
                    contentType: 'application/json; charset=utf-8',
                    dataType: "json"
                }).success(function(res) {
                    if(res.result == 's'){
                        $.ajax({
                            url: "/api/send",
                            type: "POST",
                            crossOrigin: true,
                            data: JSON.stringify(data),
                            contentType: 'application/json;',
                            dataType: "json"
                        }).success(function(s) {
                            alert(s.result)
                            location.href = "/register"
                        })
                    }else{
                        alert(res.result)
                        location.href = "/register"
                    }

                }).fail(function(res){
                    alert("에러코드:"+res.status+"\n잠시뒤에 다시 시도해주세요.");
                });
            }

        });


    }
    var emailChk = function (email) {
        var exptext = /^[A-Za-z0-9_\.\-]+@[A-Za-z0-9\-]+\.[A-Za-z0-9\-]+/;
        if(exptext.test(email) == false) {
            return false;
        }
        return true;
    }
    var phoneChk = function (phoneNum) {
        var regExp =/^\d([0-9 -]{0,10}\d)?$/;
        var myArray;
        if(regExp.test(phoneNum)){
            return true;
        } else {
            return false;
        }
    }
    jQuery.fn.serializeObject = function() {
        var obj = null;
        try {
            if (this[0].tagName && this[0].tagName.toUpperCase() == "FORM") {
                var arr = this.serializeArray();
                console.log(arr)
                if (arr) {
                    obj = {};

                    jQuery.each(arr, function() {
                        if(obj.hasOwnProperty(this.name)){
                            console.log(obj[this.name])
                            if(this.name == 'intrs' && this.value =='기타'){
                                obj[this.name] = obj[this.name].concat(', ', $('#intrsEtcValue').val())
                            }
                            else if(this.name == 'inflowPath' && this.value =='기타'){
                                obj[this.name] = obj[this.name].concat(', ', $('#inflowPathEtcValue').val())
                            }
                            else{
                                obj[this.name] = obj[this.name].concat(', ', this.value)
                            }


                        }
                        else {
                            obj[this.name] = this.value;
                        }
                    });
                }//if ( arr ) {
            }
        } catch (e) {
            alert(e.message);
        } finally {
        }

        return obj;
    };
    return {

        init:function(){
            newRegister();

        }
    };

}();