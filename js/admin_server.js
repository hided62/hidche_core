
var serverAdminTemplate = '\
<tr class="bg0" data-server_name="<%name%>">\
    <th style="color:<%color%>;"><%korName%>(<%name%>)</th>\
    <td><%status%></td>\
    <td><button class="valid_if_set with_border obj_fill" onclick="modifyServerStatus(this, \'close\');">폐쇄</button></td>\
    <td><button class="valid_if_set with_border obj_fill" onclick="modifyServerStatus(this, \'open\');">오픈</button></td>\
    <td><a class="just_link" href="../<%name%>/install.php"><button class="valid_if_set with_border obj_fill">리셋</button></a></td>\
    <td><a class="just_link" href="../<%name%>/install_db.php"><button class="valid_if_set only_admin with_border obj_fill">하드리셋</button></a></td>\
    <td><button class="valid_if_set with_border obj_fill" onclick="Entrance_AdminClosedLogin(this);">폐쇄중 로그인</button></td>\
    <td><button class="valid_if_set with_border obj_fill" onclick="Entrance_AdminOpen119(this);">서버119</button></td>\
    <td><button class="only_admin with_border obj_fill" onclick="Entrance_AdminOpen119(this);">업데이트</button></td>\
</tr>\
';


function drawServerAdminList(serverList){
    var $table = $('#server_admin_list');
    $.each(serverList.server, function(idx, server){
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

        var $tr = $(TemplateEngine(serverAdminTemplate, server));
        $table.append($tr);
        if(!server.valid){
            $tr.find('.valid_if_set').css('background','#333333').prop('disabled', true);
        }
    });
    if(serverList.grade == 5){
        $table.find('.only_admin').css('background','#333333').prop('disabled', true);
    }
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
        url:'j_server_change_status.php',
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