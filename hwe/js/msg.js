

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
var sequence =null;
var myNation = null;
var lastMsg = null;

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

function refreshMsg(result){
    if(result && !result.result){
        alert(result.reason);
    }
    return redrawMsg(fetchMsg());
}

function fetchMsg(){
    return $.ajax({
        url: 'j_msg_get_recent.php',
        type: 'post',
        dataType:'json',
        data: {
            sequence:sequence
        }
    });
}

function redrawMsg(deferred){

    function registerSequence(obj){
        if(!obj.result){
            deferred.reject();
            return;
        }
        sequence = obj.sequence;
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

            if(msgType == 'diplomacy'){
                //외교는 항상 새로 그린다
                $msgBoard.empty();
            }


            var needRefreshLastContact = (msgType == 'private');

            //list의 맨 앞이 가장 최신 메시지임.
            var $msgs = msgSource.map(function(msg){

                var contactTarget = (msg.src.id != myGeneralID) ? msg.src.id : msg.dest.id;
                if(needRefreshLastContact && contactTarget != myGeneralID)
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

                if(msg.option && msg.option.parent){
                    //parent는 삭제.
                    $('#msg_{0}'.format(msg.option.parent)).detach();
                }

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

            $msgBoard.prepend($msgs);
        });

    }

    deferred
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

        $.each(nation.general, function(){
            var generalID = this[0];
            var generalName = this[1];
            var isNPC = !!(this[2] & 0x2);
            var isRuler = !!(this[2] & 0x1);

            

            if(generalID == myGeneralID){
                return true;
            }

            var textName = generalName;
            if(isRuler){
                textName = '*{0}*'.format(textName);
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

    if(myGeneralLevel >= 5){
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
    
    myNation = {
        'id':basicInfo.myNationID,
        'mailbox':basicInfo.myNationID+9000,
        'color':'#000000',
        'nation':'재야'
    };
    lastMsg = {
        id : basicInfo.lastContact
    };
    myGeneralID = basicInfo.generalID;
    isChief = basicInfo.isChief;
    myGeneralLevel = basicInfo.generalLevel;
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
    var getTemplate = $.get('js/templates/message.html',function(obj){
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

    var MessageList = fetchMsg();
        
    senderList = $.when(senderList, basicInfo)
        .then(refreshMailboxList)
        .then(activateMessageForm);
    
    $.when(MessageList, getTemplate, basicInfo, senderList)
        .then(function(){
            redrawMsg(MessageList);
        });
});