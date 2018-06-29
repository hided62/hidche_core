$(function(){
    
    var userList = {};
    var groupList = {};
    var tGroup = [];
    var basicPath = document.location.pathname;
    basicPath = basicPath.substring(0, basicPath.lastIndexOf('/'))+'/';
    
    var $userFrame;
    
    window.groupList = groupList;
    window.userList = userList;
    
    var aGroup = {};
    
    $('#troop_list tbody > tr').each(function(idx){
        var $this = $(this);
        //console.log(this);
        //console.log($this);
        if(idx%3 == 0){
            aGroup = {
                turn : '77:77',
                turnTime : 5555,
                부대장 : '에러-집합장없음',
                부대명 : '에러-부대명없음',
                srclist : [],
                도시 : '없지롱',
                list: [],
                trs:[]
            };
            
            aGroup.trs.push($this);
            var $children = $this.children();
            var names = $.trim($children.eq(1).text()).split('【');
            var 부대명 = $.trim(names[0]);
            var 도시 = $.trim(names[1].substr(0,names[1].length-1));
            
            aGroup.도시 = 도시;
            aGroup.부대명 = 부대명;
            
            aGroup.obj = $this;
            aGroup.$users = $children.eq(3);
            
            var lists = $.trim($children.eq(3).text()).split(', ');
            lists.pop();
            aGroup.srcList = lists;
        }
        else if(idx%3 == 1){
            aGroup.trs.push($this);
            var turnBase = $this.children().eq(0).text().split('】')[1];
            //console.log(turnBase);
            var userName = $this.children().eq(1).text();
            
            var turn = turnBase;
            
            var turnMS = turnBase.split(':');
            var turnM = parseInt(turnMS[0]);
            var turnS = parseInt(turnMS[1]);
            var turnTime = turnM*60+turnS;
            
            aGroup.turn = turn;
            aGroup.turnTime = turnTime;
            aGroup.부대장 = userName;
            aGroup.obj2 = $this;
            
            groupList[aGroup.부대명] = aGroup;
            tGroup.push(aGroup);
        }
        else{
            if($this.find('input').length==0){
                $this.detach();
            }
        }
    });
    
    $.each(tGroup,function(idx,val){
        console.log(val);
        for(var i=0;i<val.trs.length;i++){
            val.trs[i].detach();
        }
    });
    tGroup.sort(function(lhs,rhs){
        return lhs.turnTime-rhs.turnTime;
    });
    
    var $last = $('table:eq(1) tr:eq(-1)');
    var $tbody = $('table:eq(1) tbody');
    $last.detach();
    console.log($last);
    $.each(tGroup,function(idx,val){
        for(var i=0;i<val.trs.length;i++){
            $tbody.append(val.trs[i]);
        }
        $tbody.append('<tr><td colspan="5"></td></tr>');
    });
    $tbody.find('tr:eq(-1)').detach();
    $tbody.append($last);
    
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
                    if(this.tagName == "TABLE"){
                        cnt+=1;
                        if(cnt==2){
                            tmpUsers = $(this);
                            return false;
                        }
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
                    
                    var $name = $this.children('.i_name');
                    $name.addClass('nameplate');
    
                    var name = $name.find('.t_name').text();
                    
                    var $도시 = $this.children('.i_city');
                    var 도시 = $.trim($도시.text());
                    
                    var $턴 = $this.children('.i_action');
                    //console.log($턴);
                    var 턴0 = $턴.text().split(':');
                    var 턴 = parseInt(턴0[0])*60 + parseInt(턴0[1]);
                    
                    var userInfo = {
                        이름 : name,
                        부대 : 부대,
                        도시 : 도시,
                        턴 : 턴,
                        obj : $this
                    };
                    
    
                    
                    groupList[부대].list.push(userInfo);
                    userList[name] = userInfo;
                    $this.hide();
                    $content.append($this);
                    
                });
                
                
                $.each(groupList,function(부대명,aGroup){
                    aGroup.$users.html('');
                    
                    $.each(aGroup.list,function(idx,userInfo){
                        var $user = $('<span><span class="name"></span><span class="other"></span></span>');
                        var $userName = $user.children('.name');
                        if(userInfo.이름 == aGroup.부대장){
                            $userName.html('*'+userInfo.이름+'*');
                            $userName.css('color','lightgreen');
                        }
                        else{
                            $userName.html(userInfo.이름);
                        }
                        
                        if(userInfo.도시 != aGroup.도시){
                            $userName.css('color','red');
                            $user.children('.other').html('【'+userInfo.도시+'】');
                        }
                        
                        $user.hover(function(){
                            var top = aGroup.obj2.offset().top + aGroup.obj2.outerHeight();
                            $userFrame.css('top',top);
                            userInfo.obj.show();
                            $userFrame.show();
                            console.log('올림!'+userInfo.이름);
                        },function(){
                            $userFrame.hide();
                            userInfo.obj.hide();
                            console.log('내림!'+userInfo.이름);
                        });
                        
                        $user.append(', ');
                        aGroup.$users.append($user);
                    });
                    
                    aGroup.$users.append('('+aGroup.list.length+'명)');
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
    
    
    
    $userFrame = $('<div id="on_mover" style="position:absolute;">'+
        '<table class="tb_layout bg0" style="width:100%;"><thead><tr>'+
        '<td width="98" align="center" class="bg1">이 름</td>'+
        '<td width="98" align="center" class="bg1"">통무지</td>'+
        '<td width="58" align="center" class="bg1">자 금</td>'+
        '<td width="58" align="center" class="bg1">군 량</td>'+
        '<td width="48" align="center" class="bg1">도시</td>'+
        '<td width="28" align="center" class="bg1">守</td>'+
        '<td width="58" align="center" class="bg1">병 종</td>'+
        '<td width="68" align="center" class="bg1">병 사</td>'+
        '<td width="48" align="center" class="bg1">훈련</td>'+
        '<td width="48" align="center" class="bg1">사기</td>'+
        '<td width="148" align="center" class="bg1">명 령</td>'+
        '<td width="58" align="center" class="bg1">삭턴</td>'+
        '<td width="58" align="center" class="bg1">턴</td>'+
    '</tr></thead><tbody class="content"></tbody></table></div>');
    $userFrame.find('thead td');
    $userFrame.css('width','900px').css('margin','0').css('padding','0').css('left','50%').css('margin-left','-450px');
    $userFrame.hide();
    
    
    
    $('body').append($userFrame);
    
});