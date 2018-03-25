
var serverAdminTemplate = '\
<tr class="bg0" data-server_name="<%name%>">\
    <th style="color:<%color%>;"><%korName%>(<%name%>)</th>\
    <td><%status%></td>\
    <td><input class="with_skin obj_fill" type="button" value="폐쇄" onclick="modifyServerStatus(this, \'close\');"></td>\
    <td><input class="with_skin obj_fill" type="button" value="오픈" onclick="modifyServerStatus(this, \'open\');"></td>\
    <td><input class="with_skin obj_fill" type="button" value="리셋" onclick="modifyServerStatus(this, \'reset\');"></td>\
    <td><input class="with_skin obj_fill" type="button" value="하드리셋" onclick="modifyServerStatus(this, \'reset_full\');"></td>\
    <td><input class="with_skin obj_fill" type="button" value="폐쇄중 로그인" onclick="Entrance_AdminClosedLogin(this);"></td>\
    <td><input class="with_skin obj_fill" type="button" value="서버119" onclick="Entrance_AdminOpen119(this);"></td>\
    <td><input class="with_skin obj_fill" type="button" value="업데이트" onclick="Entrance_AdminOpen119(this);"></td>\
</tr>\
';


function drawServerAdminList(serverList){
    var $table = $('#server_admin_list');
    $.each(serverList, function(idx, server){
        console.log(server);
        var status = '';
        if(!server.valid){
            status = '에러, {0}'.format(server.reason);
        }
        else if(!server.run){
            status = '폐쇄됨';
        }
        else{
            status = '운영 중';
        }
        server.status = status;

        $table.append(
            TemplateEngine(serverAdminTemplate, server)
        );
    });
}

$(function(){
    Entrance_AdminImport();
    Entrance_AdminInit();
    Entrance_AdminUpdate();

    $.ajax({
        cache:false,
        type:'post',
        url:'j_server_get_admin_status.php',
        dataType:'json'
    }).then(drawServerAdminList);
});
function Entrance_AdminImport() {
}

function Entrance_AdminInit() {
    console.log('adminInit');
    $("#Entrance_000201").click(Entrance_Donation);
    $("#Entrance_000202").click(Entrance_Member);
    $("#notice_change_btn").click(Entrance_AdminChangeNotice);
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
    var notice = $("#notice_edit").val();

    if(!confirm('정말 실행하시겠습니까?')){
        return;
    }

    $.ajax({
        type:'post',
        url:'j_server_change_status.php',
        dataType:'json',
        data:{
            action: 'notice',
            notice: notice
        }
    }).then(function(response){
        if(response.result == "SUCCESS") {
            location.reload();
        } else {
            alert(response.msg);
        }
    });
}

function modifyServerStatus(caller, action) {
    var $caller = $(caller);
    var server = $caller.parents('tr').data('server_name');
    
    if(!confirm('정말 실행하시겠습니까?')){
        return;
    }
    $.ajax({
        type:'post',
        url:'j_server_change_status.php.php',
        dataType:'json',
        data:{
            server: server,
            action: action
        }
    }).then(function(response) {
            if(response.result == "SUCCESS") {
                if(action == 'reset') {
                    location.href = response.installURL;
                } else {
                    location.reload();
                }
            } else {
                alert(response.msg);
            }
        }
    );
}

function Entrance_AdminNPCLogin(caller) {
    var $caller = $(caller);
    var serverDir = $caller.parents('tr').data('server_name');
    location.href = serverDir+"/npc_login.php";
}

function Entrance_AdminNPCCreate(caller) {
    var $caller = $(caller);
    var serverDir = $caller.parents('tr').data('server_name');
    location.href = serverDir+"/npc_join.php";
}

function Entrance_AdminClosedLogin(caller) {
    var $caller = $(caller);
    var serverDir = $caller.parents('tr').data('server_name');
    location.href = serverDir+"/npc_login.php";
}

function Entrance_AdminOpen119(caller) {
    var $caller = $(caller);
    var serverDir = $caller.parents('tr').data('server_name');
    location.href = serverDir+"/_119.php";
}

function tryServerUpdateAndUpgrade(caller){
    var $caller = $(caller);
}