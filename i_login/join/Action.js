var LoginJoin_idChecked = 0;
var LoginJoin_pidChecked = 0;
var LoginJoin_nameChecked = 0;
var LoginJoin_emailChecked = 0;
var LoginJoin_emailVerified = 0;

function LoginJoin_Import() {
}

function LoginJoin_Init() {
    $(".LoginJoin_back").click(LoginJoin_Back);
    $("#LoginJoin_000601").click(LoginJoin_CheckId);
    $("#LoginJoin_001402").click(LoginJoin_CheckPid);
    $("#LoginJoin_001701").click(LoginJoin_CheckName);
    $("#LoginJoin_002001").click(LoginJoin_CheckEmail);
    $("#LoginJoin_002300").click(LoginJoin_SendCode);
    $("#LoginJoin_002302").click(LoginJoin_VerifyCode);

    $("#LoginJoin_000600").keydown(LoginJoin_InvalidateId);
    $("#LoginJoin_001400").keydown(LoginJoin_InvalidatePid);
    $("#LoginJoin_001401").keydown(LoginJoin_InvalidatePid);
    $("#LoginJoin_001700").keydown(LoginJoin_InvalidateName);
    $("#LoginJoin_002000").keydown(LoginJoin_InvalidateEmail);

    $("#LoginJoin_002800").click(function() { LoginJoin_Join(); });
    $("#LoginJoin_002900").click(function() { LoginJoin_Update(); });
}

function LoginJoin_Update() {
    LoginJoin_UpdateRegisteredCount();

    $("#LoginJoin_000600").val("");
    $("#LoginJoin_000900").val("");
    $("#LoginJoin_001100").val("");
    $("#LoginJoin_001400").val("");
    $("#LoginJoin_001401").val("");
    $("#LoginJoin_001700").val("");
    $("#LoginJoin_002000").val("");
    $("#LoginJoin_002301").val("");
    $("#LoginJoin_002700").attr("checked", false);

    LoginJoin_InvalidateId();
    LoginJoin_InvalidatePid();
    LoginJoin_InvalidateName();
    LoginJoin_InvalidateEmail();
    LoginJoin_InvalidateCode();

    $("#LoginJoin_000600").focus();
}

function LoginJoin_UpdateRegisteredCount() {
    GetJSON(
        HOME+I+LOGIN+W+JOIN+W+GET, { },
        function(response, textStatus) {
            if(response.result == "SUCCESS") {
                $("#LoginJoin_000100").text(response.registeredCount);
            }
        }
    );
}

function LoginJoin_Back() {
    $("#LoginJoin_00").hide();

    $("#Login_00").show();
    Login_Update();
}

function LoginJoin_InvalidateId() {
    LoginJoin_EnableCheckId();
}

function LoginJoin_InvalidatePid() {
    LoginJoin_EnableCheckPid();
}

function LoginJoin_InvalidateName() {
    LoginJoin_EnableCheckName();
}

function LoginJoin_InvalidateEmail() {
    LoginJoin_EnableCheckEmail();
    LoginJoin_InvalidateCode();
}

function LoginJoin_InvalidateCode() {
    LoginJoin_DisableSendCode(0);
    LoginJoin_DisableVerifyCode(0);
    LoginJoin_emailVerified = 0;
}

function LoginJoin_CheckId() {
    LoginJoin_Check(0);
}

function LoginJoin_CheckPid() {
    LoginJoin_Check(1);
}

function LoginJoin_CheckName() {
    LoginJoin_Check(2);
}

function LoginJoin_CheckEmail() {
    LoginJoin_Check(3);
}

function LoginJoin_SendCode() {
    var email = $("#LoginJoin_002000").val();

    Popup_Wait(function() {
        PostJSON(
            HOME+I+LOGIN+W+JOIN+W+"sendCode"+POST, {
                email: email
            },
            function(response, textStatus) {
                Popup_WaitShow(response.msg, function() {
                    if(response.result == "SUCCESS") {
                        LoginJoin_DisableSendCode(1);
                        LoginJoin_EnableVerifyCode();
                        $("#LoginJoin_002301").val("");
                        $("#LoginJoin_002301").focus();
                    } else {
                    }
                })
            }
        )
    });
}

function LoginJoin_VerifyCode() {
    var email = $("#LoginJoin_002000").val();
    var code = $("#LoginJoin_002301").val();

    if(code.length < 6) {
        Popup_Alert("인증코드는 6자리입니다!", function() {
            $("#LoginJoin_002301").val("");
            $("#LoginJoin_002301").focus();
        });
        return false;
    } else if(code.length == 6) {
        Popup_Wait(function() {
            PostJSON(
                HOME+I+LOGIN+W+JOIN+W+"verifyCode"+POST, {
                    email: email,
                    code: code
                },
                function(response, textStatus) {
                    Popup_WaitShow(response.msg, function() {
                        if(response.result == "SUCCESS") {
                            LoginJoin_DisableVerifyCode(1);
                            LoginJoin_emailVerified = 1;
                        } else {
                            $("#LoginJoin_002301").val("");
                            $("#LoginJoin_002301").focus();
                        }
                    })
                }
            )
        });
    }
}

function LoginJoin_Check(type) {
    var id = $("#LoginJoin_000600").val();
    var pid1 = $("#LoginJoin_001400").val();
    var pid2 = $("#LoginJoin_001401").val();
    var name = $("#LoginJoin_001700").val();
    var email = $("#LoginJoin_002000").val();

    Popup_Wait(function() {
        PostJSON(
            HOME+I+LOGIN+W+JOIN+W+"check"+POST, {
                type: type,
                id: id,
                pid1: pid1,
                pid2: pid2,
                name: name,
                email: email
            },
            function(response, textStatus) {
                Popup_WaitShow(response.msg, function() {
                    if(response.result == "SUCCESS") {
                        switch(response.type) {
                            case "0":
                                LoginJoin_DisableCheckId();
                            break;
                            case "1":
                                LoginJoin_DisableCheckPid();
                            break;
                            case "2":
                                LoginJoin_DisableCheckName(response.name);
                            break;
                            case "3":
                                LoginJoin_DisableCheckEmail();
                                LoginJoin_EnableSendCode();
                            break;
                        }
                    }
                })
            }
        )
    });
}

function LoginJoin_Join() {
    if(LoginJoin_JoinTest() == false) {
        return;
    }

    var id = $("#LoginJoin_000600").val();
    var pw = $("#LoginJoin_000900").val();
    var token = $("#token_join").val();
    var pid1 = $("#LoginJoin_001400").val();
    var pid2 = $("#LoginJoin_001401").val();
    var name = $("#LoginJoin_001700").val();
    var email = $("#LoginJoin_002000").val();

    pw = hex_md5(pw+""+pw)+""+hex_md5(token);

    Popup_Wait(function() {
        PostJSON(
            HOME+I+LOGIN+W+JOIN+W+POST, {
                id: id,
                pw: pw,
                pid1: pid1,
                pid2: pid2,
                name: name,
                email: email
            },
            function(response, textStatus) {
                Popup_WaitShow(response.msg, function() {
                    if(response.result == "SUCCESS") {
                        LoginJoin_Back();
                    } else {
                        LoginJoin_Update();
                    }
                });
            }
        )
    });
}

function LoginJoin_JoinTest() {
    if($("#LoginJoin_002700").is(":checked") == false) {
        Popup_Alert("주의사항을 읽으시고 동의하시는 분만 삼모전을 사용하실수 있습니다!");
        return false;
    }

    if(LoginJoin_idChecked + LoginJoin_pidChecked + LoginJoin_nameChecked + LoginJoin_emailChecked + LoginJoin_emailVerified < 5) {
        Popup_Alert("5가지 확인을 해주세요!");
        return false;
    }

    var pw1 = $("#LoginJoin_000900").val();
    var pw2 = $("#LoginJoin_001100").val();

    if(pw1.length < 4 || pw1.length > 12) {
        Popup_Alert("비밀번호 길이가 부적합합니다!");
        return false;
    }
    if(pw1 != pw2) {
        Popup_Alert("비밀번호가 일치하지 않습니다!");
        return false;
    }
    return true;
}

function LoginJoin_EnableCheckId() {
    LoginJoin_idChecked = 0;
    $("#LoginJoin_000601").val("아이디 중복 확인");
    $("#LoginJoin_000601").removeAttr("disabled");
    $("#LoginJoin_000601").css("color", "white");
}

function LoginJoin_DisableCheckId() {
    LoginJoin_idChecked = 1;
    $("#LoginJoin_000601").val("확인 완료");
    $("#LoginJoin_000601").attr("disabled", true);
    $("#LoginJoin_000601").css("color", "gray");
}

function LoginJoin_EnableCheckPid() {
    LoginJoin_pidChecked = 0;
    $("#LoginJoin_001402").val("생년월일 확인");
    $("#LoginJoin_001402").removeAttr("disabled");
    $("#LoginJoin_001402").css("color", "white");
}

function LoginJoin_DisableCheckPid() {
    LoginJoin_pidChecked = 1;
    $("#LoginJoin_001402").val("확인 완료");
    $("#LoginJoin_001402").attr("disabled", true);
    $("#LoginJoin_001402").css("color", "gray");
}

function LoginJoin_EnableCheckName() {
    LoginJoin_nameChecked = 0;
    $("#LoginJoin_001701").val("닉네임 중복 확인");
    $("#LoginJoin_001701").removeAttr("disabled");
    $("#LoginJoin_001701").css("color", "white");
}

function LoginJoin_DisableCheckName(name) {
    LoginJoin_nameChecked = 1;
    $("#LoginJoin_001701").val("확인 완료");
    $("#LoginJoin_001701").attr("disabled", true);
    $("#LoginJoin_001701").css("color", "gray");
    $("#LoginJoin_001700").val(name);
}

function LoginJoin_EnableCheckEmail() {
    LoginJoin_emailChecked = 0;
    $("#LoginJoin_002001").val("이메일 중복 확인");
    $("#LoginJoin_002001").removeAttr("disabled");
    $("#LoginJoin_002001").css("color", "white");
}

function LoginJoin_DisableCheckEmail() {
    LoginJoin_emailChecked = 1;
    $("#LoginJoin_002001").val("확인 완료");
    $("#LoginJoin_002001").attr("disabled", true);
    $("#LoginJoin_002001").css("color", "gray");
}

function LoginJoin_EnableSendCode() {
    $("#LoginJoin_002300").val("인증번호 전송");
    $("#LoginJoin_002300").removeAttr("disabled");
    $("#LoginJoin_002300").css("color", "white");
}

function LoginJoin_DisableSendCode(type) {
    if(type == 0) {
        $("#LoginJoin_002300").val("인증번호 전송");
    } else {
        $("#LoginJoin_002300").val("전송 완료");
    }
    $("#LoginJoin_002300").attr("disabled", true);
    $("#LoginJoin_002300").css("color", "gray");
}

function LoginJoin_EnableVerifyCode() {
    $("#LoginJoin_002302").val("인증번호 확인");
    $("#LoginJoin_002302").removeAttr("disabled");
    $("#LoginJoin_002302").css("color", "white");
}

function LoginJoin_DisableVerifyCode(type) {
    if(type == 0) {
        $("#LoginJoin_002302").val("인증번호 확인");
    } else {
        $("#LoginJoin_002302").val("확인 완료");
    }
    $("#LoginJoin_002302").attr("disabled", true);
    $("#LoginJoin_002302").css("color", "gray");
}
