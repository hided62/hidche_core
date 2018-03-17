

function changeInstallMode(){
    $.ajax({
        cache:false,
        type:'post',
        url:'j_install_status.php',
        dataType:'json',
    }).then(function(result){
        if(result.step == 'config'){
            $('#db_form_card').show();
            $('#admin_form_card').hide();
            return;
        }
        if(result.step == 'admin'){
            $('#db_form_card').hide();
            $('#admin_form_card').show();
            return;
        }
        if(result.step == 'done'){
            alert('설치가 완료되었습니다.');
            window.location.href = "..";
            return;
        }
        if(result.step == 'conn_fail'){
            $('#db_form_card').hide();
            $('#admin_form_card').hide();
            alert('설치 이후 DB 설정이 변경된 것 같습니다. conf.php 파일의 설정을 확인해주십시오.');
            return;
        }
        if(result.step == 'sql_fail'){
            $('#db_form_card').hide();
            $('#admin_form_card').hide();
            alert('DB가 제대로 설정되지 않았거나, 훼손된 것 같습니다. DB를 복구하거나 conf.php 파일을 삭제 후 재설치를 진행해 주십시오.');
            return;
        }

    });
}

$(document).ready( function () {
    changeInstallMode();
});