/**
 * Created by Juhyun on 2020. 11. 28..
 */
var Event = function () {

    var newEventComments = function () {
        $('#commentSubmitBtn').on('click',function(){
            var data = $('#eventForm').serializeObject();
            console.log(data)
            if(data['nickname'] == '' ){
                alert("닉네임을 입력 해주세요")
                return false
            }
            if (emailChk(data['email']) == false) {
                alert("이메일형식이 올바르지 않습니다.");
                return false
            }
            if(data['comment'] == ""){
                alert("소감 한마디 작성해주세요.")
                return false

            } else{
                $.ajax({
                    url: "/api/newEvent",
                    type: "POST",
                    crossOrigin: true,
                    data: JSON.stringify(data),
                    contentType: 'application/json; charset=utf-8',
                    dataType: "json"
                }).success(function(res) {
                    if(res.result == 's'){
                        alert("댓글 작성이 완료되었습니다.")
                        location.href = "/event"
                    }else{
                        alert(res.result)
                        location.href = "/event"
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
    return {

        init:function(){
            newEventComments();

        }
    };

}();