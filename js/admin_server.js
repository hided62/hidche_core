
var serverAdminTemplate = '\
<tr class="bg0 server_admin_<%name%>" data-server_name="<%name%>" data-is_root="<%isRoot%>" data-git-path="<%lastGitPath%>">\
    <th style="color:<%color%>;"><%korName%>(<%name%>)</th>\
    <td><%status%></td>\
    <td><%version%></td>\
    <td><button class="serv_act_close with_skin valid_if_set with_border obj_fill" onclick="modifyServerStatus(this, \'close\');">폐쇄</button></td>\
    <td><button class="serv_act_open with_skin valid_if_set with_border obj_fill" onclick="modifyServerStatus(this, \'open\');">오픈</button></td>\
    <td><a class="just_link" href="../<%name%>/install.php"><button class="serv_act_reset with_skin valid_if_set with_border obj_fill">리셋</button></a></td>\
    <td><a class="just_link" href="../<%name%>/install_db.php"><button class="serv_act_hard_reset with_skin valid_if_installed only_admin with_border obj_fill">하드리셋</button></a></td>\
    <td><button class="serv_act_login_close with_skin valid_if_set with_border obj_fill" onclick="Entrance_AdminClosedLogin(this);">폐쇄중 로그인</button></td>\
    <td><button class="serv_act_119 with_skin valid_if_set with_border obj_fill" onclick="Entrance_AdminOpen119(this);">서버119</button></td>\
    <td><button class="serv_act_update with_skin with_border obj_fill" onclick="serverUpdate(this);">업데이트</button></td>\
</tr>\
';

function serverUpdate(caller){
    var $caller = $(caller);
    var $tr = $caller.parents('tr');
    var server = $tr.data('server_name');
    var isRoot = $tr.data('is_root');

    var target = $tr.data('gitPath');

    if(typeof isRoot !== 'boolean'){
        isRoot = (isRoot != 'false');
    };

    var allowFullUpdate = (server in window.aclList && window.aclList[server].indexOf('fullUpdate')>=0);
    allowFullUpdate |= window.adminGrade > 5;

    var allowUpdate = (server in window.aclList && window.aclList[server].indexOf('update')>=0);
    allowUpdate |= window.adminGrade >= 5;
    allowUpdate |= allowFullUpdate;
    
    if(!allowUpdate){
        alert('권한이 없습니다!');
        return;
    }

    
    if(allowFullUpdate){
        target = prompt('가져올 git tree-ish 명을 입력해주세요.', target)
        if(!target){
            return;
        }
    }
    else if(isRoot){
        if(!confirm('서버 라이브러리, 루트 서버에 대해 git pull을 실행합니다.')){
            return;
        }
    }
    else if (!confirm('다음 git tree-ish 주소로 업데이트를 시도합니다 : ' + target)) {
        return;
    }
    
    $.ajax({
        type:'post',
        url:'../j_updateServer.php',
        dataType:'json',
        data:{
            server: server,
            target: target
        }
    }).then(function(response) {
            if(response.result) {
                var aux = '';
                if(isRoot){
                    aux = ' (이미지 서버 갱신:{0}, {1})'.format(response.imgResult, response.imgDetail);
                }
                alert('{0} 서버가 {1} 버전으로 업데이트 되었습니다.{2}'.format(response.server, response.version, aux));
                location.reload();
            } else {
                alert(response.reason);
            }
        }
    );
}

function drawServerAdminList(serverList){
    var $table = $('#server_admin_list');
    var $showErrorLog = $('#showErrorLog');

    if(serverList.grade >= 5){
        $showErrorLog.show();
    }
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
        if(serverList.grade < 4){
            $tr.find('button').prop('disabled', true);
        }
        if(!server.valid){
            $tr.find('.valid_if_set').prop('disabled', true);
        }
        if(!server.installed){
            $tr.find('.valid_if_installed').prop('disabled', true);
        }

        

        var aclByServer = serverList.acl[server.name];

        $.each(aclByServer, function(idx, action){
            console.log(action);
            if(action == 'update' || action == 'fullUpdate'){
                if(!server.installed){
                    return true;
                }
                $tr.find('.serv_act_update').prop('disabled', false);
                $showErrorLog.show();
            }
            else if(action == 'openClose'){
                if(!server.valid){
                    return true;
                }
                $tr.find('.serv_act_open, .serv_act_close').prop('disabled', false);
            }
            else if(action == 'reset'){
                if(!server.installed){
                    return true;
                }
                $tr.find('.serv_act_reset, .serv_act_close').prop('disabled', false);
            }
        });
    });
    window.adminGrade = serverList.grade;
    window.aclList = serverList.acl;
    if(serverList.grade <= 5){
        $table.find('.only_admin').prop('disabled', true);
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