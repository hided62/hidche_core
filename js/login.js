$(document).ready(function() {
    $("#main_form").validate({
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
        errorPlacement: function(error, element) {
            // Add the `help-block` class to the error element
            error.addClass("invalid-feedback");

            if (element.prop("type") === "checkbox") {
                error.insertAfter(element.parent("label"));
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        }
    });

    $("#main_form").submit(function() {
        var raw_password = $('#password').val();
        var salt = $('#global_salt').val();
        var hash_pw = sha512(salt + raw_password + salt);

        $.post({
            url: 'j_login.php',
            dataType: 'json',
            data: {
                'username': $('#username').val(),
                'password': hash_pw
            }
        }).then(function(obj) {
            if (obj.result) {
                window.location.href = "./";
                return;
            }
            if (!obj.reqOTP) {
                alert(obj.reason);
                return;
            }

            var $modal = $('#modalOTP').modal();
            $modal.on('shown.bs.modal', function() {
                $('#otp_code').focus();
            });


        });
        return false;
    });

    $('#otp_form').submit(function() {
        $.post({
            url: 'oauth_kakao/j_check_OTP.php',
            dataType: 'json',
            data: {
                'otp': $('#otp_code').val(),
            }
        }).then(function(obj) {
            if (obj.result) {
                alert(obj.reason);
                window.location.href = "./";
                return;
            }

            alert(obj.reason);

            if (obj.reset) {
                $('#modalOTP').modal('hide')
                return;
            }
        });
        return false;
    });


    if (document.body.clientWidth < 700) {
        var targetWidth = document.body.clientWidth * 0.9;
        var scale = targetWidth / 700;
        var $map = $('#running_map');
        $map.find('.col').css('max-width', targetWidth);
        $map.find('.card').css('width', targetWidth);
        $map.find('.map-container').css({
            'transform-origin': 'top left',
            'transform': 'scale({0}, {0})'.format(scale),
            'height': 500 * scale,
        });
        $map.find('.map_body').data('scale', scale);
    }

    reloadWorldMap({
        targetJson: "{0}/j_map_recent.php".format(runningServer.name),
        reqType: 'get',
        dynamicMapTheme: true,
        callback: function(data, rawObject) {
            $('#running_map .card-body').html(rawObject.history);
        }
    });
});