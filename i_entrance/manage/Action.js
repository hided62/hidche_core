function EntranceManage_Import() {
}

function EntranceManage_Init() {
    $("#EntranceManage_0001").click(EntranceManage_Back);
    $("#EntranceManage_000603").click(EntranceManage_ChangePw);
    $("#EntranceManage_001600").attr("disabled", "true");
    $("#EntranceManage_001601").change(EntranceManage_SelectIcon);
    $("#EntranceManage_001602").click(EntranceManage_ChangeIcon);
    $("#EntranceManage_001603").click(EntranceManage_DeleteIcon);
    $("#EntranceManage_0019").click(EntranceManage_Quit);

    if($.browser.mozilla == true) {
        $("#EntranceManage_001601").css("left", "10px");
    } else {
        $("#EntranceManage_001600").show();
    }
}

function EntranceManage_Update() {
    Popup_Wait(function() {
        PostJSON(
            HOME+I+ENTRANCE+W+MANAGE+W+GET, {
            },
            function(response, textStatus) {
                if(response.result == "SUCCESS") {
                    EntranceManage_UpdateInfo(response);
                    Popup_WaitHide();
                } else {
                    Popup_WaitShow("정보 로드 실패!");
                }
            }
        )
    });
}

function EntranceManage_Back() {
    $("#EntranceManage_00").hide();
    $("#Entrance_00").show();
    Entrance_Update();
}

function EntranceManage_SelectIcon() {
    $("#EntranceManage_001600").val($("#EntranceManage_001601").val());
}

function EntranceManage_UpdateInfo(member) {
    $("#EntranceManage_0004").text(member.id);
    $("#EntranceManage_0008").text(member.name);
    $("#EntranceManage_0010").text(member.grade);
    $("#EntranceManage_001500").attr("src", member.picture0);
    $("#EntranceManage_001501").attr("src", member.picture1);
    $("#EntranceManage_0020").html(member.donation);
}

function EntranceManage_ChangePw() {
    var pw = $("#EntranceManage_000600").val();
    var pw1 = $("#EntranceManage_000601").val();
    var pw2 = $("#EntranceManage_000602").val();

    if(pw.length < 4 || pw.length > 12) {
        Popup_Alert("비밀번호 길이가 부적합합니다!", function() {
            $("#EntranceManage_000600").val("");
            $("#EntranceManage_000600").focus();
        });
        return false;
    }
    if(pw1.length < 4 || pw1.length > 12) {
        Popup_Alert("비밀번호 길이가 부적합합니다!", function() {
            $("#EntranceManage_000601").val("");
            $("#EntranceManage_000601").focus();
        });
        return false;
    }
    if(pw1 != pw2) {
        Popup_Alert("비밀번호가 일치하지 않습니다!", function() {
            $("#EntranceManage_000601").val("");
            $("#EntranceManage_000601").focus();
        });
        return false;
    }

    Popup_Confirm('정말 실행하시겠습니까?', function() {
        Popup_Wait(function() {
            PostJSON(
                HOME+I+ENTRANCE+W+MANAGE+W+"password"+POST, {
                    pw: hex_md5(pw+""+pw),
                    newPw: hex_md5(pw1+""+pw1)
                },
                function(response, textStatus) {
                    Popup_WaitShow(response.msg, function() {
                        $("#EntranceManage_000600").val("");
                        $("#EntranceManage_000601").val("");
                        $("#EntranceManage_000602").val("");
                    });
                }
            )
        })
    });
}

function EntranceManage_ChangeIcon() {
    if($("#EntranceManage_001601").val() == "") {
        Popup_Alert("파일을 선택해 주세요!");
    } else {
        Popup_Wait(function() {
            $("#formIcon").submit();
        });
    }
}

function EntranceManage_DeleteIcon() {
    Popup_Confirm('정말 실행하시겠습니까?', function() {
        Popup_Wait(function() {
            PostJSON(
                HOME+I+ENTRANCE+W+MANAGE+W+"delete"+POST, {
                },
                function(response, textStatus) {
                    Popup_WaitShow(response.msg, function() {
                        EntranceManage_Update();
                    });
                }
            )
        })
    });
}

function EntranceManage_Quit() {
    var pw = $("#EntranceManage_000600").val();

    if(pw.length < 4 || pw.length > 12) {
        Popup_Alert("현재 비밀번호를 입력해주세요.", function() {
            $("#EntranceManage_000600").val("");
            $("#EntranceManage_000600").focus();
        });
        return false;
    }

    Popup_Confirm('정말 탈퇴하시겠습니까?', function() {
        Popup_Wait(function() {
            PostJSON(
                HOME+I+ENTRANCE+W+MANAGE+W+"quit"+POST, {
                    pw: hex_md5(pw+""+pw)
                },
                function(response, textStatus) {
                    if(response.result == "SUCCESS") {
                        Popup_WaitShow(response.msg, function() {
                            ReplaceFrame(HOME);
                        });
                    } else {
                        Popup_WaitShow(response.msg, function() {
                            EntranceManage_Update();
                        });
                    }
                }
            )
        })
    });
}
