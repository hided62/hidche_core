var serverListTemplate = "\
<tr class='server_item bg0 server_name_<%name%>' data-server='<%name%>'>\
    <td class='server_name'>\
        <span style='font-weight:bold;font-size:1.4em;color:<%color%>'><%korName%>섭</span><br>\
        <span class='n_country'></span>\
    </td>\
    <td colspan='4' class='server_down'>- 폐 쇄 중 -</td>\
</tr>\
";

var serverTextInfo = "\
<td>\
서기 <%year%>년 <%month%>월 (<span style='color:orange;'><%scenario%></span>)<br>\
유저 : <%userCnt%> / <%maxUserCnt%>명 <span style='color:cyan;'>NPC : <%npcCnt%>명</span> (<span style='color:limegreen;'><%turnTerm%></span>)\
</td>\
";

var serverFullTemplate = "\
<td colspan='4' class='server_full'>- 장수 등록 마감 -</td>\
";

var serverCreateAndSelectTemplate = "\
<td colspan='2' class='not_registered'>- 미 등 록 -</td>\
<td class='ignore_border'>\
<a href='<%serverPath%>/select_npc.php'><button class='general_select with_skin'>장수선택</button></a>\
<a href='<%serverPath%>/join.php'><button class='general_create with_skin'>장수생성</button></a>\
</td>\
";

var serverCreateTemplate = "\
<td colspan='2' class='not_registered'>- 미 등 록 -</div>\
<td class='ignore_border'>\
<a href='<%serverPath%>/join.php'><button class='general_create with_skin'>장수생성</button></a>\
</td>\
";

var serverLoginTemplate = "\
<td style='background:url(<%picture%>);background-size: 64px 64px;'></td>\
<td><%name%></td>\
<td class='ignore_border'>\
<a href='<%serverPath%>/'><button class='general_login with_skin'>입장</button></a>\
</td>\
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
        var $serverHtml = $(TemplateEngine(serverListTemplate, serverInfo));
        $serverList.append($serverHtml);
        if(!serverInfo.enable){
            return true;
        }

        var serverPath = '../'+serverInfo.name;


        $.getJSON('../'+serverInfo.name+'/j_server_basic_info.php',{}, function(result){
            console.log(result);
            console.log(result.game);
            if(!result.game){
                return;
            }


            var game= result.game;
            //TODO: 서버 폐쇄 방식을 새롭게 변경
            $serverHtml.find('.server_down').detach();

            if(game.isUnited == 2){
                $serverHtml.find('.n_country').html('§천하통일§');
            }
            else{
                $serverHtml.find('.n_country').html('<{0}국 경쟁중>'.format(game.nationCnt));
            }

            $serverHtml.append(
                TemplateEngine(serverTextInfo, game)
            );

            if(result.me){
                var me = result.me;
                me.serverPath = serverPath;
                $serverHtml.append(
                    TemplateEngine(serverLoginTemplate, me)
                );
            }
            else if(game.userCnt >= game.maxUserCnt){
                $serverHtml.append(
                    TemplateEngine(serverFullTemplate, {})
                );
            }
            else if(game.npcMode == 1){
                $serverHtml.append(
                    TemplateEngine(serverCreateAndSelectTemplate, {serverPath:serverPath})
                ).addClass('server_create_and_select');
            }
            else{
                $serverHtml.append(
                    TemplateEngine(serverCreateTemplate, {serverPath:serverPath})
                );
            }

            
        });
    });
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
