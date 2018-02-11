

var messageTemplate = '';

var refreshMsg = (function(){
    var generalID=null;
    var isChief = false;
    var sequence =null;
    
    return function(){
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
            generalID = obj.generalID;
            isChief = obj.isChief;
            sequence = obj.sequence;
            return obj;
        }

        function refineMessageObjs(obj){
            var msgList = [obj.public, obj.private, obj.diplomacy, obj.national];
            $.each(msgList, function(){
                if(!this){
                    return true;
                }

                console.log(this);

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

                //list의 맨 앞이 가장 최신 메시지임.
                var msgHtmls = msgSource.map(function(msg){
                    msg.msgType = msgType;
                    var msgHtml = TemplateEngine(messageTemplate, msg);
                    console.log(msgHtml);
                    return msgHtml;
                });

                var $items = $(msgHtmls.join(''));
                console.log($items);
                $msgBoard.prepend($items);
            });

        }

        deferred
            .then(registerGlobal)
            .then(refineMessageObjs)
            .then(printTemplate);




    };
})();

jQuery(function($){
    $.get('tmp_template.html',function(obj){
        messageTemplate = obj;
    }).then(refreshMsg);
    //refreshMsg();
});