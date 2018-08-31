var userFrame = '\
<tr id="userinfo_<%userID%>" data-username="<%userName%>" data-id="<%userID%>">\
    <th scope="row"><%userID%></th>\
    <td><%userName%></td>\
    <td class="small"><%emailFunc(email)%><br>(<%authType%>)</td>\
    <td><%userGradeText%><p class="small hide_text user_grade_<%userGrade%>" style="margin:0;"><%shortDate(blockUntil)%></p></td>\
    <td><%nickname%></td>\
    <td><img class="generalIcon" src="<%icon%>" width="64" height="64"></td>\
    <td class="small"><%slotGeneralList%></td>\
    <td class="small"><%shortDate(joinDate)%></td>\
    <td class="small"><%shortDate(loginDate)%></td>\
    <td class="small"><%shortDate(deleteAfter)%></td>\
    <td>\
        <div class="btn-group" role="group">\
            <button type="button" onclick="changeUserStatus(\'delete\', this);" class="btn btn-danger btn-sm">강제<br>탈퇴</button>\
            <button type="button" onclick="changeUserStatus(\'reset_pw\', this);" class="btn btn-info btn-sm">암호<br>변경</button>\
            <button type="button" onclick="changeUserStatus(\'block\', this);" class="btn btn-warning btn-sm">유저<br>차단</button>\
            <button type="button" onclick="changeUserStatus(\'unblock\', this);" class="btn btn-secondary btn-sm">차단<br>해제</button>\
            <button type="button" onclick="changeUserStatus(\'set_userlevel\', this);" class="btn btn-primary btn-sm">별도<br>권한</button>\
        </div>\
    </td>\
</tr>';

function convUserGrade(grade){
    var userGradeMap = {
        0:'차단',
        1:'일반',
        4:'특별',
        5:'부운영자',
        6:'운영자'
    };

    if(grade in userGradeMap){
        return userGradeMap[grade];
    }
    return grade;
}

function fillAllowJoinLogin(result){
    $('#radios_allow_join .active').removeClass('active');
    $('#radios_allow_login .active').removeClass('active');
    if(result.allowJoin){
        $('#allow_join_y').parent().addClass('active');
    }
    else{
        $('#allow_join_n').parent().addClass('active');
    }

    if(result.allowLogin){
        $('#allow_login_y').parent().addClass('active');
    }
    else{
        $('#allow_login_n').parent().addClass('active');
    }
}

function fillUserList(result){
    var $user_list = $('#user_list');
    

    $user_list.empty();

    var slotGeneralList = $.map(result.servers, function(value){
        return '<span class="server_generalName_{0}"></span>'.format(value)
    }).join('<br>');

    var emailFunc = function(text){
        return String(text).replace('@','@<br>');
    }
    var brFunc = function(text){
        return String(text).split(' ').join('<br>');
    };

    var shortDateFunc = function(date){
        if(!date){
            return '-';
        }
        return brFunc(date.substr(2));
    }

    $.each(result.users, function(idx, user){
        user.br = brFunc;
        user.shortDate = shortDateFunc;
        user.emailFunc = emailFunc;
        if(!user.email){
            user.email = '-';
        }
        user.slotGeneralList = slotGeneralList;
        user.userGradeText = convUserGrade(user.userGrade);

        $user_list.append(
            TemplateEngine(userFrame, user)
        )
    });

    //TODO: slotGeneralList에 값을 채워야함. ajax로 받아올 필요 있음
}

function changeSystem(action, param=null){
    var text = '{0}{1}을 진행합니다.'.format(action, param?(', '+param):'');
    if(!confirm(text)){
        return;
    }

    $.ajax({
        type:'post',
        url:'j_set_userlist.php',
        dataType:'json',
        data:{
            'action':action,
            'param':param
        }
    }).then(function(result){
        if(result.result){
            if(result.affected !== undefined){
                alert('{0}건이 처리되었습니다.'.format(result.affected));
                refreshAll();
            }
            else{
                alert('완료되었습니다.');
            }
            
        }
        else{
            alert(result.reason);
        }
    });
}

function changeUserStatus(action, userID, param=null){
    if(userID instanceof Element){
        userID = parseInt($(userID).parents('tr').data('id'));
    }
    if(!$.isNumeric(userID)){
        alert('userID가 올바르게 지정되지 않았습니다!');
        return;
    }
    userID = parseInt(userID);

    
    if(action == 'set_userlevel'){
        if(!$.isNumeric(param)){
            param = prompt('원하는 등급을 입력해주세요.(1:일반, 4:특별, 5:부운영자, 6:운영자)', '1');
        }
        param = parseInt(param);
        
        if(param < 1 || param > 6){
            alert('올바르지 않습니다.');
            return;
        }
    }

    if(action == 'block'){
        if(!$.isNumeric(param)){
            param = prompt('블록 기간을 입력해주세요. <= 0은 반영구(50년)입니다.', '7');
        }
        param = parseInt(param);
    }

    var userName = $('#userinfo_'+userID).data('username');

    var text = '{0}에 대해서 {1}{2}을 진행합니다.'.format(userName, action, param?(', '+param):'');
    if(!confirm(text)){
        return;
    }


    $.ajax({
        type:'post',
        url:'j_set_userlist.php',
        dataType:'json',
        data:{
            'action':action,
            'user_id':userID,
            'param':param
        }
    }).then(function(result){
        if(result.result){
            if(result.detail !== undefined){
                alert('완료되었습니다. : {0}'.format(result.detail));
                refreshAll();
            }
            else{
                alert('완료되었습니다.');
                refreshAll();
            }
            
        }
        else{
            alert(result.reason);
        }
    });
    
}

function refreshAll(){
    $.ajax({
        type:'post',
        url:'j_get_userlist.php',
        dataType:'json'
    }).then(function(result){
        if(!result.result){
            alert(reuslt.reason);
            return;
        }

        fillAllowJoinLogin(result);
        fillUserList(result);
    });
}

$(function(){
    refreshAll();
    
    $('input[name=allow_join], input[name=allow_login]').on('change', function(){
        var $this = $(this);
        changeSystem($this.attr('name'), $this.val());
    })
});
