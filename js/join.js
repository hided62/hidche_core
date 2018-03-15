$(document).ready( function () {
    $( "#main_form" ).validate( {
        rules: {
            username: {
                required: true,
                minlength: 4,
                maxlength: 64,
                remote: {
                    url: "j_check_dup.php",
                    type: "post",
                    data: {
                        type:'username',
                        'value':function(){
                            return $('#username').val();
                        }
                    },
                    dataType: 'json'
                }
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
                remote: {
                    url: "j_check_dup.php",
                    type: "post",
                    data: {
                        type:'nickname',
                        'value':function(){
                            return $('#nickname').val();
                        }
                    },
                    dataType: 'json'
                }
            },
            secret_agree: "required"
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
            },
            secret_agree: "동의해야만 가입하실 수 있습니다."
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
    } );

    $( "#main_form" ).submit(function(){
        var raw_password = $('#password').val();
        var salt = $('#global_salt').val();
        console.log(salt + raw_password + salt);
        var hash_pw = sha512(salt + raw_password + salt);

        $.post({
            url:'j_join_process.php',
            dataType:'json',
            data:{
                'secret_agree':$('#secret_agree').val(),
                'username':$('#username').val(),
                'password':hash_pw,
                'nickname':$('#nickname').val(),

            }
        }).then(function(obj){
            if(!obj.result){
                alert(obj.reason);
            }
            else{
                alert('정상적으로 가입되었습니다.');
            }

            window.location.href = '../';

        });
        console.log('Yes!');
        return false;
    });
} );


$(function($){
    $.get('terms.html').then(function(txt){
        $('#terms').html(txt);
    });
});