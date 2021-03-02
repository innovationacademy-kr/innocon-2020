/**
 * Created by Juhyun on 2020. 11. 28..
 */
var Login = function () {

    var Login = function () {
        $('#submitBtn').on('click',function(){
            var data = $('#loginForm').serialize()
            console.log(data)
            $.ajax({
                url: "/api/login",
                "type": "POST",
                crossOrigin: true,
                "data": data,
                dataType: "json"
            }).done(function(res) {
                var response = JSON.parse(res);
                if(response.login==true){
                    sessionStorage.setItem("loginStatus",true);
                    sessionStorage.setItem("userId",response.userId);
                    location.href = "/list"
                }else{
                    alert(response.message);
                }
            }).fail(function(res){
                alert("에러코드:"+res.status+"\n잠시뒤에 다시 시도해주세요.");
            });

        });
    }
    var Logout = function () {
        $('#logoutBtn').on('click',function(){
            $.ajax({
                url: "/api/logOut/"+ sessionStorage.getItem("userId"),
                "type": "POST",
                dataType: "json"
            }).success(function(res) {
                if(res = "s"){
                    sessionStorage.clear();
                    location.href = "/admin"
                }else{
                    alert(res.message);
                }
            }).fail(function(res){
                alert("에러코드:"+res.status+"\n잠시뒤에 다시 시도해주세요.");
            });

        });
    }
    return {

        init:function(){
            Login();
            Logout();

        }
    };

}();