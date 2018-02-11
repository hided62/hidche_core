

//FIXME: ES6 template literal을 ES5에 맞게 변경
var messageTemplate = `
<table 
    width="498px" 
    border="1" 
    bordercolordark="gray" 
    bordercolorlight="black" 
    cellpadding="0" 
    cellspacing="0"
    <%if(msgType == 'private') {%>
        bgcolor="#CC6600" 
    <%} else if(msgType == 'national') {%>
        bgcolor="#336600" 
    <%} else /*$msgType == 'public'*/ {%>
        bgcolor="#000055"   
    <%} %>
    style="font-size:13px;table-layout:fixed;word-break:break-all;"
    id="msg_<%id%>"
>
    <tbody><tr>
        <td width="64px" height="64px">
            <%if(src.icon) { %>
                <img src="<%encodeURI(src.icon)%>">
            <%} else {%>
                <img src="/image/default.jpg">
            <%}%>
        </td>
        <td width="434px" valign="top">
            <%if(msgType == 'private') {%>
                <b>[
                    <font color="<%src.color%>"><%e(src.name)%>:<%e(src.nation)%></font>
                ▶
                    <font color="<%dest.color%>"><%e(dest.name)%>:<%e(dest.nation)%></font>
                ]</b>
            <%} else if(msgType == 'national' && src.nation_id == dest.nation_id){%>
                <b>[
                    <font color="<%src.color%>"><%e(src.name)%>:<%e(src.nation)%></font>
                ]</b>
            <%} else if(msgType == 'national' || msgType == 'diplomacy'){%>
                <b>[
                    <font color="<%src.color%>"><%e(src.name)%>:<%e(src.nation)%></font>
                ▶
                    <font color="<%dest.color%>"><%e(dest.nation)%></font>
                ]</b>
            <%} else {%>
                <b>[
                    <font color="<%src.color%>"><%e(src.name)%>:<%e(src.nation)%>
                ]</b>
            <%} %>
            <font size="1">&lt;<%e(time)%>&gt;</font>
            <br>
            <%e(text)%>
            <%if(this.option){ console.log('HasOption!!'); %>
                <div>
                <button class="prompt_yes btn_prompt">수락</button><button class="prompt_no btn_prompt">거절</button>
                </div>
            <%} %>
        </td>
    </tr></tbody>
</table>
`;

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
            .then(printTemplate);




    };
})();

jQuery(function($){

    refreshMsg();
});