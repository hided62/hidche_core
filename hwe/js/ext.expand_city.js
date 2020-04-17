
$(function() {
    
    var cityList = {};
    var userList = {};
    
    var cityGroupOrder = ['하북','중원','서북','서촉','남중','초','오월','동이'];
    var city규모 = {특:1,대:2,중:3,소:4,이:5,진:6,관:7,수:8};
    window.cityList = cityList;
    window.userList = userList;

    var basicPath = document.location.pathname;
    basicPath = basicPath.substring(0, basicPath.lastIndexOf('/'))+'/';
    
    
    
    var mergeSort = function(arr,cmpFunc){

        if(typeof cmpFunc == "undefined"){
            cmpFunc = function(a,b){
                if(a<b) return -1;
                if(a>b) return 1;
                return 0;
            }
        }
        
        var merge = function(left,right){
            var retVal=[];
            
            var leftIdx=0;
            var rightIdx=0;
            
            while(leftIdx<left.length && rightIdx<right.length){
                var cmpVal = cmpFunc(left[leftIdx],right[rightIdx]);
                
                if(cmpVal<=0){
                    retVal.push(left[leftIdx]);
                    leftIdx++;
                }
                else{
                    retVal.push(right[rightIdx]);
                    rightIdx++;
                }
            }
            
            retVal = retVal.concat(left.slice(leftIdx)).concat(right.slice(rightIdx))
            
            return retVal;
        };
        
        var _mergeSort = function(arr){
            if(arr.length<2){
                return arr;
            }
            
            var middle = Math.floor(arr.length/2);
        
            var left = arr.slice(0,middle);
            var right = arr.slice(middle);
            
            return merge(_mergeSort(left),_mergeSort(right));
        };
        
        return _mergeSort(arr);
        
    };
    
    window.mergeSort = mergeSort;
    
    var extDutyWindow = function(userInfo){
        window.currUser = userInfo;
        //도시 순서 재구성
        var subCityGroupList = {};
        var subCityList = {};
        
        var tmpOldVal = 0;
        
        //var callCity = function(num){console.log('city no.'+num+' is clicked!')};
        
        if($('#ext_win').length==0){
            
            var $win = $('<div id="ext_win" style="padding:0;" title="관직 임명"><table style="padding:0;margin:0;" cellspacing="0" cellpadding="0" border="0"><tr><td width="700px" style="padding:0;"><iframe id="in_frame" src="map.php?type=1&amp;graphic=1" width="700" height="520" frameborder="0" marginwidth="0" marginheight="0" topmargin="0" scrolling="no"></iframe></td>'+
                         '<td width="305px" style="padding:0;vertical-align:top;height:520px;"><div style="height:520px;width:305px;overflow-y:scroll;overflow-x:hidden;">'+
                         '<table id="inner_table"><thead><tr><th width="35">도시</th><th width="85">태수</th><th width="85">군사</th><th width="85">종사</th></tr></thead><tbody class="content">'+
                         '</tbody></table></div></td></tr></table>'+
                         '<form name="form1" id="fake_form"><span id="user_name"></span> : <select name="double" id="city_select"></select>'+
                         '<div id="duty_radio" style="display:inline;">'+
                         '<input type="radio" id="duty_type1" value="4" name="duty_radio"><label for="duty_type1">태수</label>'+
						 '<input type="radio" id="duty_type2" value="3" name="duty_radio"><label for="duty_type2">군사</label>'+
						 '<input type="radio" id="duty_type3" value="2" name="duty_radio"><label for="duty_type3">종사</label>'+
                         '</div></form></div>');
        	$win.hide();
            
            $win.css('font-size','9pt');
            
            
            
            $('#fake_form').css('display','inline');
        	$('body').append($win);
            
            $('#inner_table').attr('border','1').attr('cellspacing',"0").attr('cellpadding',"0")
            .attr('bordercolordark',"gray").attr('bordercolorlight',"black").attr('align','center')
            .css('font-size','13px').css('width','290px');

            $('#inner_table thead tr');
            
            $('#fake_form').submit(function(){
                return false;
            });
            
            $('#in_frame').load(function(){
                $('#in_frame').contents().find('div').click(function(){$('#city_select').change();});
            });
            
            $("#duty_radio").buttonset();
            
            var $city_select = $('#city_select');
            $city_select.css('color','white').css('background-color','black');
            
            
            $city_select.change(function(event){
                if($city_select.val()==null){
                    $city_select.val(tmpOldVal);
                    return false;
                }
                
                if($city_select.val() == tmpOldVal){
                    return false;
                }
                
                tmpOldVal = $city_select.val();
                //console.log(tmpOldVal);                
                var newInfo = subCityList[tmpOldVal];
                var cityInfo = newInfo.city;
                
                var p태수 = cityInfo.p태수 && currUser.p태수;
            	var p군사 = cityInfo.p군사 && currUser.p군사;
            	var p종사 = cityInfo.p종사 && currUser.p종사;
                
                //console.log(p태수,p군사,p종사);
                
                $('#duty_type1').button({disabled:!p태수});   
                $('#duty_type2').button({disabled:!p군사});  
                $('#duty_type3').button({disabled:!p종사});  
                
                var $태수 = cityInfo.$태수;
                var $군사 = cityInfo.$군사;
                var $종사 = cityInfo.$종사;
                
                
                
                return true;
            });
            
            
            $win.dialog({
                autoOpen:false,
                width:1005,
                height:680,
                buttons:{
                    "임명":function(){
                        //console.log("임명!");
                        
                        var $checked = $('#duty_radio :radio:checked');
                        
                        if($checked.length==0 || $checked.attr('disabled')=='disabled'){
                            alert('직책을 선택해주세요!');
                            return false;
                        }
                        var type = $checked.attr('value');
                        var text = $checked.next().text();
                        var userVal = currUser.val;
                        var userName = currUser.name;
                        var cityVal = $city_select.val();
                        
                        var cityInfo = subCityList[cityVal].city;
                        
                        $.post(basicPath+'j_myBossInfo.php',{
                            destCityID:cityVal,
                            destGeneralID:userVal,
                            officerLevel:type,
                            action:'임명'
                        },function(rawData){
                            
                            cityInfo['p'+text]=false;
                            var $target = cityInfo.users.find('.mode_'+type);
                            $target.prop('disabled',true);
                            $target.css('background','transparent');
                            $target.css('border','0');
                            $target.css('color','');
                            
                            cityInfo['$'+text].html(userName);
                            
                            $win.dialog("close");
                        });
                        
                       
                    },
                    "닫기":function(){
                        $win.dialog("close");
                    }
                    
                }
            });
            
            
            
            //console.log($win.parent());
            $win.parent().css('font-size','10pt');
        }
        
        $('#ext_win').dialog("close").dialog( "option", "position", { my: "center top", at: "center bottom", of: userInfo.$user} );
        
        var $innerContent = $('#inner_table .content');
        $innerContent.html('');
        $("#user_name").html(userInfo.name+'['+userInfo.city+']');
        
        $('#city_select').html('');
        
        $.each(cityList,function(idx,cityInfo){
            var 지역 = cityInfo.지역;
            
            var p태수 = cityInfo.p태수 && currUser.p태수;
            var p군사 = cityInfo.p군사 && currUser.p군사;
            var p종사 = cityInfo.p종사 && currUser.p종사;
            
            var newInfo = {
                지역 : 지역,
                규모 : cityInfo.규모,
                이름 : cityInfo.이름,
                val : cityInfo.val,
                city : cityInfo,
                p태수 : p태수,
                p군사 : p군사,
                p종사 : p종사
            };
            
            //console.log(newInfo);
            
            if(p태수 || p군사 || p종사){
                if(typeof subCityGroupList[지역] == 'undefined'){
                    subCityGroupList[지역] = [];
                }
                subCityGroupList[지역].push(newInfo);
                subCityList[cityInfo.val] = newInfo;
            }
            
        });
        
        $.each(cityGroupOrder,function(idx,groupName){
            if(typeof subCityGroupList[groupName] == 'undefined'){
                
                return true;
            }
            
            var subList = subCityGroupList[groupName];
            
            var $group = $('<tr><td colspan="4" style="color:skyblue;">【 '+groupName+' 】</td></tr>');
            $innerContent.append($group);
            
            subList.sort(function(a,b){
                
                var cmp규모 = city규모[a.규모] - city규모[b.규모];
                if(cmp규모 != 0) return cmp규모;
                
                return a.이름.localeCompare(b.이름);
            });
            var $optgroup = $('<optgroup label=" 【 '+groupName+' 】 " style="color:skyblue;"></optgroup>');
            
            $.each(subList,function(idx,newInfo){
                $optgroup.append('<option value="'+newInfo.val+'" style="color:white;">'+newInfo.이름+'</option>');
                
                var cityInfo = newInfo.city;
                var $city = $('<tr><td>'+newInfo.이름+'</td></tr>');
                
                var $태수 = cityInfo.$태수.clone();
                $city.append($태수);
                if(!newInfo.p태수){
                    $태수.css('color','red');
                }
                else{
                    $태수.click(function(){
                        $('#duty_radio :radio:eq(0)').attr('checked','checked');
                        $('#duty_radio :radio').button('refresh');
                    }).css('cursor','pointer');
                }
                
                
                var $군사 = cityInfo.$군사.clone();
                $city.append($군사);
                if(!newInfo.p군사)$군사.css('color','red');
                else{
                    $군사.click(function(){
                        $('#duty_radio :radio:eq(1)').attr('checked','checked');
                        $('#duty_radio :radio').button('refresh');
                    }).css('cursor','pointer');
                }
                
                var $종사 = cityInfo.$종사.clone();
                $city.append($종사);
                if(!newInfo.p종사)$종사.css('color','red');
                else{
                    $종사.click(function(){
                        $('#duty_radio :radio:eq(2)').attr('checked','checked');
                        $('#duty_radio :radio').button('refresh');
                    }).css('cursor','pointer');
                }
                
                $innerContent.append($city);
                $city.click(function(){
                    $('#city_select').val(newInfo.val).change();
                });
                
            });
            
            $('#city_select').append($optgroup);
        });
        
        tmpOldVal = $('#city_select').val();
        $('#city_select').change();
        $('#ext_win').dialog("open");
    };
    
    var loadDuty = function(){
        
        try{
            $('.for_duty').remove();
            $.get(basicPath+'b_myBossInfo.php',function(rawData){
                $html = $(rawData);
                //window.$html = $html;
                
                var cnt =0;
                var $tmpTable = $html.filter('#officer_list').eq(0);
                
                $selects = $tmpTable.find("select");
                if($selects.length == 0){
                    alert("수뇌가 아닙니다!");
                    return false;
                }
                
                var setUserAvailable = function($userList,typeName){
                    $userList.each(function(idx){
                        var $this = $(this);
                        
                        var val = $this.val();
                        var name = $.trim($this.text());
                        
                        for(var i=name.length-1;i>0;i--){
                            if(name[i]=='【'){
                                name = $.trim(name.substr(0,i));
                                break;
                            }
                        }
                        
                        if(val == '0'){
                            return true;
                        }
                        
                        if(typeof userList[name] != 'undefined'){
                            userList[name].val = val;
                        	userList[name][typeName] = true;
                        }
                        
                    });
                };
                
                var setCityAvailiable = function($cityList,typeName){
                    $cityList.each(function(idx){
                        
                        var $this = $(this);
                        
                        var val = $.trim($this.val());
                        var name = $.trim($this.text());
                        
                        cityList[name].val = val;
                        cityList[name][typeName] = true;
                    });
                }
                
                $.each(cityList,function(idx,cityInfo){
                	cityInfo.p태수=false;
                    cityInfo.p군사=false;
                    cityInfo.p종사=false;
                });
                
                $.each(userList,function(idx,userInfo){
                    userInfo.p태수=false;
                    userInfo.p군사=false;
                    userInfo.p종사=false;
                });
                
                setUserAvailable($selects.eq(1).find("option"),"p태수");
                setUserAvailable($selects.eq(3).find("option"),"p군사");
                setUserAvailable($selects.eq(5).find("option"),"p종사");
                
                setCityAvailiable($selects.eq(0).find("option"),"p태수");
                setCityAvailiable($selects.eq(2).find("option"),"p군사");
                setCityAvailiable($selects.eq(4).find("option"),"p종사");

                
                $.each(cityList,function(idx,cityInfo){
                    
                    //console.log(cityInfo.users.children());
                    
                    cityInfo.users.children().each(function(idx){
                        //console.log(this);
                        var $this = $(this);
                        
                        var username = $this.data('username');
                        
                        var userInfo = userList[username];
                        if(!userInfo){
                            return true;
                        }
                        
                        if(userInfo.val == '-1'){
                            return true;
                        }
                        
                        $name = $this.find('.nameplate');
                        
                        $name.append('<br class="for_duty">');
                        
                        
                        var addBtn=function($name,cityInfo,userInfo,type,text,warn){
                            
                            var enabled = cityInfo['p'+text]&&userInfo['p'+text];
                            var cityVal = cityInfo.val;
                            var $btn = $('<button type="button">'+text.substr(0,1)+'</button>');
                            $btn.addClass('mode_'+type);
                            $btn.addClass('for_duty');
                            
                            if(!enabled){
                                $btn.prop('disabled',true);
                                $btn.css('background','transparent');
                                $btn.css('border','0');
                            }
                            else{
                                if(userInfo.is수뇌){
                                	$btn.css('color','red');
                            	}
                            }
                            
                            $btn.css('padding','1px 4px');
                            $btn.css('margin','0');
                            
                            $btn.click(function(){
                                if(userInfo.is수뇌){
                                    if(!confirm('수뇌입니다. 임명할까요?')){
                                        return false;
                                    }
                                }
                                
                                $.post(basicPath+'j_myBossInfo.php',{
                                    destCityID:cityVal,
                                    destGeneralID:userInfo.val,
                                    officerLevel:type,
                                    action:'임명'
                                },function(rawData){
                                    
                                    cityInfo['p'+text]=false;
                                    var $target = cityInfo.users.find('.mode_'+type);
                                    $target.prop('disabled',true);
                                    $target.css('background','transparent');
                                    $target.css('border','0');
                                    $target.css('color','');
                                    
                                    cityInfo['$'+text].html(userInfo.name);
                                });
                            }); 
                            
                            //console.log($btn);
                            $name.append($btn);
                        };
                        
                        addBtn($name,cityInfo,userInfo,4,'태수');
                        addBtn($name,cityInfo,userInfo,3,'군사');
                        addBtn($name,cityInfo,userInfo,2,'종사');
                        
                        //특수 버튼!
                        if(userInfo.p태수||userInfo.p군사||userInfo.p종사){
                            var $btn = $('<button type="button">...</button>');
                            $btn.addClass('for_duty');
                            if(userInfo.is수뇌){
                                $btn.css('color','red');
                            }
                            $btn.css('padding','1px 4px');
                            $btn.css('margin','0');
                            
                            $btn.click(function(){
                                if(userInfo.is수뇌){
                                    if(!confirm('수뇌입니다. 임명할까요?')){
                                        return false;
                                    }
                                }
                                
                                extDutyWindow(userInfo);
                            });
                            
                            $btn.css('border','solid 1px Chocolate').css('background-color','#400000').css('margin-left','4px');
                            $btn.css('line-hight','20px').css('color','GhostWhite').css('padding','2px 2px').css('height','18px').css('line-height','8px');
                            
                            //$name.append($btn);
                            
                        }
                    });
                });
                
            });
            
            
        }
        catch(a){
            console.log(a);
        }
        
        return false;
    };
    
    var loadUser = function(){
        $.each(cityList,function(idx,val){
            if(typeof val.users == "undefined"){
                val.obj.append('<tr><td colspan="12"><table align="center" class="tb_layout cityUser bg0">'+
                               '<thead><tr>'+
                               '<td width="100" align="center" class="bg1">이 름</td><td width="100" align="center" class="bg1">통무지</td><td width="100" align="center" class="bg1">부 대</td><td width="60" align="center" class="bg1">자 금</td>'+
                               '<td width="60" align="center" class="bg1">군 량</td><td width="30" align="center" class="bg1">守</td><td width="60" align="center" class="bg1">병 종</td>'+
                               '<td width="60" align="center" class="bg1">병 사</td><td width="50" align="center" class="bg1">훈련</td><td width="50" align="center" class="bg1">사기</td><td width="150" align="center" class="bg1">명 령</td>'+
                               '<td width="60" align="center" class="bg1">삭턴</td><td width="60" align="center" class="bg1">턴</td>'+
                               '</tr></thead>'+
                               '<tbody class="cityUserBody"></tbody></table></td></tr>');
                
                val.users = val.obj.find(".cityUserBody");
            }
            else{
                val.users.html("");
            }
        });
        
        
        $.get(basicPath+'b_genList.php',function(rawData){
            var cnt =0;
            
            var $helper = $('#helper_genlist');
            $helper.html('').append($.parseHTML(rawData));

            var tmpUsers = $('#general_list tbody tr');
            
            tmpUsers.each(function(idx){
                var $this = $(this);
                
                var $city = $this.children('.i_city');
                $city.remove();
                var cityName = $.trim($city.text());
                
                var $name = $this.children('.i_name');
                $name.addClass('nameplate');

				var name = $name.find('.t_name').text();
                                
                var $work = $this.children('.i_action');
                
                var cityInfo = cityList[cityName];
                if(typeof cityInfo == 'undefined'){
                    return true;
                }
                if(cityInfo.warn주민)$work.html($work.html().split('정착 장려').join('<span style="color:yellow;">정착 장려</span>'));
                if(cityInfo.warn농업)$work.html($work.html().split('농지 개간').join('<span style="color:yellow;">농지 개간</span>'));
                if(cityInfo.warn상업)$work.html($work.html().split('상업 투자').join('<span style="color:yellow;">상업 투자</span>'));
                if(cityInfo.warn치안)$work.html($work.html().split('치안 강화').join('<span style="color:yellow;">치안 강화</span>'));
                if(cityInfo.warn수비)$work.html($work.html().split('수비 강화').join('<span style="color:yellow;">수비 강화</span>'));
                if(cityInfo.warn성벽)$work.html($work.html().split('성벽 보수').join('<span style="color:yellow;">성벽 보수</span>'));
                
                
                
                var $stat = $this.children('.i_stat');
                var stat = $stat.text();
                
                var is수뇌 = stat.indexOf('+')>=0;
                
                $this.data('username',name);
                
                if(cityList[cityName].$태수.text() == name){
                    cityList[cityName].$태수.css('color','lightgreen');
                }
                if(cityList[cityName].$군사.text() == name){
                    cityList[cityName].$군사.css('color','lightgreen');
                }
                if(cityList[cityName].$종사.text() == name){
                    cityList[cityName].$종사.css('color','lightgreen');
                }
                
                userList[name]={
                    $city:cityInfo,
                    city:cityName,
                    $user:$this,
                    name:name,
                    val:'-1',
                    p태수:false,
                    p군사:false,
                    p종사:false,
                    is수뇌:is수뇌
                };
                
                if(cityList[cityName]){
                    cityList[cityName].users.append($this);
                }
                
            });
        });
        
        if($("#loadDutyBtn").length == 0){
            
            var $onBossList = $('<button id="loadDutyBtn">인사부 연동</button>');
            $onBossList.click(function(){
                loadDuty();
                return false;
            });
            
            $('form').append($onBossList);
        }
        
        $('#by_users').show();
    };
    
    var mainFunc = function(){
        //대상 추출
        window.cityList = {};
    	window.userList = {};
        
        $("form").each(function(){
            var $this = $(this);
            $this.attr('name','p'+$this.attr('name'));
        });
        
        $("table").each(function(idx,val){
            $this = $(this);
            
            
            if($this.attr('class')=='tb_layout bg2'){
                $this.addClass('cityInfo');
            }
            else{
                return true;
            }
            
            window.$tmpTable = $this;
            var cityInfo = {};
            
            //이름 추출
            {
                
                var titleText = $this.find('tr:eq(0)>td:eq(0)').text();
                var loc0 = titleText.indexOf("【");
                var loc1 = titleText.indexOf("|");
                var loc2 = titleText.indexOf("】");
                
                var cityLoc = $.trim(titleText.substring(loc0+1,loc1));
                var citySize = $.trim(titleText.substring(loc1+1,loc2));
                var cityName = $.trim(titleText.substring(loc2+1));
                
                cityName = cityName.replace("[","");
                cityName = cityName.replace("]","");
                
                $this.data('cityname',cityName);
                
                cityInfo.지역 = cityLoc;
                cityInfo.규모 = citySize;
                cityInfo.이름 = cityName;
                
                cityInfo.val = '-1';
                
                cityInfo.p태수 = false;
                cityInfo.p군사 = false;
                cityInfo.p종사 = false;
                
            }
            
            //주민, 농상치성수
            
            
            {
                var $baseTr = $this.find('tr:eq(1)');
                cityInfo.$주민 = $baseTr.find('td:eq(1)');
                cityInfo.$농업 = $baseTr.find('td:eq(3)');
                cityInfo.$상업 = $baseTr.find('td:eq(5)');
                cityInfo.$치안 = $baseTr.find('td:eq(7)');
                cityInfo.$수비 = $baseTr.find('td:eq(9)');
                cityInfo.$성벽 = $baseTr.find('td:eq(11)');
                
                var tmpVal;
                
                tmpVal = cityInfo.$주민.text().split('/');                
                cityInfo.주민 = parseInt(tmpVal[0]);
                cityInfo.max주민 = parseInt(tmpVal[1]);
                
                tmpVal = cityInfo.$농업.text().split('/');                
                cityInfo.농업 = parseInt(tmpVal[0]);
                cityInfo.max농업 = parseInt(tmpVal[1]);
                
                tmpVal = cityInfo.$상업.text().split('/');                
                cityInfo.상업 = parseInt(tmpVal[0]);
                cityInfo.max상업 = parseInt(tmpVal[1]);
                
                tmpVal = cityInfo.$치안.text().split('/');                
                cityInfo.치안 = parseInt(tmpVal[0]);
                cityInfo.max치안 = parseInt(tmpVal[1]);
                
                tmpVal = cityInfo.$수비.text().split('/');                
                cityInfo.수비 = parseInt(tmpVal[0]);
                cityInfo.max수비 = parseInt(tmpVal[1]);
                
                tmpVal = cityInfo.$성벽.text().split('/');                
                cityInfo.성벽 = parseInt(tmpVal[0]);
                cityInfo.max성벽 = parseInt(tmpVal[1]);
                
                if		(cityInfo.주민>cityInfo.max주민*0.9){ cityInfo.$주민.css('color','lightgreen');}
                else if	(cityInfo.주민>cityInfo.max주민*0.7){ cityInfo.$주민.css('color','yellow');}
                else 										{ cityInfo.$주민.css('color','orangered');}
                
                if		(cityInfo.농업>cityInfo.max농업*0.8){ cityInfo.$농업.css('color','lightgreen');}
                else if	(cityInfo.농업>cityInfo.max농업*0.4){ cityInfo.$농업.css('color','yellow');}
                else 										{ cityInfo.$농업.css('color','orangered');}
                
                if		(cityInfo.상업>cityInfo.max상업*0.8){ cityInfo.$상업.css('color','lightgreen');}
                else if	(cityInfo.상업>cityInfo.max상업*0.4){ cityInfo.$상업.css('color','yellow');}
                else 										{ cityInfo.$상업.css('color','orangered');}
                
                if		(cityInfo.치안>cityInfo.max치안*0.8){ cityInfo.$치안.css('color','lightgreen');}
                else if	(cityInfo.치안>cityInfo.max치안*0.4){ cityInfo.$치안.css('color','yellow');}
                else 										{ cityInfo.$치안.css('color','orangered');}
                
                if		(cityInfo.수비>cityInfo.max수비*0.6){ cityInfo.$수비.css('color','lightgreen');}
                else if	(cityInfo.수비>cityInfo.max수비*0.3){ cityInfo.$수비.css('color','yellow');}
                else 										{ cityInfo.$수비.css('color','orangered');}
                
                if		(cityInfo.성벽>cityInfo.max성벽*0.6){ cityInfo.$성벽.css('color','lightgreen');}
                else if	(cityInfo.성벽>cityInfo.max성벽*0.3){ cityInfo.$성벽.css('color','yellow');}
                else 										{ cityInfo.$성벽.css('color','orangered');}
                
                
                cityInfo.remain주민 = cityInfo.주민-cityInfo.max주민;
                cityInfo.remain농업 = cityInfo.농업-cityInfo.max농업;
                cityInfo.remain상업 = cityInfo.상업-cityInfo.max상업;
                cityInfo.remain치안 = cityInfo.치안-cityInfo.max치안;
                cityInfo.remain수비 = cityInfo.수비-cityInfo.max수비;
                cityInfo.remain성벽 = cityInfo.성벽-cityInfo.max성벽;
                
                cityInfo.warn주민 = false;
                cityInfo.warn농업 = false;
                cityInfo.warn상업 = false;
                cityInfo.warn치안 = false;
                cityInfo.warn수비 = false;
                cityInfo.warn성벽 = false;
                
                if(cityInfo.remain주민 > -10*2000)	cityInfo.warn주민 = true;
                if(cityInfo.주민 > 0.92*cityInfo.max주민)	cityInfo.warn주민 = true;
                if(cityInfo.remain농업 > -10*100)	cityInfo.warn농업 = true;
                if(cityInfo.remain상업 > -10*100)	cityInfo.warn상업 = true;
                if(cityInfo.remain치안 > -10*100)	cityInfo.warn치안 = true;
                if(cityInfo.remain수비 > -10*70)	cityInfo.warn수비 = true;
                if(cityInfo.remain성벽 > -10*70)	cityInfo.warn성벽 = true;
                
                if(cityInfo.warn농업) cityInfo.$농업.append('<span class="remain" style="color:yellow;">['+cityInfo.remain농업+']</span>');
                if(cityInfo.warn상업) cityInfo.$상업.append('<span class="remain" style="color:yellow;">['+cityInfo.remain상업+']</span>');
                if(cityInfo.warn치안) cityInfo.$치안.append('<span class="remain" style="color:yellow;">['+cityInfo.remain치안+']</span>');
                if(cityInfo.warn수비) cityInfo.$수비.append('<span class="remain" style="color:yellow;">['+cityInfo.remain수비+']</span>');
                if(cityInfo.warn성벽) cityInfo.$성벽.append('<span class="remain" style="color:yellow;">['+cityInfo.remain성벽+']</span>');
                
            }
            
            //태수,군사,종사
            {
                var $baseTr = $this.find('tr:eq(2)');
                cityInfo.$태수 = $baseTr.find('td:eq(7)');
                cityInfo.$군사 = $baseTr.find('td:eq(9)');
                cityInfo.$종사 = $baseTr.find('td:eq(11)');
            }
            
            //기타
            {
                
                cityInfo.userCnt = $this.find('tr:eq(3) td:eq(1)').text().split(',').length -1;
            }
            
            cityInfo.obj = $this;
            cityList[cityInfo.이름] = cityInfo;
        });
        
        
        var $onGenList = $('<button type="button">암행부 연동</button>');
        $onGenList.click(function(){
            loadUser();
            return false;
        });
        $('form').append($onGenList);
       	

        $('table:eq(0) tr:last').after('<tr><td id="sort_more"></td></tr>');
        
        
        $sort_more = $('#sort_more');
        $sort_more.html('재 정렬 순서 :');
        
        var sortIt = function(callback){
            var arCity = [];
            $('.cityInfo').each(function(){
                var $this = $(this);
                var cityName = $this.data('cityname');
                
                var cityInfo = cityList[cityName];
                arCity.push(cityInfo);
            });
            
            arCity = mergeSort(arCity,callback);
            //console.log(arCity);
            
            var $anchor = $('.anchor');
            //console.log($anchor);
            
            $('body > br').remove();
            
            $('.cityInfo').detach();
            
            $.each(arCity,function(idx,val){
                $anchor.before('<br>');
                $anchor.before(val.obj);
            });
            $anchor.before('<br>');
            
        };
        
        var $btn;
        
        $btn = $('<button type="button">도시명</button>').click(function(){
            sortIt(function(a,b){
                return a.이름.localeCompare(b.이름);
            });
        });
        $sort_more.append($btn);
        
        $btn = $('<button type="button">인구율</button>').click(function(){
            sortIt(function(a,b){
                return 1.0*a.주민/a.max주민 - 1.0*b.주민/b.max주민;
            });
        });
        $sort_more.append($btn);
        
        $btn = $('<button type="button">남은 주민</button>').click(function(){
            sortIt(function(a,b){
                return a.remain주민 - b.remain주민;
            });
        });
        $sort_more.append($btn);
        
        $btn = $('<button type="button">남은 농업</button>').click(function(){
            sortIt(function(a,b){
                return a.remain농업 - b.remain농업;
            });
        });
        $sort_more.append($btn);
        
        $btn = $('<button type="button">남은 상업</button>').click(function(){
            sortIt(function(a,b){
                return a.remain상업 - b.remain상업;
            });
        });
        $sort_more.append($btn);
        
        $btn = $('<button type="button">남은 치안</button>').click(function(){
            sortIt(function(a,b){
                return a.remain치안 - b.remain치안;
            });
        });
        $sort_more.append($btn);
        
        $btn = $('<button type="button">남은 수비</button>').click(function(){
            sortIt(function(a,b){
                return a.remain수비 - b.remain수비;
            });
        });
        $sort_more.append($btn);
        
        $btn = $('<button type="button">남은 성벽</button>').click(function(){
            sortIt(function(a,b){
                return a.remain성벽 - b.remain성벽;
            });
        });
        $sort_more.append($btn);
        
        $btn = $('<button type="button">배치 장수 수</button>').click(function(){
            sortIt(function(a,b){
                return b.userCnt - a.userCnt;
            });
        });
        $sort_more.append($btn);
    };
    
    mainFunc();
});