



function fillUserInfo(result){
    if(!result.result){
        alert(result.reason);
        location.href='entrance.php';
        return;
    }

    $('#slot_id').html(result.id);
    $('#slot_nickname').html(result.name);
    $('#slot_grade').html(result.grade);
    $('#slot_icon').attr('src', result.picture);
    $('#global_salt').val(result.global_salt);
}

function changeIconPreview(){
    var $preview = $(this);
    console.log($preview);

    var filename = $preview[0].files[0].name;
    var reader = new FileReader();
    reader.onload = function(e) {
        $('#slot_new_icon').attr('src', e.target.result).css('visibility','visible');
    }

    reader.readAsDataURL($preview[0].files[0]);
    
    $('#image_upload_filename').val(filename);
}

function deleteIcon(){
    $.ajax({
        type:'post',
        url:'j_icon_delete.php',
        dataType:'json'
    }).then(function(result){
        if(!result.result){
            alert(result.reason);
            location.reload();
            return;
        }

        var ajaxResults = result.servers.map(function(server){
            return $.ajax({
                type:'post',
                url:'../{0}/j_adjust_icon.php'.format(server),
                dataType:'json'
            });
        });

        $.when.apply($, ajaxResults).then(function(){
            alert('이미지를 삭제했습니다.');
            location.reload();
        },function(){
            //서버 폐쇄등으로 접근하지 못할 수도 있음.
            alert('이미지를 삭제했습니다.');
            location.reload();
        });
        
    },function(){
        alert('알 수 없는 이유로 아이콘 삭제를 실패했습니다.');
        location.reload();
    });
}

function changeIcon(e){
    e.preventDefault();
    var $icon = $('#image_upload');

    if($icon[0].files.length == 0){
        alert('파일을 선택해주세요');
        return false;
    }
    var icon = $icon[0].files[0];



    $.ajax({
        type:'post',
        url:'j_icon_change.php',
        dataType:'json',
        contentType:false,
        processData:false,
        data:new FormData($(this)[0])
    }).then(function(result){
        if(!result.result){
            alert(result.reason);
            location.reload();
            return;
        }

        var ajaxResults = result.servers.map(function(server){
            return $.ajax({
                type:'post',
                url:'../{0}/j_adjust_icon.php'.format(server),
                dataType:'json'
            });
        });

        $.when.apply($, ajaxResults).then(function(){
            alert('이미지를 업로드했습니다.');
            location.reload();
        },function(){
            //서버 폐쇄등으로 접근하지 못할 수도 있음.
            alert('이미지를 업로드했습니다.');
            location.reload();
        });
        
    },function(){
        alert('알 수 없는 이유로 아이콘 업로드를 실패했습니다.');
        location.reload();
    });
}

function changePassword(e){
    e.preventDefault();
    var $form = $(this);

    var old_pw = $('#current_pw').val();
    var new_pw = $('#new_pw').val();
    var new_pw_confirm = $('#new_pw_confirm').val();

    if(!old_pw){
        alert('이전 비밀번호를 입력해야 합니다.');
        return;
    }
    if(new_pw.length < 6){
        alert('비밀번호 길이는 6글자 이상이어야 합니다.');
        return;
    }

    if(new_pw != new_pw_confirm){
        alert('입력 값이 일치하지 않습니다.');
        return;
    }

    var global_salt = $('#global_salt').val();

    var old_password = sha512(global_salt+old_pw+global_salt);
    var new_password = sha512(global_salt+new_pw+global_salt);

    $.ajax({
        type:'post',
        url:'j_change_password.php',
        dataType:'json',
        data:{
            old_pw:old_password,
            new_pw:new_password
        }
    }).then(function(result){
        if(!result.result){
            alert(result.reason);
        }
        else{
            alert('비밀번호를 바꾸었습니다');
            location.reload();
        }
    },function(){
        alert('알 수 없는 이유로 비밀번호를 바꾸지 못했습니다.');
    });
}

function deleteMe(){
    var $form = $('#delete_me_form');

    var pw = $('#delete_pw').val();

    if(!pw){
        alert('비밀번호를 입력해야 합니다.');
        return;
    }

    var global_salt = $('#global_salt').val();

    var password = sha512(global_salt+pw+global_salt);

    $.ajax({
        type:'post',
        url:'j_delete_me.php',
        dataType:'json',
        data:{
            pw:password
        }
    }).then(function(result){
        if(!result.result){
            alert(result.reason);
        }
        else{
            alert('탈퇴 처리되었습니다.');
            location.href='../';
        }
    },function(){
        alert('알 수 없는 이유로 회원탈퇴에 실패했습니다.');
    });
}

$(function(){
    $.ajax({
        type:'post',
        url:'j_get_user_info.php',
        dataType:'json'
    }).then(function(result){
        fillUserInfo(result);
    },function(){
        alert('알 수 없는 이유로, 회원 정보를 불러오지 못했습니다.');
        location.href='entrance.php';
    });

    $('#image_upload').change(changeIconPreview);

    $('#btn_remove_icon').click(function(){
        if(confirm('아이콘을 제거할까요?')){
            deleteIcon();
        }
        return false;
    });

    $('#change_pw_form').submit(changePassword);

    $('#change_icon_form').submit(changeIcon);

    $('#delete_me_form').submit(function(e){
        e.preventDefault();
        if(confirm('한 달 동안 재 가입할 수 없습니다. 정말로 탈퇴할까요?')){
            deleteMe(e);
        }
    });
})


function EntranceManage_Update() {
    Popup_Wait(function() {
        PostJSON(
            "../../i_entrance/manage/Get.php", {
            },
            function(response, textStatus) {
                if(response.result == "SUCCESS") {
                    EntranceManage_UpdateInfo(response);
                    Popup_WaitHide();
                } else {
                    Popup_WaitShow("정보 로드 실패!");
                }
            }
        )
    });
}

function EntranceManage_ChangeIcon() {
    if($("#EntranceManage_001601").val() == "") {
        Popup_Alert("파일을 선택해 주세요!");
    } else {
        Popup_Wait(function() {
            $("#formIcon").submit();
        });
    }
}

function EntranceManage_DeleteIcon() {
    Popup_Confirm('정말 실행하시겠습니까?', function() {
        Popup_Wait(function() {
            PostJSON(
                "../../i_entrance/manage/deletePost.php", {
                },
                function(response, textStatus) {
                    Popup_WaitShow(response.msg, function() {
                        EntranceManage_Update();
                    });
                }
            )
        })
    });
}

function EntranceManage_Quit() {
    var pw = $("#EntranceManage_000600").val();

    if(pw.length < 4 || pw.length > 12) {
        Popup_Alert("현재 비밀번호를 입력해주세요.", function() {
            $("#EntranceManage_000600").val("");
            $("#EntranceManage_000600").focus();
        });
        return false;
    }

    Popup_Confirm('정말 탈퇴하시겠습니까?', function() {
        Popup_Wait(function() {
            PostJSON(
                "../../i_entrance/manage/quitPost.php", {
                    pw: hex_md5(pw+""+pw)
                },
                function(response, textStatus) {
                    if(response.result == "SUCCESS") {
                        Popup_WaitShow(response.msg, function() {
                            ReplaceFrame("../../");
                        });
                    } else {
                        Popup_WaitShow(response.msg, function() {
                            EntranceManage_Update();
                        });
                    }
                }
            )
        })
    });
}
