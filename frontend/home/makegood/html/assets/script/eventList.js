/**
 * Created by Juhyun on 2020. 11. 28..
 */
var EventList = function () {

    var destroyTable= function () {
        if ($.fn.DataTable.isDataTable('#page-length-option')) {
            $('#page-length-option').DataTable().destroy();
        }
    }


    var initTable = function () {
        $.ajax({
            url: "/api/getAllEvent",
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
                    { "data": "nickname" },
                    { "data": "email" },
                    { "data": "comment"}
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
                    $('#nickname').val(rowData[0].nickname)
                    $('#email').val(rowData[0].email)
                    $('#comment').append(rowData[0].comment)
                    $('#created_at').text(rowData[0].created_at)
                    $('#detailEvent').attr('data-index', rowData[0].id);
                    $('#detailEvent').show();


                } )

        }).fail(function(res){
            alert("에러코드:"+res.status+"\n잠시뒤에 다시 시도해주세요.");
        });
    }
    var updateData = function (id) {
        var data = $('#detailInfo').serializeObject();
        console.log('update')
        $.ajax({
            url: "/api/updateEvent/"+id,
            type: "PUT",
            crossOrigin: true,
            data: JSON.stringify(data),
            contentType: 'application/json; charset=utf-8',
            dataType: "json"
        }).success(function(res) {
            if(res = 's'){
                alert("수정이 완료 되었습니다")
                location.href = "/eventList"
            }

        }).fail(function(res){
            alert("에러코드:"+res.status+"\n잠시뒤에 다시 시도해주세요.");
        });
    }

    var deleteData = function (id) {
        $.ajax({
            url: "/api/deleteEvent/"+id,
            type: "delete",
            crossOrigin: true,
            contentType: 'application/json; charset=utf-8',
            dataType: "json"
        }).success(function(res) {
            if(res = 's'){
                alert("삭제가 완료 되었습니다")
                location.href = "/eventList"
            }

        }).fail(function(res){
            alert("에러코드:"+res.status+"\n잠시뒤에 다시 시도해주세요.");
        });
    }


    var btnFunc = function () {
        $(".close").on('click', function () {
            $('#detailEvent').hide();
            $('#detailInfo').each(function(){
                this.reset();
            });
        })
        $("#delBtn1").on('click', function () {
            $('#deleteChk').show();
            $("#delBtn2").on('click', function () {
                deleteData($('#detailEvent').attr('data-index'))
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
                    updateData($('#detailEvent').attr('data-index'))
                })
                $("#updateClose").on('click', function () {
                    $('#updateChk').hide();
                })

            }
            else{
                $('#detailEvent').hide();
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