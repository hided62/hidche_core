
$(document).ready( function () {
    $('#db_form').validate({
        rules:{
            db_host:"required",
            db_port:"required",
            db_id:"required",
            db_pw:"required",
            db_name:"required",
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
            url:'j_install_db.php',
            dataType:'json',
            data:{
                full_reset:$('#full_reset').val(),
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
                alert('DB.php가 생성되었습니다.');
                deferred.resolve();
            }

            return deferred.promise();
        }).then(function(){
            location.href = 'install.php';
        });
        
    });
});