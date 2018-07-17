


function reloadWorldMap(option){
    var $world_map = $('.world_map');

    if($world_map.length == 0){
        return;
    }
    
    var defaultOption = {
        isDetailMap:true, //복잡 지도, 단순 지도
        clickableAll:false, //어떤 경우든 클릭을 가능하게 함. 해당 동작의 동작 가능성 여부와는 별도.
        selectCallback:null, //callback을 지정시 clickable과 관계 없이 해당 함수를 실행. 
        hrefTemplate:'#', //도시가 클릭가능할 경우 지정할 href값. {0}은 도시 id로 변환됨

        //아래부터는 post query에 들어갈 녀석
        year:null, //year값, 연감등에 사용
        month:null, //month값, 연감등에 사용
        aux:null, //기타 넣고 싶은 값을 입력
        neutralView:false, //clickable, 소속 국가, 첩보 여부 등을 반환여부를 설정
        showMe:true, //반환 값에 본인이 위치한 도시 값을 반환하도록 설정. neutralView와 별개
        targetJson:'j_map.php'
    };
    
    option = $.extend(defaultOption, option);

    var isDetailMap = option.isDetailMap;
    var clickableAll = option.clickableAll;
    var selectCallback = option.selectCallback;
    var hrefTemplate = option.hrefTemplate;

    var cityPosition = getCityPosition();

    //OBJ : startYear, year, month, cityList, nationList, spyList, shownByGeneralList, myCity

    function checkReturnObject(obj){
        if(!obj.result){
            var obj = $.Deferred();
            obj.reject('fail');
            return obj.promise();
        }

        if(!$.isNumeric(obj.startYear)
            ||!$.isNumeric(obj.year)
            ||!$.isNumeric(obj.month)
        ){
            var obj = $.Deferred();
            obj.reject('fail');
            return obj.promise();
        }

        return obj;
    }
    
    function setMapBackground(obj){
        var startYear = obj.startYear;
        var year = obj.year;
        var month = obj.month;

        if(isDetailMap){
            $world_map.addClass('map_detail').removeClass('map_basic');
        }
        else{
            $world_map.addClass('map_basic').removeClass('map_detail');
        }
        
        var $map_title = $('.map_title_text');
        if(year < startYear + 1){
            $map_title.css('color', 'magenta');
        }
        else if(year < startYear + 2){
            $map_title.css('color', 'orange');
        }
        else if(year < startYear + 3){
            $map_title.css('color', 'yellow');
        }


        $world_map.removeClass('map_string map_summer map_fall map_winter');
        if(month <= 3){
            $world_map.addClass('map_spring');
        }
        else if(month <= 6){
            $world_map.addClass('map_summer');
        }
        else if(month <= 9){
            $world_map.addClass('map_fall');
        }
        else{
            $world_map.addClass('map_winter');
        }

        $map_title.html('{0}年 {1}月'.format(year, month));

        return obj;
    }

    function convertCityObjs(obj){
        //원본 Obj는 굉장히 간소하게 온다, Object 형태로 변환해서 사용한다.

        function toCityObj(arr){
            return {
                id:arr[0],
                level:arr[1],
                state:arr[2],
                nationId:(arr[3]>0)?arr[3]:null,
                region:arr[4],
                supply:(arr[5]!=0)
            };
        }

        function toNationObj(arr){
            return {
                id:arr[0],
                name:arr[1],
                color:arr[2],
                capital:arr[3]
            };
        }

        var cityList = obj.cityList.map(toCityObj);
        var nationList = obj.nationList.map(toNationObj);
        nationList = convertDictById(nationList); //array of object -> dict

        var spyList = obj.spyList;
        var shownByGeneralList = convertSet(obj.shownByGeneralList);//Array -> Set

        var myCity = obj.myCity;
        var myNation = obj.myNation;

        function formatCityInfo(city) {
            var region_str = '';
            var level_str = '';
            switch(city.region) {
                case 1: region_str = "【하북|"; break;
                case 2: region_str = "【중원|"; break;
                case 3: region_str = "【서북|"; break;
                case 4: region_str = "【서촉|"; break;
                case 5: region_str = "【남중|"; break;
                case 6: region_str = "【　초|"; break;
                case 7: region_str = "【오월|"; break;
                case 8: region_str = "【동이|"; break;
            }
        
            switch(city.level) {
                case 1: level_str = "수】"; break;
                case 2: level_str = "진】"; break;
                case 3: level_str = "관】"; break;
                case 4: level_str = "이】"; break;
                case 5: level_str = "소】"; break;
                case 6: level_str = "중】"; break;
                case 7: level_str = "대】"; break;
                case 8: level_str = "특】"; break;
            }

            city.text = region_str + level_str + city.name;
            city.region_str = region_str;
            city.level_str = level_str;

            return city;
        }

        function mergePositionInfo(city){
            var id = city.id;
            if(!(id in cityPosition)){
                return city;
            }
            var xy_val = cityPosition[id];
            city.name = xy_val[0];
            city.x = xy_val[1];
            city.y = xy_val[2];
            return city;
        }

        function mergeNationInfo(city){
            //nationId 값으로 isCapital, color, nation을 통합

            var nationId = city.nationId;
            if(nationId === null || !(nationId in nationList)){
                city.nationId = null;
                city.nation = null;
                city.color = null;
                city.isCapital = false;
                return city;
            }

            var nationObj = nationList[nationId];
            city.nation = nationObj.name;
            city.color = nationObj.color;
            city.isCapital = (nationObj.capital == city.id);

            return city;
        }

        function mergeClickable(city){
            //clickable = (remainSpy << 3) | (ourCity << 2) | (shownByGeneral << 1) | clickableAll
            var id = city.id;
            var nationId = city.nationId;
            var clickable = 0;
            if(id in spyList){
                clickable |= spyList[id] << 3;
            }
            if(myNation !== null && nationId == myNation){
                clickable |= 4;
            }
            if(shownByGeneralList.hasOwnProperty(id)){
                clickable |= 2;
            }
            if(myCity !== null && id == myCity){
                clickable |= 2;
            }
            if(clickableAll){
                clickable |= 1;
            }


            city.clickable = clickable;
            return city;
        }

        cityList = cityList
            .map(mergePositionInfo)
            .map(mergeNationInfo)
            .map(mergeClickable)
            .map(formatCityInfo);

        return {
            'cityList' : cityList,
            'myCity' : myCity
        };
    }

    function drawDetailWorldMap(obj){
        
        var $map_body = $('.world_map .map_body');

        var cityList = obj.cityList;
        var myCity = obj.myCity;

        cityList.forEach(function(city){
            var id = city.id;
            $('.city_base_{0}'.format(id)).detach();
            //이전 도시는 지운다.

            var $cityObj = $('<div class="city_base city_base_{0}"></div>'.format(id));
            $cityObj.addClass('city_level_{0}'.format(city.level));
            $cityObj.data('obj', city).css({'left':city.x-20,'top':city.y-15});

            if('color' in city && city.color !== null){
                var $bgObj = $('<div class="city_bg"></div>');
                $cityObj.append($bgObj);
                $bgObj.css({'background-image':'url({0}/b{1}.png)'.format(pathConfig.gameImage, convColorValue(city.color))});
            }

            var $linkObj = $('<a class="city_link"></a>');
            $linkObj.data({'text':city.text,'nation':city.nation,'id':city.id});
            $cityObj.append($linkObj);

            var $imgObj = $('<div class="city_img"><img src="{0}/cast_{1}.gif"><div class="city_filler"></div></div>'.format(pathConfig.gameImage, city.level));
            $linkObj.append($imgObj);
            
            

            if(city.state > 0){
                var $stateObj = $('<div class="city_state"><img src="{0}/event{1}.gif"></div>'.format(pathConfig.gameImage, city.state));
                $linkObj.append($stateObj);
            }

            if(city.nationId && city.nationId > 0){
                var flagType = city.supply?'f':'d';
                var $flagObj = $('<div class="city_flag"><img src="{0}/{1}{2}.gif"></div>'.format(pathConfig.gameImage, flagType, convColorValue(city.color)));
                if(city.isCapital){
                    var $capitalObj = $('<div class="city_capital"><img src="{0}/event51.gif"></div>'.format(pathConfig.gameImage));
                    $flagObj.append($capitalObj);
                }
                $imgObj.append($flagObj);
            }

            var $nameObj = $('<span class="city_detail_name">{0}</span>'.format(city.name));
            $imgObj.append($nameObj);
         

            $map_body.append($cityObj);
            
            
        });

        $world_map.find('.city_base_{0} .city_filler'.format(myCity)).addClass('my_city');
        
        return obj;
    }

    function drawBasicWorldMap(obj){

        var $map_body = $('.world_map .map_body');

        var cityList = obj.cityList;
        var myCity = obj.myCity;

        cityList.forEach(function(city){
            var id = city.id;
            $('.city_base_{0}'.format(id)).detach();
            //이전 도시는 지운다.

            var $cityObj = $('<div class="city_base city_base_{0}"></div>'.format(id));
            $cityObj.addClass('city_level_{0}'.format(city.level));
            $cityObj.data('obj', city).css({'left':city.x-20,'top':city.y-15});

            var $linkObj = $('<a class="city_link"></a>');
            $linkObj.data({'text':city.text,'nation':city.nation,'id':city.id});
            $cityObj.append($linkObj);

            var $imgObj = $('<div class="city_img"><div class="city_filler"></div></div>');
            if('color' in city && city.color !== null){
                $imgObj.css({'background-color':city.color});
            }
            $linkObj.append($imgObj);

            if(city.state > 0){
                var state_text = 'wrong';
                if(city.state < 10){
                    state_text = 'good';
                }
                else if(city.state < 40){
                    state_text = 'bad';
                }
                else if(city.state < 50){
                    state_text = 'war';
                }
                
                var $stateObj = $('<div class="city_state city_state_{0}"></div>'.format(state_text));
                $imgObj.append($stateObj);
            }

            //단순 표기에서는 깃발 여부가 없음
            if(city.isCapital){
                var $capitalObj = $('<div class="city_capital"></div>');
                $imgObj.append($capitalObj);
            }

            var $nameObj = $('<span class="city_detail_name">{0}</span>'.format(city.name));
            $imgObj.append($nameObj);

            $map_body.append($cityObj);
        });

        $world_map.find('.city_base_{0} .city_filler'.format(myCity)).addClass('my_city');

        return obj;
    }

    function setMouseWork(obj){
        var $tooltip = $('.world_map .city_tooltip');
        var $tooltip_city = $tooltip.find('.city_name');
        var $tooltip_nation = $tooltip.find('.nation_name');

        var $objs = $('.world_map .city_link');

        var $map_body = $('.world_map .map_body');

        //Mouse over 모드 작동
        $map_body.on('mousemove', function(e){
            var parentOffset = $map_body.offset(); 
            var relX = e.pageX - parentOffset.left;
            var relY = e.pageY - parentOffset.top;
            
            $tooltip.css({'top': relY + 10, 'left': relX + 10});
        });

        $objs.on('mouseenter', function(e){
            var $this = $(this);

            $tooltip_city.data('target', $this.data('id'));
            $tooltip_city.html($this.data('text'));
            var nation_text = $this.data('nation');
            if(nation_text){
                $tooltip_nation.html(nation_text).show();
            }
            else{
                $tooltip_nation.html('').hide();
            }

            $tooltip.show();
        });

        $objs.on('mouseleave', function(event){
            $tooltip.hide();
        });

        $objs.on('click', function(e){
            return;
        });


        return obj;
    }

    function setCityClickable(obj){

        obj.cityList.forEach(function(city){
            var $cityLink = $world_map.find('.city_base_{0} .city_link'.format(city.id));

            if('clickable' in city && city.clickable > 0){
                $cityLink.attr('href',hrefTemplate.format(city.id));
            }

            if(selectCallback){
                $cityLink.click(function(){
                    return selectCallback(city);
                });
            }
        });
        
        return obj;
    }

    function saveCityInfo(obj){
        $world_map.data('cityInfo', obj);
        return obj;
    }

    if(isDetailMap){
        $world_map.addClass('map_detail');
    }
    else{
        $world_map.removeClass('map_datail');
    }

    //deferred mode of jQuery. != promise-then.
    var deferred = $.ajax({
        url: option.targetJson,
        type: 'post',
        dataType:'json',
        data: {
            data: JSON.stringify({
                neutralView:option.neutralView,
                year:option.year,
                month:option.month,
                showMe:option.showMe,
                aux:option.aux
            })
        }
    });

    deferred
        .then(checkReturnObject)
        .then(setMapBackground)
        .then(convertCityObjs)
        .then(isDetailMap?drawDetailWorldMap:drawBasicWorldMap)
        .then(setMouseWork)
        .then(setCityClickable)
        .then(saveCityInfo);    
}

