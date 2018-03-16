var serverListTemplate = "\
<tr class='server_item bg0' data-server='<%name%>'>\
    <td class='server_name'>\
        <span style='font-weight:bold;font-size:1.4em;color:<%color%>'><%korName%>섭</span>\
        <span class='n_country'></span>\
    </td>\
    <td colspan='3' class='server_down'>- 폐 쇄 중 -</td>\
</tr>\
";

var serverFullTemplate = "\
<div class='Entrance_ServerListBlock'>- 장수 등록 마감 -</div>\
";

var serverCreateAndSelectTemplate = "\
<div class='Entrance_ServerListNoRegister'>- 미 등 록 -</div>\
<input class='general_select' type='button' value='장수선택'>\
<input class='general_create' type='button' value='장수생성'>\
";

var serverCreateTemplate = "\
<div class='Entrance_ServerListNoRegister'>- 미 등 록 -</div>\
<input class='general_create' type='button' value='장수생성'>\
";

function Entrance_Import() {
    ImportAction(HOME+I+ENTRANCE+W+MANAGE+W+ACTION);

    EntranceManage_Import();
}

function Entrance_Init() {
    EntranceManage_Init();

    $("#btn_user_manage").click(Entrance_Manage);
    $("#btn_logout").click(Entrance_Logout);
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
                    Entrance_drawServerList(response.server);
                    //Entrance_ServerList(response.serverCount, response.servers);
                    //Entrance_ServerListPosition();
                    Popup_WaitHide();
                } else {
                    Popup_WaitShow("서버목록 로드 실패!");
                }
            }
        )
    });
}

function Entrance_drawServerList(serverInfos){
    var $serverList = $('#server_list');
    $.each(serverInfos, function(idx, serverInfo){
        var serverHtml = TemplateEngine(serverListTemplate, serverInfo);
        $serverList.append(serverHtml);
        if(!serverInfo.enable){
            return true;
        }

        $.getJSON('../'+serverInfo.name+'/j_server_basic_info.php',{}, function(result){

        });
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
