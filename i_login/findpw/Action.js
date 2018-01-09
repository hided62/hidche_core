var LoginFindpw_idChecked = 0;
var LoginFindpw_pidChecked = 0;
var LoginFindpw_emailChecked = 0;
var LoginFindpw_emailVerified = 0;

function LoginFindpw_Import() {
}

function LoginFindpw_Init() {
    $(".LoginFindpw_back").click(LoginFindpw_Back);
    $("#LoginFindpw_000601").click(LoginFindpw_CheckId);
    $("#LoginFindpw_001402").click(LoginFindpw_CheckPid);
    $("#LoginFindpw_002001").click(LoginFindpw_CheckEmail);
    $("#LoginFindpw_002300").click(LoginFindpw_SendCode);
    $("#LoginFindpw_002302").click(LoginFindpw_VerifyCode);

    $("#LoginFindpw_000600").keydown(LoginFindpw_InvalidateId);
    $("#LoginFindpw_001400").keydown(LoginFindpw_InvalidatePid);
    $("#LoginFindpw_001401").keydown(LoginFindpw_InvalidatePid);
    $("#LoginFindpw_002000").keydown(LoginFindpw_InvalidateEmail);

    $("#LoginFindpw_002800").click(function() { LoginFindpw_Findpw(); });
    $("#LoginFindpw_002900").click(function() { LoginFindpw_Update(); });
}

function LoginFindpw_Update() {
    LoginFindpw_UpdateRegisteredCount();

    $("#LoginFindpw_000600").val("");
    $("#LoginFindpw_001400").val("");
    $("#LoginFindpw_001401").val("");
    $("#LoginFindpw_002000").val("");
    $("#LoginFindpw_002301").val("");

    LoginFindpw_InvalidateId();
    LoginFindpw_InvalidatePid();
    LoginFindpw_InvalidateEmail();
    LoginFindpw_InvalidateCode();

    $("#LoginFindpw_000600").focus();
}

function LoginFindpw_UpdateRegisteredCount() {
    GetJSON(
        HOME+I+LOGIN+W+FINDPW+W+GET, { },
        function(response, textStatus) {
            if(response.result == "SUCCESS") {
                $("#LoginFindpw_000100").text(response.registeredCount);
            }
        }
    );
}

function LoginFindpw_Back() {
    $("#LoginFindpw_00").hide();

    $("#Login_00").show();
    Login_Update();
}

function LoginFindpw_InvalidateId() {
    LoginFindpw_EnableCheckId();
}

function LoginFindpw_InvalidatePid() {
    LoginFindpw_EnableCheckPid();
}

function LoginFindpw_InvalidateEmail() {
    LoginFindpw_EnableCheckEmail();
    LoginFindpw_InvalidateCode();
}

function LoginFindpw_InvalidateCode() {
    LoginFindpw_DisableSendCode(0);
    LoginFindpw_DisableVerifyCode(0);
    LoginFindpw_emailVerified = 0;
}

function LoginFindpw_CheckId() {
    LoginFindpw_Check(0);
}

function LoginFindpw_CheckPid() {
    LoginFindpw_Check(1);
}

function LoginFindpw_CheckEmail() {
    LoginFindpw_Check(2);
}

function LoginFindpw_SendCode() {
    var email = $("#LoginFindpw_002000").val();

    Popup_Wait(function() {
        PostJSON(
            HOME+I+LOGIN+W+FINDPW+W+"sendCode"+POST, {
                email: email
            },
            function(response, textStatus) {
                Popup_WaitShow(response.msg, function() {
                    if(response.result == "SUCCESS") {
                        LoginFindpw_DisableSendCode(1);
                        LoginFindpw_EnableVerifyCode();
                        $("#LoginFindpw_002301").val("");
                        $("#LoginFindpw_002301").focus();
                    } else {
                    }
                })
            }
        )
    });
}

function LoginFindpw_VerifyCode() {
    var email = $("#LoginFindpw_002000").val();
    var code = $("#LoginFindpw_002301").val();

    if(code.length < 6) {
        Popup_Alert("인증코드는 6자리입니다!", function() {
            $("#LoginFindpw_002301").val("");
            $("#LoginFindpw_002301").focus();
        });
        return false;
    } else if(code.length == 6) {
        Popup_Wait(function() {
            PostJSON(
                HOME+I+LOGIN+W+FINDPW+W+"verifyCode"+POST, {
                    email: email,
                    code: code
                },
                function(response, textStatus) {
                    Popup_WaitShow(response.msg, function() {
                        if(response.result == "SUCCESS") {
                            LoginFindpw_DisableVerifyCode(1);
                            LoginFindpw_emailVerified = 1;
                        } else {
                            $("#LoginFindpw_002301").val("");
                            $("#LoginFindpw_002301").focus();
                        }
                    })
                }
            )
        });
    }
}

function LoginFindpw_Check(type) {
    var id = $("#LoginFindpw_000600").val();
    var pid1 = $("#LoginFindpw_001400").val();
    var pid2 = $("#LoginFindpw_001401").val();
    var email = $("#LoginFindpw_002000").val();

    Popup_Wait(function() {
        PostJSON(
            HOME+I+LOGIN+W+FINDPW+W+"check"+POST, {
                type: type,
                id: id,
                pid1: pid1,
                pid2: pid2,
                email: email
            },
            function(response, textStatus) {
                Popup_WaitShow(response.msg, function() {
                    if(response.result == "SUCCESS") {
                        switch(response.type) {
                            case "0":
                                LoginFindpw_DisableCheckId();
                            break;
                            case "1":
                                LoginFindpw_DisableCheckPid();
                            break;
                            case "2":
                                LoginFindpw_DisableCheckEmail();
                                LoginFindpw_EnableSendCode();
                            break;
                        }
                    }
                })
            }
        )
    });
}

function LoginFindpw_Findpw() {
    if(LoginFindpw_FindpwTest() == false) {
        return;
    }

    var id = $("#LoginFindpw_000600").val();
    var pid1 = $("#LoginFindpw_001400").val();
    var pid2 = $("#LoginFindpw_001401").val();
    var email = $("#LoginFindpw_002000").val();

    Popup_Wait(function() {
        PostJSON(
            HOME+I+LOGIN+W+FINDPW+W+POST, {
                id: id,
                pid1: pid1,
                pid2: pid2,
                name: name,
                email: email
            },
            function(response, textStatus) {
                Popup_WaitShow(response.msg, function() {
                    if(response.result == "SUCCESS") {
                        LoginFindpw_Back();
                    } else {
                        LoginFindpw_Update();
                    }
                });
            }
        )
    });
}

function LoginFindpw_FindpwTest() {
    if(LoginFindpw_idChecked + LoginFindpw_pidChecked + LoginFindpw_emailChecked + LoginFindpw_emailVerified < 4) {
        Popup_Alert("4가지 확인을 해주세요!");
        return false;
    }
    return true;
}

function LoginFindpw_EnableCheckId() {
    LoginFindpw_idChecked = 0;
    $("#LoginFindpw_000601").val("아이디 존재 확인");
    $("#LoginFindpw_000601").removeAttr("disabled");
    $("#LoginFindpw_000601").css("color", "white");
}

function LoginFindpw_DisableCheckId() {
    LoginFindpw_idChecked = 1;
    $("#LoginFindpw_000601").val("확인 완료");
    $("#LoginFindpw_000601").attr("disabled", true);
    $("#LoginFindpw_000601").css("color", "gray");
}

function LoginFindpw_EnableCheckPid() {
    LoginFindpw_pidChecked = 0;
    $("#LoginFindpw_001402").val("생년월일 존재 확인");
    $("#LoginFindpw_001402").removeAttr("disabled");
    $("#LoginFindpw_001402").css("color", "white");
}

function LoginFindpw_DisableCheckPid() {
    LoginFindpw_pidChecked = 1;
    $("#LoginFindpw_001402").val("확인 완료");
    $("#LoginFindpw_001402").attr("disabled", true);
    $("#LoginFindpw_001402").css("color", "gray");
}

function LoginFindpw_EnableCheckEmail() {
    LoginFindpw_emailChecked = 0;
    $("#LoginFindpw_002001").val("이메일 존재 확인");
    $("#LoginFindpw_002001").removeAttr("disabled");
    $("#LoginFindpw_002001").css("color", "white");
}

function LoginFindpw_DisableCheckEmail() {
    LoginFindpw_emailChecked = 1;
    $("#LoginFindpw_002001").val("확인 완료");
    $("#LoginFindpw_002001").attr("disabled", true);
    $("#LoginFindpw_002001").css("color", "gray");
}

function LoginFindpw_EnableSendCode() {
    $("#LoginFindpw_002300").val("인증번호 전송");
    $("#LoginFindpw_002300").removeAttr("disabled");
    $("#LoginFindpw_002300").css("color", "white");
}

function LoginFindpw_DisableSendCode(type) {
    if(type == 0) {
        $("#LoginFindpw_002300").val("인증번호 전송");
    } else {
        $("#LoginFindpw_002300").val("전송 완료");
    }
    $("#LoginFindpw_002300").attr("disabled", true);
    $("#LoginFindpw_002300").css("color", "gray");
}

function LoginFindpw_EnableVerifyCode() {
    $("#LoginFindpw_002302").val("인증번호 확인");
    $("#LoginFindpw_002302").removeAttr("disabled");
    $("#LoginFindpw_002302").css("color", "white");
}

function LoginFindpw_DisableVerifyCode(type) {
    if(type == 0) {
        $("#LoginFindpw_002302").val("인증번호 확인");
    } else {
        $("#LoginFindpw_002302").val("확인 완료");
    }
    $("#LoginFindpw_002302").attr("disabled", true);
    $("#LoginFindpw_002302").css("color", "gray");
}
