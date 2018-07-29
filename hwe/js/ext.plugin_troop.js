$(function(){
    
    var userList = {};
    var groupList = {};
    var tGroup = [];
    var basicPath = document.location.pathname;
    basicPath = basicPath.substring(0, basicPath.lastIndexOf('/'))+'/';
    
    var $userFrame;
    
    window.userList = userList;
    
    var runAnalysis = function(){
        $.each(groupList,function(idx,val){
            val.list = [];
        });
        userList = [];
        var $content = $('#on_mover .content');
        $content.html('');
        $.get(basicPath+'b_genList.php',function(rawData){
            
            try{
                $html = $(rawData);
                var cnt =0;
                
                var tmpUsers = {};
                $html.each(function(idx){
                    var $this = $(this);
                    if($this.attr('id') == "general_list"){
                        tmpUsers = $(this);
                        return false;
                    }
                });
                
                tmpUsers.find("tbody > tr").each(function(idx){
                    var $this = $(this);
                    var $부대 = $this.children('.i_troop');
                    
                    var 부대 = $.trim($부대.text());
                    
                    if(부대 == '-'){
                        //부대 안탔음!
                        return true;
                    }
                    $부대.remove();
                    
                    var generalID = parseInt($this.data('general-id'));
                    userList[generalID] = $this;
                    $this.hide();
                    $content.append($this);
                });
                
                $('.troopUser').hover(function(){
                    var $this = $(this);
                    var parent = $this.closest('tr');
                    var generalID = parseInt($this.data('general-id'));
                    console.log(generalID);
                    var top = parent.offset().top + parent.outerHeight();
                    $userFrame.css('top',top);
                    userList[generalID].show();
                    $userFrame.show();
                },function(){
                    var $this = $(this);
                    var parent = $this.closest('tr');
                    var generalID = parseInt($this.data('general-id'));
                    userList[generalID].hide();
                    $userFrame.hide();
                });
            }
            catch(err){
                console.log(err);
            }
        });
        
        
    };
    
    var $frame = $('table:eq(0) td:eq(0)');
    $frame.find('br:last').remove();

	var $btn = $('<input type="button" value="암행부 연동">');
    $btn.click(function(){
        runAnalysis();
    });
    
    $frame.append($btn);
    
    
    
    $userFrame = $("<div id='on_mover' style='position:absolute;'>"+
        "<table class='tb_layout bg0' style='width:100%;'><thead>"+
        "<tr>"+
            "<td width=98 class='bg1 center'>이 름</td>"+
            "<td width=98 class='bg1 center'>통무지</td>"+
            "<td width=53 class='bg1 center'>자 금</td>"+
            "<td width=53 class='bg1 center'>군 량</td>"+
            "<td width=48 class='bg1 center'>도시</td>"+
            "<td width=28 class='bg1 center'>守</td>"+
            "<td width=58 class='bg1 center'>병 종</td>"+
            "<td width=63 class='bg1 center'>병 사</td>"+
            "<td width=38 class='bg1 center'>훈련</td>"+
            "<td width=38 class='bg1 center'>사기</td>"+
            "<td width=213 class='bg1 center'>명 령</td>"+
            "<td width=38 class='bg1 center'>삭턴</td>"+
            "<td width=48 class='bg1 center'>턴</td>"+
        "</tr>"+
    "</thead></thead><tbody class='content'></tbody></table></div>");
    $userFrame.find('thead td');
    $userFrame.css('width','960px').css('margin','0').css('padding','0').css('left','50%').css('margin-left','-480px');
    $userFrame.hide();
    
    
    
    $('body').append($userFrame);
    
});