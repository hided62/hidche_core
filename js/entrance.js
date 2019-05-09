var serverListTemplate = "\
<tr class='server_item bg0 server_name_<%name%>' data-server='<%name%>'>\
    <td class='server_name obj_tooltip' data-toggle='tooltip' data-placement='bottom'>\
        <span style='font-weight:bold;font-size:1.4em;color:<%color%>'><%korName%>섭</span><br>\
        <span class='n_country'></span>\
        <span class='tooltiptext server_date'></span>\
    </td>\
    <td colspan='4' class='server_down'>- 폐 쇄 중 -</td>\
</tr>\
";

var serverTextInfo = "\
<td>\
서기 <%year%>년 <%month%>월 (<span style='color:orange;'><%scenario%></span>)<br>\
유저 : <%userCnt%> / <%maxUserCnt%>명 <span style='color:cyan;'>NPC : <%npcCnt%>명</span> (<span style='color:limegreen;'><%turnTerm%>분 턴 서버</span>)\
</td>\
";

var serverFullTemplate = "\
<td colspan='4' class='server_full'>- 장수 등록 마감 -</td>\
";

var serverCreateAndSelectTemplate = "\
<td colspan='2' class='not_registered'>- 미 등 록 -</td>\
<td class='ignore_border'>\
<a href='<%serverPath%>/select_npc.php'><button type='button' class='general_select with_skin'>장수선택</button></a>\
<a href='<%serverPath%>/join.php'><button type='button' class='general_create with_skin'>장수생성</button></a>\
</td>\
";

var serverCreateTemplate = "\
<td colspan='2' class='not_registered'>- 미 등 록 -</div>\
<td class='ignore_border'>\
<a href='<%serverPath%>/join.php'><button type='button' class='general_create with_skin'>장수생성</button></a>\
</td>\
";

var serverLoginTemplate = "\
<td style='background:url(\"<%picture%>\");background-size: 64px 64px;'></td>\
<td><%name%></td>\
<td class='ignore_border'>\
<a href='<%serverPath%>/'><button type='button' class='general_login with_skin'>입장</button></a>\
</td>\
";


var serverReservedTemplate = "\
<td colspan='4' class='server_reserved'>\
<%openDatetime==starttime?'':'- 가오픈 일시 : '+openDatetime+ '- <br>'%>\
- 오픈 일시 : <%starttime%> - <br>\
<span style='color:orange;'><%scenarioName%></span> <span style='color:limegreen;'><%turnterm%>분 턴 서버</span><br>\
(상성 설정:<%fictionMode%>), (빙의 여부:<%npcMode%>), (최대 스탯:<%gameConf.defaultStatTotal%>), (기타 설정:<%otherTextInfo%>)</td>\
";

$(function(){
    $("#btn_logout").click(Entrance_Logout);
    Entrance_UpdateServer();
});

function Entrance_UpdateServer() {
    $.ajax({
        type:'post',
        url:"j_server_get_status.php",
        dataType:'json',
    }).then(function(response){
        if(response.result == "SUCCESS") {
            Entrance_drawServerList(response.server);
        }
    });
}

function Entrance_drawServerList(serverInfos){
    var $serverList = $('#server_list');
    $.each(serverInfos, function(idx, serverInfo){
        var $serverHtml = $(TemplateEngine(serverListTemplate, serverInfo));
        $serverList.append($serverHtml);
        if(!serverInfo.exists){
            return true;
        }

        var serverPath = "../"+serverInfo.name;


        $.getJSON("../"+serverInfo.name+'/j_server_basic_info.php',{}, function(result){
            if(result.reserved){
                $serverHtml.find('.server_down').detach();
                $serverHtml.append(
                    TemplateEngine(serverReservedTemplate, result.reserved)
                );
                return;
            }

            if(!result.game){
                return;
            }


            var game= result.game;
            //TODO: 서버 폐쇄 방식을 새롭게 변경
            $serverHtml.find('.server_down').detach();
            var serverTime = '%s ~ %s'.format(game.startFrom)

            if(game.isUnited == 2){
                $serverHtml.find('.n_country').html('§천하통일§');
                $serverHtml.find('.server_date').html('{0} <br>~ {1}'.format(game.starttime, game.turntime));
            }
            else{
                $serverHtml.find('.n_country').html('<{0}국 경쟁중>'.format(game.nationCnt));
                $serverHtml.find('.server_date').html('{0} ~'.format(game.starttime));
            }

            $serverHtml.append(
                TemplateEngine(serverTextInfo, game)
            );

            if(result.me && result.me.name){
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

            initTooltip($serverHtml);
        });
    });
}

function Entrance_Logout() {
    $.ajax({
        type:'post',
        url:"j_logout.php",
        dataType:'json',
    }).then(function(response) {
        if(response.result) {
            location.href="../";
        } else {
            alert('로그아웃 실패');
        }
    });
}
