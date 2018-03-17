

function changeInstallMode(){
    $.ajax({
        cache:false,
        type:'post',
        url:'j_install_status.php',
        dataType:'json',
    }).then(function(result){
        if(result.step == 'config'){
            $('#db_form_card').show();
            $('#admin_form_card').hide();
            return;
        }
        if(result.step == 'admin'){
            $('#db_form_card').hide();
            $('#admin_form_card').show();
            return;
        }
        if(result.step == 'done'){
            alert('설치가 완료되었습니다.');
            window.location.href = "..";
            return;
        }
        if(result.step == 'conn_fail'){
            $('#db_form_card').hide();
            $('#admin_form_card').hide();
            alert('설치 이후 DB 설정이 변경된 것 같습니다. conf.php 파일의 설정을 확인해주십시오.');
            return;
        }
        if(result.step == 'sql_fail'){
            $('#db_form_card').hide();
            $('#admin_form_card').hide();
            alert('DB가 제대로 설정되지 않았거나, 훼손된 것 같습니다. DB를 복구하거나 conf.php 파일을 삭제 후 재설치를 진행해 주십시오.');
            return;
        }

    });
}

$(document).ready( function () {
    changeInstallMode();

    $('#db_form').validate({
        rules:{
            db_host:"required",
            db_port:"required",
            db_id:"required",
            db_pw:"required",
            db_name:"required"
        },
        errorElement: "div",
        errorPlacement: function ( error, element ) {
            // Add the `help-block` class to the error element
            error.addClass( "invalid-feedback" );

            if ( element.prop( "type" ) === "checkbox" ) {
                error.insertAfter( element.parent( "label" ) );
            } else {
                error.insertAfter( element );
            }
        },
        highlight: function ( element, errorClass, validClass ) {
            $( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
        },
        unhighlight: function (element, errorClass, validClass) {
            $( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
        }
    })
    $('#db_form').submit(function(e){
        e.preventDefault();
        if(!$("#db_form").valid()){
            return;
        }
        $.ajax({
            cache:false,
            type:'post',
            url:'j_setup_db.php',
            dataType:'json',
            data:{
                db_host:$('#db_host').val(),
                db_port:$('#db_port').val(),
                db_id:$('#db_id').val(),
                db_pw:$('#db_pw').val(),
                db_name:$('#db_name').val()
            }
        }).then(function(result){
            var deferred = $.Deferred();

            if(!result.result){
                alert(result.reason);
                deferred.reject('fail');
            }
            else{
                alert('conf.php가 생성되었습니다. 관리자 계정 생성을 진행합니다.');
                deferred.resolve();
            }

            return deferred.promise();
        }).then(function(){
            changeInstallMode();
        });
        
    });
});