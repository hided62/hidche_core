$(document).ready( function () {
    $( "#main_form" ).validate( {
        rules: {
            username: {
                required: true
            },
            password: {
                required: true,
            }
        },
        messages: {
            username: {
                required: "유저명을 입력해주세요"
            },
            password: {
                required: "비밀번호를 입력해주세요"
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
    } );

    $( "#main_form" ).submit(function(){
        var raw_password = $('#password').val();
        var salt = $('#global_salt').val();
        var hash_pw = sha512(salt + raw_password + salt);

        $.post({
            url:'i_login/j_login.php',
            dataType:'json',
            data:{
                'username':$('#username').val(),
                'password':hash_pw
            }
        }).then(function(obj){
            if(!obj.result){
                alert(obj.reason);
            }
            else{
                window.location.href = 'i_entrance/entrance.php';
            }

        });
        return false;
    });
} );