
var serverAdminTemplate = '\
<tr class="bg0" data-server_name="<%name%>" data-is_root="<%isRoot%>" data-git-path="<%lastGitPath%>">\
    <th style="color:<%color%>;"><%korName%>(<%name%>)</th>\
    <td><%status%></td>\
    <td><%version%></td>\
    <td><button class="with_skin valid_if_set with_border obj_fill" onclick="modifyServerStatus(this, \'close\');">폐쇄</button></td>\
    <td><button class="with_skin valid_if_set with_border obj_fill" onclick="modifyServerStatus(this, \'open\');">오픈</button></td>\
    <td><a class="just_link" href="../<%name%>/install.php"><button class="with_skin valid_if_set with_border obj_fill">리셋</button></a></td>\
    <td><a class="just_link" href="../<%name%>/install_db.php"><button class="with_skin valid_if_installed only_admin with_border obj_fill">하드리셋</button></a></td>\
    <td><button class="with_skin valid_if_set with_border obj_fill" onclick="Entrance_AdminClosedLogin(this);">폐쇄중 로그인</button></td>\
    <td><button class="with_skin valid_if_set with_border obj_fill" onclick="Entrance_AdminOpen119(this);">서버119</button></td>\
    <td><button class="with_skin with_border obj_fill" onclick="serverUpdate(this);">업데이트</button></td>\
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
    
    if(isRoot){
        if(!confirm('서버 라이브러리, 루트 서버에 대해 git pull을 실행합니다.')){
            return;
        }
    }
    else if(window.adminGrade < 6){
        if (!confirm('다음 git tree-ish 주소로 업데이트를 시도합니다 : ' + target)) {
            return;
        }
    }
    else {
        target = prompt('가져올 git tree-ish 명을 입력해주세요.', target)
        if(!target){
            return;
        }

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
                alert('{0} 서버가 {1} 버전으로 업데이트 되었습니다.'.format(response.server, response.version));
                location.reload();
            } else {
                alert(response.reason);
            }
        }
    );
}

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
            $tr.find('.valid_if_set').prop('disabled', true);
        }
        if(!server.installed){
            $tr.find('.valid_if_installed').prop('disabled', true);
        }
    });
    window.adminGrade = serverList.grade;
    if(serverList.grade == 5){
        
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