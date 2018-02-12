

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
    if ((color.r*0.299 + color.g*0.587 + color.b*0.114) > 186){
        return true;
    }
    else{
        return false;
    }
}

var messageTemplate = '';
var generalID=null;
var isChief = false;
var sequence =null;

function refreshMsg(){
    var deferred = $.ajax({
        url: 'json_result.php',
        type: 'post',
        dataType:'json',
        contentType: 'application/json',
        data: JSON.stringify({
            sequence:sequence
        })
    });
    
    function registerGlobal(obj){
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
                    msg.src.color = '#ffffff';
                }

                if(!msg.dest.nation){
                    msg.dest.nation = '재야';
                    msg.dest.color = '#ffffff';
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

            //list의 맨 앞이 가장 최신 메시지임.
            var msgHtmls = msgSource.map(function(msg){
                msg.msgType = msgType;
                var msgHtml = TemplateEngine(messageTemplate, msg);
                return msgHtml;
            });

            var $items = $(msgHtmls.join(''));
            $msgBoard.prepend($items);
        });

    }

    deferred
        .then(registerGlobal)
        .then(refineMessageObjs)
        .then(printTemplate);




}

function refreshMailboxList(obj){
    generalID = obj.generalID;
    isChief = obj.isChief;
    
    var last = {
        'id':obj.last
    };
    var myNation = {
        'mailbox':obj.myNationMailbox,
        'color':'#000000',
        'nation':'재야'
    };

    var $mailboxList = $('#mailbox_list');

    $mailboxList.change(function(){
        console.log($(this).val());
    })

    var oldSelected = $mailboxList.val();

    $mailboxList.empty();

    $.each(obj.nation, function(){
        var nation = this;
        var $optgroup = $('<optgroup label="{0}"></optgroup>'.format(nation.nation));
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
            var isRuler = this.length>2;

            if(generalID == last.id){
                last.name = generalName;
            }

            var textName = generalName;
            if(isRuler){
                textName = '*{0}*'.format(textName);
            }

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

    //최근 대화상대
    if(last.id){
        var $last = $('<option value="{0}">{1}</option>'.format(last.id, last.name));
        $favorite.append($last);
    }

    //TODO:운영자를 추가하는 코드도 넣을 것.

    $mailboxList.prepend($favorite);

    if(!oldSelected){
        $mailboxList.val(myNation.mailbox);
    }
    else{
        $mailboxList.val(oldSelected);
    }
}

jQuery(function($){
    $.get('tmp_template.html',function(obj){
        messageTemplate = obj;
    }).then(refreshMsg);
    //refreshMsg();

    $.ajax({
        url: 'sender_list.json',
        type: 'post',
        dataType:'json',
        contentType: 'application/json',
        data: JSON.stringify({
            
        })
    }).then(refreshMailboxList);
});