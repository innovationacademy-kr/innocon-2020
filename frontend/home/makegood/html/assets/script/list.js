/**
 * Created by Juhyun on 2020. 11. 28..
 */
var List = function () {

    var destroyTable= function () {
        if ($.fn.DataTable.isDataTable('#page-length-option')) {
            $('#page-length-option').DataTable().destroy();
        }
    }


    var initTable = function () {
        $.ajax({
            url: "/api/getAllRegister",
            type: "GET",
            contentType: 'application/json; charset=utf-8',
            dataType: "json"
        }).success(function(res) {
            var table = $('#page-length-option').DataTable( {
                "responsive": true,
                "data": res.data,
                "select": true,
                "columns": [
                    { "data": "No.", "sortable": true,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { "data": "email" },
                    { "data": "name" },
                    { "data": "phone" },
                    { "data": "affiliation" },
                    { "data": "intrs" },
                    { "data": "inflowPath" },
                    { "data": "acceptance",
                        render: function (data, type, row, meta) {
                            if(data){
                                return "O"
                            }else{
                                return "X"
                            }
                        }
                    },
                    { "data": "sendSubscription",
                        render: function (data, type, row, meta) {
                            if(data){
                                return "O"
                            }else{
                                return "X"
                            }
                        }
                    }

                ],
                "columnDefs": [{
                    "searchable": false,
                    "orderable": false,
                    "targets": 0
                }],
                "order": [[
                    0, 'desc'
                ]],
                "createdRow": function( row, data, dataIndex ) {
                    $(row).attr("id" , data.id );
                    var td = $(row).find("td:first");
                    td.removeClass('details-control');
                }
            } );

            table
                .on( 'select', function ( e, dt, type, indexes ) {
                    var rowData = table.rows( indexes ).data().toArray();
                    $('#name').val(rowData[0].name)
                    $('#email').val(rowData[0].email)
                    if(rowData[0].acceptance){
                        $("input:radio[name='acceptance']:radio[value=1]").prop('checked', true);
                    }else{
                        $("input:radio[name='acceptance']:radio[value=0]").prop('checked', true);
                    }
                    $('#phone').val(rowData[0].phone)
                    $('#affiliation').val(rowData[0].affiliation)
                    $('#qna').val(rowData[0].qna)
                    chkBox("intrs", String(rowData[0].intrs).split(", "))
                    chkBox("inflowPath", String(rowData[0].inflowPath).split(", "))
                    $('#created_at').text(rowData[0].created_at)
                    if(rowData[0].updated_at != "None"){
                        $('#updated_at').text(rowData[0].updated_at)
                        $('.updateDiv').show();
                    }
                    $('#detailUser').attr('data-index', rowData[0].id);
                    $('#detailUser').show();


                } )

        }).fail(function(res){
            alert("에러코드:"+res.status+"\n잠시뒤에 다시 시도해주세요.");
        });
    }
    var updateData = function (id) {
        var data = $('#detailInfo').serializeObject();
        console.log('update')
        $.ajax({
            url: "/api/updateReg/"+id,
            type: "PUT",
            crossOrigin: true,
            data: JSON.stringify(data),
            contentType: 'application/json; charset=utf-8',
            dataType: "json"
        }).success(function(res) {
            if(res = 's'){
                alert("수정이 완료 되었습니다")
                location.href = "/list"
            }

        }).fail(function(res){
            alert("에러코드:"+res.status+"\n잠시뒤에 다시 시도해주세요.");
        });
    }

    var deleteData = function (id) {
        $.ajax({
            url: "/api/deleteReg/"+id,
            type: "delete",
            crossOrigin: true,
            contentType: 'application/json; charset=utf-8',
            dataType: "json"
        }).success(function(res) {
            if(res = 's'){
                alert("삭제가 완료 되었습니다")
                location.href = "/list"
            }

        }).fail(function(res){
            alert("에러코드:"+res.status+"\n잠시뒤에 다시 시도해주세요.");
        });
    }

    var chkBox = function (id ,list) {


        $('#'+id+' [value="'+list.join('"],[value="')+'"]').prop('checked',true);
        if(!(list.length == $('input:checkbox[name='+id+']:checked').length)){
            $('#'+id+' [value="기타"]').prop('checked',true);
            if(id == 'intrs'){
                $("input[name=intrs]:checked").each(function() {
                    var test = $(this).val();
                    if(!(list.includes(test))){
                        $('#intrsEtcValue').val(list[list.length-1])
                    }
                })
            }
            if(id == 'inflowPath'){
                $("input[name=inflowPath]:checked").each(function() {
                    var test = $(this).val();
                    console.log(test)
                    console.log(list)
                    if(list.includes(test) == false){
                        $('#inflowPathEtcValue').val(list[list.length-1])
                    }
                })
            }
        }

    }
    var btnFunc = function () {
        $(".close").on('click', function () {
            $('#detailUser').hide();
            $('#detailInfo').each(function(){
                this.reset();
            });
        })
        $("#delBtn1").on('click', function () {
            $('#deleteChk').show();
            $("#delBtn2").on('click', function () {
                deleteData($('#detailUser').attr('data-index'))
            })
            $("#deleteClose").on('click', function () {
                $('#deleteChk').hide();
            })
        })
        $("form :input").change(function() {
            $(this).closest('form').data('changed', true);
        });
        $('#submit').click(function() {
            if($('#detailInfo').data('changed')) {
                $('#updateChk').show();
                $("#updateBtn").on('click', function () {
                    updateData($('#detailUser').attr('data-index'))
                })
                $("#updateClose").on('click', function () {
                    $('#updateChk').hide();
                })

            }
            else{
                $('#detailUser').hide();
                $('#detailInfo').each(function(){
                    this.reset();
                });
            }
        });

    }


    return {

        init:function(){
            destroyTable();
            initTable();
            btnFunc();

        }
    };

}();