function Entrance_Import() {
    ImportAction(HOME+I+ENTRANCE+W+MANAGE+W+ACTION);

    EntranceManage_Import();
}

function Entrance_Init() {
    EntranceManage_Init();

    $("#Entrance_000101").click(Entrance_Manage);
    $("#Entrance_000102").click(Entrance_Logout);
}

function Entrance_Update() {
    Entrance_UpdateServer();
}

function Entrance_UpdateServer() {
    Popup_Wait(function() {
        PostJSON(
            HOME+I+ENTRANCE+W+SERVERLIST+POST, {
            },
            function(response, textStatus) {
                if(response.result == "SUCCESS") {
                    Entrance_ServerList(response.serverCount, response.servers);
                    Entrance_ServerListPosition();
                    Popup_WaitHide();
                } else {
                    Popup_WaitShow("서버목록 로드 실패!");
                }
            }
        )
    });
}

function Entrance_ServerList(serverCount, servers) {
    $("#Entrance_000002").html(servers);
    $("#Entrance_000002").height(serverCount*64);
}

function Entrance_ServerListPosition() {
    var heightTitle = $("#Entrance_000000").height();
    var heightSub = $("#Entrance_000001").height();
    var heightList = $("#Entrance_000002").height();
    var heightComment = $("#Entrance_000003").height();
    var top = heightTitle+heightSub+heightList+5;

    $("#Entrance_000003").css("top", top+"px");
    top += heightComment;
    $("#Entrance_0000").height(top+2);
    top = 20 + top;
    $("#Entrance_0001").css("top", top+"px");
    top = 20 + top + $("#Entrance_0001").height();
    $("#Entrance_0002").css("top", top+"px");
    top = 20 + top + $("#Entrance_0002").height();
    $("#Entrance_0003").css("top", top+"px");
}

function Entrance_Manage() {
    $("#Entrance_00").hide();
    $("#EntranceManage_00").show();
    EntranceManage_Update();
}

function Entrance_Logout() {
    Popup_Wait(function() {
        PostJSON(
            HOME+I+ENTRANCE+W+LOGOUT+POST, {
            },
            function(response, textStatus) {
                if(response.result == "SUCCESS") {
                    Popup_WaitHide();
                    ReplaceFrame(HOME);
                } else {
                    Popup_WaitShow("로그아웃 실패!");
                }
            }
        )
    });
}

//TODO: 재설계
function Entrance_Enter(serverDir) {
    Popup_Wait(function() {
        PostJSON(
            HOME+I+ENTRANCE+W+"enterPost", {
            },
            function(response, textStatus) {
                if(response.result == "SUCCESS") {
                    var form = $("<form></form>");
                    $(form).attr("action", HOME+serverDir);
                    $(form).attr("method", "post");

                    var id = $("<input type='hidden' name='id'>");
                    $(id).attr("value", response.id);
                    var pw = $("<input type='hidden' name='pw'>");
                    $(pw).attr("value", response.pw);
                    var conmsg = $("<input type='hidden' name='conmsg'>");
                    $(conmsg).attr("value", response.conmsg);

                    $(form).append(id).append(pw).append(conmsg);
                    $("body").append(form);
                    $(form).submit();
                } else {
                    Popup_WaitShow("입장 실패!");
                }
            }
        )
    });
}
