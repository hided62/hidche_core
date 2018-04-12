

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
            $('#global_salt').val(result.globalSalt);
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
            alert('설치 이후 DB 설정이 변경된 것 같습니다. RootDB.php 파일의 설정을 확인해주십시오.');
            return;
        }
        if(result.step == 'sql_fail'){
            $('#db_form_card').hide();
            $('#admin_form_card').hide();
            alert('DB가 제대로 설정되지 않았거나, 훼손된 것 같습니다. DB를 복구하거나 RootDB.php 파일을 삭제 후 재설치를 진행해 주십시오.');
            return;
        }

        alert('알 수 없는 오류' + result);

    });
}

$(document).ready( function () {
    changeInstallMode();
    var parentPathname = location.pathname.split('/').slice(0,-2).join('/');
    $('#serv_host').val(
        [location.protocol, '//', location.host, parentPathname].join('')
    );
    $('#db_form').validate({
        rules:{
            db_host:"required",
            db_port:"required",
            db_id:"required",
            db_pw:"required",
            db_name:"required",
            serv_host:"required",
            shared_icon_path:"required",
            game_image_path:"required"
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
    });
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
                db_name:$('#db_name').val(),
                serv_host:$('#serv_host').val(),
                shared_icon_path:$('#shared_icon_path').val(),
                game_image_path:$('#game_image_path').val()
            }
        }).then(function(result){
            var deferred = $.Deferred();

            if(!result.result){
                alert(result.reason);
                deferred.reject('fail');
            }
            else{
                alert('RootDB.php가 생성되었습니다. 관리자 계정 생성을 진행합니다.');
                deferred.resolve();
            }

            return deferred.promise();
        }).then(function(){
            changeInstallMode();
        });
        
    });

    $( "#admin_form" ).validate( {
        rules: {
            username: {
                required: true,
                minlength: 4,
                maxlength: 64,
            },
            password: {
                required: true,
                minlength: 6
            },
            confirm_password: {
                required: true,
                minlength: 6,
                equalTo: "#password"
            },
            nickname:{
                required: true,
                maxlength: 6,
            }
        },
        messages: {
            username: {
                required: "유저명을 입력해주세요",
                minlength: "{0}글자 이상 입력하셔야 합니다",
                maxlength: '{0}자를 넘을 수 없습니다'
            },
            password: {
                required: "비밀번호를 입력해주세요",
                minlength: "비밀번호는 적어도 {0}글자 이상이어야 합니다"
            },
            confirm_password: {
                required: "비밀번호를 입력해주세요",
                minlength: "비밀번호는 적어도 {0}글자 이상이어야 합니다",
                equalTo: "비밀번호가 일치하지 않습니다"
            },
            nickname: {
                required: "닉네임을 입력해주세요",
                maxlength: '닉네임은 {0}자를 넘을 수 없습니다'
            }
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
    });

    $('#admin_form').submit(function(e){
        e.preventDefault();
        if(!$("#admin_form").valid()){
            return;
        }

        var raw_password = $('#password').val();
        var salt = $('#global_salt').val();
        console.log(salt + raw_password + salt);
        var hash_pw = sha512(salt + raw_password + salt);

        $.ajax({
            cache:false,
            type:'post',
            url:'j_create_admin.php',
            dataType:'json',
            data:{
                username:$('#username').val(),
                password:hash_pw,
                nickname:$('#nickname').val()
            }
        }).then(function(result){
            var deferred = $.Deferred();

            if(!result.result){
                alert(result.reason);
                deferred.reject('fail');
            }
            else{
                deferred.resolve();
            }

            return deferred.promise();
        }).then(function(){
            changeInstallMode();
        });
        
    });
});