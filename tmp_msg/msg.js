

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
    data-id="<%id%>"
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
            <%} else if(msgType == 'national') {%>
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
        </td>
    </tr></tbody>
</table>
`;

jQuery(function($){

    var generalID=1;
    var sequence =null;

    function refreshMsg(){
    
    }

    refreshMsg();
});