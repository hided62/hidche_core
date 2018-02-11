

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
            <%if(src.iconPath !== null) { %>
                <img src="<%urlencode(src.iconPath)%>">
            <%} else {%>
                <img src="/image/default.jpg"> /*NOTE: image 폴더는 어느 단에서 다뤄야하는가? */
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
            <font size="1">&lt;<%e($datetime)%>&gt;</font>
            <br>
            <%e(message)%>
            <%if(this.option){ console.log('HasOption!!'); %>
                <div>
                <button class="prompt_yes btn_prompt">수락</button><button class="prompt_no btn_prompt">거정</button>
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

        deferred
            .then(registerGlobal);




    };
})();

jQuery(function($){

    refreshMsg();
});