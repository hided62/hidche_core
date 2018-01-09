function Login_Import() {
    ImportAction(HOME+I+LOGIN+W+FINDPW+W+ACTION);
    ImportAction(HOME+I+LOGIN+W+JOIN+W+ACTION);

    LoginFindpw_Import();
    LoginJoin_Import();
}

function Login_Init() {
    LoginFindpw_Init();
    LoginJoin_Init();

    $(".Login_Bbs").click(function() { window.open('/bbs'); });
    $(".Login_Free").click(function() { window.open('/bbs/bbs/board.php?bo_table=0free'); });
    $(".Login_News").click(function() { window.open('/bbs/bbs/board.php?bo_table=1news'); });
    $(".Login_Reference").click(function() { window.open('/bbs/bbs/board.php?bo_table=2reference'); });
    $(".Login_Patch").click(function() { window.open('/bbs/bbs/board.php?bo_table=3patch'); });
    $(".Login_Donation").click(function() { window.open('/bbs/bbs/board.php?bo_table=4donation'); });
    $(".Login_Tutorial").click(function() { window.open('../i_other/help.php'); });
    $(".Login_Emperior").click(function() { window.open('../che/a_emperior.php'); });
    $(".Login_Traffic").click(function() { window.open('../che/a_traffic.php'); });

    $("#Login_00010007").focus(Login_DefaultConMsg);

    $("#Login_00010008").click(Login_Findpw);
    $("#Login_00010009").click(Login_Join);
    $("#Login_00010010").click(Login_Login);
    $("#Login_00010013").click(function() { window.open('../i_other/help.php'); });
    $("#Login_00010014").click(function() { window.open('../i_other/screenshot.php'); });

    $("#Login_00010003").keypress(Login_Enter);
    $("#Login_00010005").keypress(Login_Enter);
    $("#Login_00010007").keypress(Login_Enter);
}

function Login_Enter(e) {
    if(e.keyCode == 13) {
        Login_Login();
    }
}

function Login_Update() {
    $("#Login_00010003").val("");
    $("#Login_00010005").val("");
    $("#Login_00010007").val("");

    $("#Login_00010003").focus();
}

function Login_DefaultConMsg() {
    $("#Login_00010007").val("자택");
}

function Login_Login() {
    var id = $("#Login_00010003").val();
    var pw = $("#Login_00010005").val();
    var token = $("#token_login").val();
    var conmsg = $("#Login_00010007").val();

    if(id.length == 0) {
        Popup_Alert("ID를 입력해주세요!", function() {
            $("#Login_00010003").focus();
        });
        return;
    }

    if(pw.length == 0) {
        Popup_Alert("PW를 입력해주세요!", function() {
            $("#Login_00010005").focus();
        });
        return;
    }

    if(conmsg.length == 0) {
        Popup_Alert("접속장소를 입력해주세요!", function() {
            $("#Login_00010007").focus();
        });
        return;
    }

    pw = hex_md5(pw+""+pw)+""+hex_md5(token);

    Popup_Wait(function() {
        PostJSON(
            HOME+I+LOGIN+W+POST, {
                id: id,
                pw: pw,
                conmsg: conmsg
            },
            function(response, textStatus) {
                if(response.result == "SUCCESS") {
                    Popup_WaitHide();
                    ReplaceFrame(HOME);
                } else {
                    Popup_WaitShow(response.msg, function() {
                        $("#Login_00010010").focus();
                    });
                }
            }
        )
    });
}

function Login_Findpw() {
    $("#Login_00").hide();
    $("#LoginFindpw_00").show();
    LoginFindpw_Update();
}

function Login_Join() {
    $("#Login_00").hide();
    $("#LoginJoin_00").show();
    LoginJoin_Update();
}
