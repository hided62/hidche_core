

function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}


function isBrightColor(color){
    color = hexToRgb(color);
    if ((color.r*0.299 + color.g*0.587 + color.b*0.114) > 140){
        return true;
    }
    else{
        return false;
    }
}

var messageTemplate = '';
var myGeneralID=null;
var isChief = false;
var lastSequence = 0;
var myNation = null;
var minMsgSeq = {
    'private':0x7fffffff,
    'public':0x7fffffff,
    'national':0x7fffffff,
}

var generalList = {};

function responseMessage(msgID, response){
    $.ajax({
        url: 'j_msg_decide_opt.php',
        type: 'post',
        dataType:'json',
        data: {
            data: JSON.stringify({
                msgID:msgID,
                response:response
            })
        }
    }).then(refreshMsg);
}

function deleteMessage(msgID){
    $.ajax({
        url: 'j_msg_delete.php',
        type: 'post',
        dataType:'json',
        data: {
            msgID:msgID
        }
    }).then(refreshMsg);
}

function refreshMsg(result){
    if(result && !result.result){
        alert(result.reason);
    }
    return redrawMsg(fetchRecentMsg(), true);
}

function fetchRecentMsg(){
    return $.ajax({
        url: 'j_msg_get_recent.php',
        type: 'post',
        dataType:'json',
        data: {
            sequence:lastSequence
        }
    });
}

function showOldMsg(msgType){
    var oldMsg = $.ajax({
        url: 'j_msg_get_old.php',
        type: 'post',
        dataType:'json',
        data: {
            to:minMsgSeq[msgType],
            type:msgType,
        }
    });
    redrawMsg(oldMsg, false);
}

function redrawMsg(deferred, addFront){
    function checkErasable(obj){

        var now = moment().format('YYYY-MM-DD HH:mm:ss');
        $('.btn-delete-msg').each(function(){
            var $btn = $(this);
            var eraseUntil = $btn.data('erase_until');
            if(eraseUntil < now){
                $btn.detach();
            }
        })
        return obj;
    }
    function checkClear(obj){
        if(!obj.result){
            var t = $.Deferred();
            t.reject();
            return t;
        }
        if(!obj.keepRecent){
            var t = $.Deferred();
            $('.msg_plate').detach();
            lastSequence = null;
            console.log('refresh!');
            redrawMsg(fetchRecentMsg(), true);
            t.reject();
            return t;
        }
        return obj;
    }
    function registerSequence(obj){
        if(!obj.result){
            var t = $.Deferred();
            t.reject();
            return t;
        }
        lastSequence = Math.max(lastSequence, obj.sequence);
        $.each(['public', 'private', 'national', 'diplomacy'], function (_, msgType) { 
             var msgList = obj[msgType];
             if(msgList === undefined || msgList.length == 0){
                 return true;
             }
             var lastMsg = msgList[msgList.length - 1];
             minMsgSeq[msgType] = Math.min(minMsgSeq[msgType], lastMsg.id);
        });
        return obj;
    }

    function refineMessageObjs(obj){
        var msgList = [obj.public, obj.private, obj.diplomacy, obj.national];
        $.each(msgList, function(){
            if(!this){
                return true;
            }

            $.each(this, function(){
                var msg = this;
                if(!msg.src.nation){
                    msg.src.nation = '재야';
                    msg.src.color = '#000000';
                }

                if(!msg.dest.nation){
                    msg.dest.nation = '재야';
                    msg.dest.color = '#000000';
                }

                msg.src.colorType = isBrightColor(msg.src.color)?'bright':'dark';
                msg.dest.colorType = isBrightColor(msg.dest.color)?'bright':'dark';
            });
        });
        return obj;
    }

    function printTemplate(obj){
        var printList = [
            [obj.public, $('#message_board .public_message'), 'public'],
            [obj.private, $('#message_board .private_message'), 'private'],
            [obj.diplomacy, $('#message_board .diplomacy_message'), 'diplomacy'],
            [obj.national, $('#message_board .national_message'), 'national'],
        ];

        $.each(printList, function(){
            var msgSource = this[0];
            var $msgBoard = this[1];
            var msgType = this[2];

            if(!msgSource || $msgBoard.length == 0){
                console.log('No Items', msgSource, $msgBoard);
                return true;
            }


            var needRefreshLastContact = (msgType == 'private');

            var now = moment().format('YYYY-MM-DD HH:mm:ss');
            //list의 맨 앞이 가장 최신 메시지임.
            var $msgs = msgSource.map(function(msg){

                var contactTarget = (msg.src.id != myGeneralID) ? msg.src.id : msg.dest.id;
                if(needRefreshLastContact && contactTarget != myGeneralID && contactTarget in generalList)
                {
                    needRefreshLastContact = false;
                    $('#last_contact').val(contactTarget).html(generalList[contactTarget].textName).show();
                }

                msg.nationID = obj.nationID;
                msg.generalName = obj.generalName;
                msg.msgType = msgType;

                if(msg.src.nation_id == msg.dest.nation_id){
                    msg.nationType = 'local';
                }
                else if(msg.nationID == msg.src.nation_id){
                    msg.nationType = 'src';
                }
                else{
                    msg.nationType = 'dest';
                }
                

                msg.defaultIcon = pathConfig.sharedIcon+'/default.jpg';
                if(msgType == 'diplomacy'){
                    msg.allowButton = permissionLevel>=4;
                }
                else{
                    msg.allowButton = true;
                }
                msg.myGeneralID = myGeneralID;
                msg.last5min = moment(msg.time).add(5, 'minute').format('YYYY-MM-DD HH:mm:ss');
                msg.now = now;
                if(msg.option && msg.option.invalid){
                    msg.invalidType = 'msg_invalid';
                }
                else{
                    msg.invalidType = 'msg_valid';
                }
                var msgHtml = TemplateEngine(messageTemplate, msg);
                

                //만약 이전 메시지와 같은 id가 온 경우 덮어씌운다.
                //NOTE:현 프로세스 상에서는 같은 id가 와선 안된다.
                var $existMsg = $('#msg_{0}'.format(msg.id));
                var $msg = $(msgHtml);
                if($existMsg.length){
                    console.log('메시지 충돌', $msg, $existMsg);
                    $existMsg.html($msg.html());
                    $msg = $existMsg;
                }

                var hideMsg = false;
                if(msg.option){
                    if(msg.option.delete !== undefined){
                        //delete는 삭제.
                        $('#msg_{0}'.format(msg.option.delete)).detach();
                    }
                    if(msg.option.overwrite !== undefined){
                        //overwrite는 숨기기.
                        $.map(msg.option.overwrite, function (overwriteID) {
                            var $msg = $('#msg_{0}'.format(overwriteID));
                            $msg.find('.btn-delete-msg').detach();
                            $msg.find('.msg_content').html('삭제된 메시지입니다.').removeClass('msg_valid').addClass('msg_invalid');
                        });
                        
                    }
                    if(msg.option.hide){
                        hideMsg = true;
                    }
                }

                if(hideMsg){
                    return null;
                }

                $msg.find('.btn-delete-msg').click(function(){
                    if(!confirm("삭제하시겠습니까?")){
                        return false;
                    }
                    deleteMessage(msg.id);
                });

                $msg.find('button.prompt_yes').click(function(){
                    if(!confirm("수락하시겠습니까?")){
                        return false;
                    }
                    responseMessage(msg.id, true);

                });
                
                $msg.find('button.prompt_no').click(function(){
                    if(!confirm("거절하시겠습니까?")){
                        return false;
                    }
                    responseMessage(msg.id, false);
                });

                if($existMsg.length){
                    return null;
                }
                else{
                    return $msg;
                }
                
            });

            if(addFront){
                $msgBoard.prepend($msgs);
            }
            else{
                $msgBoard.find('.load_old_message').before($msgs);
            }
            
        });

    }

    deferred
        .then(checkErasable)
        .then(checkClear)
        .then(registerSequence)
        .then(refineMessageObjs)
        .then(printTemplate);
}

function refreshMailboxList(obj){
    //$.ajax는 data, textStatus, jqXHR를 가진다
    //when으로 묶었으므로 data를 풀어야함.
    obj = obj[0];
    

    var $mailboxList = $('#mailbox_list');

    $mailboxList.change(function(){
        console.log($(this).val());
    })

    var oldSelected = $mailboxList.val();

    $mailboxList.empty();
    
    var $lastContact = $('#last_contact');
    var lastContact = null;
    if($lastContact.length > 0 && $lastContact.val()>=0){
        lastContact = {
            id:$lastContact.val(),
            textName:$lastContact.html()
        };
        $lastContact = null;
    }

    generalList = {};

    
    

    $.each(obj.nation, function(){
        var nation = this;
        //console.log(nation);
        var $optgroup = $('<optgroup label="{0}"></optgroup>'.format(nation.name));
        $optgroup.css('background-color', nation.color);

        if(myNation.mailbox == nation.mailbox){
            myNation.color = nation.color;
        }

        if(isBrightColor(nation.color)){
            $optgroup.css('color', 'black');
        }
        else{
            $optgroup.css('color', 'white');
        }

        nation.general.sort(function(lhs, rhs){
            if(lhs[1] < rhs[1]){
                return -1;
            }
            if(lhs[1] > rhs[1]){
                return 1;
            }
            return 0;
        });

        $.each(nation.general, function(){
            var generalID = this[0];
            var generalName = this[1];
            var isNPC = !!(this[2] & 0x2);
            var isRuler = !!(this[2] & 0x1);
            var isAmbassador = !!(this[2] & 0x4);

            

            if(generalID == myGeneralID){
                return true;
            }

            var textName = generalName;
            if(isRuler){
                textName = '*{0}*'.format(textName);
            }
            else if(isAmbassador){
                textName = '#{0}#'.format(textName);
            }

            generalList[generalID] = {
                id:generalID,
                name:generalName,
                textName:textName,
                isRuler:isRuler,
                nation:nation.nationID,
                color:nation.color
            };

            var $item = $('<option value="{0}">{1}</option>'.format(generalID, textName));

            if(permissionLevel == 4 && isAmbassador){
                $item.prop('disabled', true);
            }
            $optgroup.append($item);
        });

        $mailboxList.append($optgroup);
    });

    var $favorite = $('<optgroup label="즐겨찾기"></optgroup>');

    //아국메시지, 전체메시지
    var $ourCountry = $('<option value="{0}">【 아국 메세지 】</option>'.format(myNation.mailbox))
        .css({'background-color':myNation.color, 'color':isBrightColor(myNation.color)?'black':'white'});
    var $toPublic = $('<option value="9999">【 전체 메세지 】</option>');
    $favorite.append($ourCountry);
    $favorite.append($toPublic);

    $lastContact = $('<option id="last_contact" value="-1"></option>').hide();
    if(lastContact){
        $lastContact.show().val(lastContact.id).html(lastContact.textName);
    }
    $favorite.append($lastContact);
    //TODO:운영자를 추가하는 코드도 넣을 것.

    if(permissionLevel >= 4){
        $.each(obj.nation, function(){
            var nation = this;
            //console.log(nation);
            var $nation = $('<option value="{0}">{1}</option>'.format(nation.mailbox,nation.name));
            $nation.css('background-color', nation.color);
    
            if(isBrightColor(nation.color)){
                $nation.css('color', 'black');
            }
            else{
                $nation.css('color', 'white');
            }
            $favorite.append($nation);
        });
        
    }
    

    $mailboxList.prepend($favorite);

    if(!oldSelected){
        $mailboxList.val(myNation.mailbox);
    }
    else{
        $mailboxList.val(oldSelected);
    }
}

function registerGlobal(basicInfo){
    
    window.myNation = {
        'id':basicInfo.myNationID,
        'mailbox':basicInfo.myNationID+9000,
        'color':'#000000',
        'nation':'재야'
    };
    window.myGeneralID = basicInfo.generalID;
    window.isChief = basicInfo.isChief;
    window.myGeneralLevel = basicInfo.generalLevel;
    window.permissionLevel = basicInfo.permission;
}

function activateMessageForm(){
    var $msgInput = $('#msg_input');
    var $msgSubmit = $('#msg_submit');
    var $mailboxList = $('#mailbox_list');

    $msgInput.keypress(function (e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if (code == 13) {
            $msgSubmit.trigger('click');
            return true;
        }
    });

    $msgSubmit.click(function(){

        var text = $.trim($msgInput.val());
        $msgInput.val('').focus();

        var targetMailbox = $mailboxList.val();
        console.log(targetMailbox, text);

        var deferred;

        if(text){
            deferred = $.ajax({
                url:'j_msg_submit.php',
                type: 'post',
                dataType:'json',
                data: {
                    data: JSON.stringify({
                        mailbox:parseInt(targetMailbox),
                        text:text
                    })
                }
            });
        }
        else{
            deferred = $.Deferred();
            deferred.resolve({
                result:true,
                reason:'no_text'
            });
        }

        deferred.then(refreshMsg);
    });
}

jQuery(function($){

    //tmp_template.html은 추후 msg.js에 통합될 수 있음
    var getTemplate = $.get('js/templates/message.html?9',function(obj){
        messageTemplate = obj;
    });

    //basic_info.json은 세션값에 따라 동적으로 바뀌는 데이터임.
    var basicInfo = $.ajax({
        url:'j_basic_info.php',
        type: 'post',
        dataType:'json',
    }).then(registerGlobal);
    
    //sender_list.json 은 서버측에선 캐시 가능한 데이터임.
    var senderList = $.ajax({
        url: 'j_msg_contact_list.php',
        type: 'post',
        dataType:'json',
    });

    var MessageList = fetchRecentMsg();
        
    senderList = $.when(senderList, basicInfo)
        .then(refreshMailboxList)
        .then(activateMessageForm);
    
    $.when(MessageList, getTemplate, basicInfo, senderList)
        .then(function(){
            redrawMsg(MessageList, true);
        }).then(function(){
            $('.load_old_message').click(function(){
                var $this = $(this);
                var msgType = $this.data('msg_type');
                showOldMsg(msgType);
            })
        });
});