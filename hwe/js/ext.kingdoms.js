$(function(){
    
    var basicPath = document.location.pathname;
    basicPath = basicPath.substring(0, basicPath.lastIndexOf('/'))+'/';
    var headTbl = $('table:eq(0)');
    var $userFrame;
    
    var 국가테이블= $('table:gt(0):lt(-2)');
    
    var getUserType = function(통,무,지){
        var 총 = 통+무+지;
               
        if(통 < 총*0.2)	{
            return "무지";
        }else if(무 < 총*0.2)	{
            return "지";
        }else if(지 < 총*0.2)	{
            return "무";
        }else{
            return "만능";
        }
    };
    
    function formatScore(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    var runAnalysis = function(){
        var $content = $('#on_mover .content');
        $.get(basicPath+'a_genList.php',function(rawData){
            try{
                var $html = $(rawData);
                
                var $장수일람 = {};
                
                var 국가별 = {};
                var cnt =0;
                $html.each(function(idx){
                    
                    if(this.tagName == "TABLE"){
                        cnt+=1;
                        if(cnt==2){
                            $장수일람 = $(this);
                            return false;
                        }
                    }
                });
                $장수일람.find('tr:gt(0)').each(function(){
                    var 장수 = {};
                    $this = $(this);
                    $tds = $this.find('td');
                    
                    var 장수명 = $.trim($tds.eq(1).text());
                    var 국가 = $.trim($tds.eq(6).text());
                    
                    장수.html = $this.clone();
                    장수.장수명 = 장수명;
                    장수.국가 = 국가;
                    장수.벌점 = parseInt($tds.eq(-1).text());
                    장수.통 = parseInt($tds.eq(10).text().split('+')[0]);
                    장수.무 = parseInt($tds.eq(11).text().split('+')[0]);
                    장수.지 = parseInt($tds.eq(12).text().split('+')[0]);
                    장수.삭턴 = parseInt($tds.eq(-2).text());
                    장수.종류 = getUserType(장수.통, 장수.무, 장수.지);
                    장수.의병 = 장수명[0]=="ⓜ" || 장수명[0]=="ⓖ";
                    if(!(국가 in 국가별)){
                        국가별[국가] = {};
                        국가별[국가].무 = [];
                        국가별[국가].지 = [];
                        국가별[국가].충차 = [];
                        국가별[국가].무지 = [];
                        국가별[국가].만능 = [];
                        //국가별[국가].의병 = [];
                    }
                    
                    //if(장수.의병) 국가별[국가].의병.push(장수);
                    국가별[국가][장수.종류].push(장수);
                    
                    장수.html.hide();
                    $content.append(장수.html);
                    
                    
                });
                
                국가테이블.each(function(idx){
                    var $this = $(this);
                    var $tbl = $this;
                    var $td = $this.find('td:last');
                    var name = $.trim($this.find('td:first').text());
                    name = name.substr(2,name.length-4);
                    
                    var 국가정보 = 국가별[name];
                    
                    var total = 0;
                    var 전투유저장수 = 0;
                    var 삭턴장수 = 0;
                    var 통솔합 = 0;
                    $td.html('<p class="sum" style="margin:0;font-weight:bold;color:yellow;text-align:center"></p>');
                    $td.css('text-indent','-5.8em').css('padding-left','5.8em');
                    for(var 종류명 in 국가정보){
                        
                        var $p = $("<p></p>").css('margin','0');
                        
                        var 테이블 = 국가정보[종류명];
                        
                        if(테이블.length == 0)continue;
                        
                        테이블.sort(function(좌, 우){
                            if(우.벌점 == 좌.벌점){
                                return 좌.장수명 > 우.장수명 ? 1 : 0;
                            }
                            return 우.벌점 - 좌.벌점
                        });
                        
                        
                        var text = "　　"+종류명;
                        text = text.substr(text.length-2);
                        $p.append(text+'장(');
                        
                        text = ""+테이블.length;
                        
                        $p.append(text + ')');
                        if(text.length<3){
                            $p.append("<span style='display:inline-block;width:"+(3-text.length)/2+"em;'>&nbsp;</span>");
                        }
                        $p.append(': ');
                        
                        total += 테이블.length;
                        
                        $.each(테이블,function(idx,val){
							                      
                            var 종능 = val.통 + val.무 + val.지;
                            if(종류명 == '무' || 종류명 == '지' || 종류명 == '충차'){
                                if(val.삭턴 >= 80 && !val.의병){
                                    전투유저장수+=1;
                                    
                                    if(종능 > 150)         통솔합 += val.통;
                                    else if(종능/0.75 >= 150)통솔합 += parseInt(val.통/0.75);
                                    else if(종능/0.55 >= 150)통솔합 += parseInt(val.통/0.55);
                                    else if(종능/0.35 >= 150)통솔합 += parseInt(val.통/0.35);
                                    else if(종능/0.15 >= 150)통솔합 += parseInt(val.통/0.15);
                                }
                            }
                            
                            var $obj = $('<span></span>');
                            var $obj2 = $('<span></span>');
                            $obj.html(val.장수명);
                            
                            if(!val.의병 && val.삭턴 < 80){
                                $obj.css('text-decoration','line-through');
                                삭턴장수+=1;
                            }
                            if(val.의병){
                                $obj.css('color','cyan');
                            }
                            if(val.벌점 >= 1500) $obj.css('color','yellow');
                            else if(val.벌점 >= 200) $obj.css('color','lightgreen');
                            
                            $obj2.append($obj);
                            if(idx < 테이블.length-1){
                                $obj2.append(', ');
                            }
                            $p.append($obj2);
                            $obj2.hover(function(){
                                var top = $tbl.offset().top + $tbl.outerHeight() + 3;
                                $userFrame.css('top',top);
                                val.html.show();
                                $userFrame.show();
                                console.log('올림!'+val.장수명);
                            },function(){
                                $userFrame.hide();
                                val.html.hide();
                                console.log('내림!'+val.장수명);
                            });
                            
                            
                            
                        });
                        $td.append($p);
                    }
                    
                    var result = "* 총("+total+"), 전투장("+전투유저장수+", 약 "+formatScore(통솔합*100)+"명), 삭턴장("+삭턴장수+") *";
                    $tbl.find('.sum').html(result);
                    
                    
                });
            }
            catch(err){
                console.log(err);
            }
        });
        
    }
    
    $userFrame = $('<div id="on_mover" style="position:absolute;">'+
        '<table class="tb_layout bg0" style="width:100%;"><thead><tr>'+
        '<td width="64" align="center" class="bg1">얼 굴</td>'+
        '<td width="100" align="center" class="bg1">이 름</td>'+
        '<td width="50" align="center" class="bg1">연령</td>'+
        '<td width="50" align="center" class="bg1">성격</td>'+
        '<td width="90" align="center" class="bg1">특기</td>'+
        '<td width="50" align="center" class="bg1">레 벨</td>'+
        '<td width="100" align="center" class="bg1">국 가</td>'+
        '<td width="60" align="center" class="bg1">명 성</td>'+
        '<td width="60" align="center" class="bg1">계 급</td>'+
        '<td width="80" align="center" class="bg1">관 직</td>'+
        '<td width="45" align="center" class="bg1">통솔</td>'+
        '<td width="45" align="center" class="bg1">무력</td>'+
        '<td width="45" align="center" class="bg1">지력</td>'+
        '<td width="45" align="center" class="bg1">삭턴</td>'+
        '<td width="84" align="center" class="bg1">벌점</td>'+
    '</tr></thead><tbody class="content"></tbody></table></div>');
    $userFrame.find('thead td');
    $userFrame.css('width','1000px').css('margin','0').css('padding','0').css('left','50%').css('margin-left','-500px');
    $userFrame.css('box-shadow','0px 0px 7px 3px rgba(255,255,255,50)');
    $userFrame.hide();
    
    $('body').append($userFrame);
    
    var $frame = $('table:eq(0) td:eq(0)');
    $frame.find('br:last').remove();
    
	var $btn = $('<input type="button" value="장수 일람 연동">');
    $btn.click(function(){
        runAnalysis();
        $btn.prop("disabled",true);
        var $tr0 = $('table:eq(0) tr:eq(0)');
    	$tr0.append('<td><strong>*벌점 순 정렬*</strong><br><span style="color:yellow">벌점 1500점 이상</span>, <span style="color:lightgreen">벌점 200점 이상</span>, '+
                    '<span style="text-decoration:line-through">삭턴 장</span>, <span style="color:cyan">ⓝ장</span>'+'<br><strong>전투장 :</strong> 무장 + 지장 + 충차장 - 삭턴자(무,지,충) </td>');
    });
    
    
    
    $frame.append($btn);
});