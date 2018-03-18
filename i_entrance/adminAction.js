
var serverAdminTemplate = '\
<tr data-server_name="<%name%>\
    <th><%korName%>(<%name%>)</th>\
    <td><%status%></td>\
    <td><input type="button" value="폐쇄" onclick="Entrance_AdminPost(0);"></td>\
    <td><input type="button" value="오픈" onclick="Entrance_AdminPost(2);"></td>\
    <td><input type="button" value="리셋" onclick="Entrance_AdminPost(1);"></td>\
    <td><input type="button" class="Entrance_ServerAdminListSelectButton2" value="N로그인" onclick="Entrance_AdminNPCLogin();"></td>\
    <td><input type="button" value="N생성" onclick="Entrance_AdminNPCCreate();"></td>\
    <td><input type="button" value="폐쇄중로그인" onclick="Entrance_AdminClosedLogin();"></td>\
</tr>\
';


$(function(){
    Entrance_AdminImport();
    Entrance_AdminInit();
    Entrance_AdminUpdate();
});
function Entrance_AdminImport() {
}

function Entrance_AdminInit() {
    console.log('adminInit');
    $("#Entrance_000201").click(Entrance_Donation);
    $("#Entrance_000202").click(Entrance_Member);
    $("#Entrance_000204").click(Entrance_AdminChangeNotice);
}

function Entrance_AdminUpdate() {
}

function Entrance_Donation() {
    $("#Entrance_00").hide();
    $("#EntranceDonation_00").show();
    EntranceDonation_Update();
}

function Entrance_Member() {
    $("#Entrance_00").hide();
    $("#EntranceMember_00").show();
    EntranceMember_Update();
}

function Entrance_AdminChangeNotice() {
    var notice = $("#Entrance_000203").val();

    Popup_Confirm('정말 실행하시겠습니까?', function() {
            Popup_Wait(function() {
                PostJSON(
                    HOME+I+ENTRANCE+W+ADMIN+POST, {
                        action: 0,
                        notice: notice
                    },
                    function(response, textStatus) {
                        if(response.result == "SUCCESS") {
                            Popup_WaitHide();
                            Replace(ENTRANCE+PHP);
                        } else {
                            Popup_WaitShow(response.msg);
                        }
                    }
                )
            })
    });
}

function Entrance_AdminPost(server, select) {
    Popup_Confirm('정말 실행하시겠습니까?', function() {
            Popup_Wait(function() {
                PostJSON(
                    HOME+I+ENTRANCE+W+ADMIN+POST, {
                        action: 1,
                        server: server,
                        select: select
                    },
                    function(response, textStatus) {
                        if(response.result == "SUCCESS") {
                            Popup_WaitHide();
                            if(select == 1) {
                                ReplaceFrame(response.installURL);
                            } else {
                                Replace(ENTRANCE+PHP);
                            }
                        } else {
                            Popup_WaitShow(response.msg);
                        }
                    }
                )
            })
    });
}

function Entrance_AdminNPCLogin(serverDir) {
    ReplaceFrame(serverDir+W+'npc_login'+PHP);
}

function Entrance_AdminNPCCreate(serverDir) {
    ReplaceFrame(serverDir+W+'npc_join'+PHP);
}

function Entrance_AdminClosedLogin(serverDir) {
    ReplaceFrame(serverDir+W+'npc_login'+PHP);
}

function Entrance_AdminOpen119(serverDir) {
    Open(serverDir+W+'_119'+PHP);
}
