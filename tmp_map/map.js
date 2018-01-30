
String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) { 
        return (typeof args[number] != 'undefined') ? args[number] : match;
    });
};

function reloadWorldMap(isDetailMap, clickableAll, selectCallback){

    var cityPosition = getCityPosition();

    //OBJ : startYear, year, month, cityList, nationList, spyList, ourCityList, shownByGeneralList, myCity
    var $world_map = $('.world_map');

    function setMapBackground(obj){
        var startYear = obj.startYear;
        var year = obj.year;
        var month = obj.month;

        
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
        else if(month <= 6){
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
                "id":arr[0],
                "level":arr[1],
                "state":arr[2],
                "nationId":(arr[3]>0)?arr[3]:null,
                "region":arr[4],
                "supply":arr[5]
            };
        }

        function toNationObj(arr){
            return {
                "id":arr[0],
                "name":arr[1],
                "color":arr[2],
                "capital":(arr[3]!==0)
            };
        }

        function convertSpyList(arr){
            var result = [];
            arr.forEach(function(v){
                var cityId = (v / 10) | 0;
                var spy = v % 10;
                result[cityId] = spy;
            });
            return result;
        }

        

        var cityList = obj.cityList.map(toCityObj);
        var nationList = obj.nationList.map(toNationObj);
        nationList = convertDictById(nationList); //array of object -> dict

        var spyList = convertSpyList(obj.spyList);//Array -> Dict
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
            if(nationId == myNation){
                clickable |= 4;
            }
            if(shownByGeneralList.hasOwnProperty(id)){
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
            console.log(city);
            var id = city.id;
            $('.city_base_{0}'.format(id)).detach();
            var flagTemplate = '<div class="map_flag"><div class="map_flag_capital"></div></div>';
            var stateTemplate = '<div class="map_state"></div>';
            var backgroundTemplate = '<div class="map_background"></div>';

            


            
            //TODO: 도시를 그린다.........
            //
            //도시 선택 크기는 동일하게 가고 도시 이미지는 div로 설정.
            var $cityObj = $('<div class="city_base city_base_{0}"></div>'.format(id));
            $cityObj.addClass('city_level_{0}'.format(city.level));

            $cityObj.css({'left':city.x-20,'top':city.y-15});

            var $linkObj = $('<a class="city_link" href="#"></a>');
            $linkObj.data({'text':city.text,'nation':city.nation});
            var $imgObj = $('<div class="city_img"><img src="/images/cast_{0}.gif"></div>'.format(city.level));
            $cityObj.data('obj', city);
            //$cityObj.append($bgObj);
            //$cityObj.append($linkObj);

            if('color' in city && city.color !== null){
                var $bgObj = $('<div class="city_bg"></div>');
                $cityObj.append($bgObj);
                $bgObj.css({'background-image':'url(/images/b{0}.png)'.format(convColorValue(city.color))});
            }

            if(city.state > 0){
                var $imgObj = $('<img class="city_state" src="/images/state_{0}.gif">'.format(city.state));
            }

            $cityObj.append($linkObj);

            $linkObj.append($imgObj);
            $map_body.append($cityObj);

            if(selectCallback){
                $linkObj.click(function(){
                    selectCallback($cityObj);
                    return false;
                });
            }
            
            
        });
        
        return obj;
    }

    function drawBasicWorldMap(obj){
        

        
        return obj;
    }

    function setMouseWork(obj){
        var $tooltip = $('.world_map .city_tooltip');
        var $tooltip_city = $tooltip.find('.city_name');
        var $tooltip_nation = $tooltip.find('.nation_name');

        var $objs = $('.world_map .city_link');

        var $map_body = $('.world_map .map_body');


        $map_body.mousemove(function(e){
            var parentOffset = $map_body.offset(); 
            var relX = e.pageX - parentOffset.left;
            var relY = e.pageY - parentOffset.top;
            
            $tooltip.css({'top': relY + 10, 'left': relX + 10});
        });
        $objs.hover(function(event){
            var $city = $(this);

            $tooltip_city.html($city.data('text'));
            $tooltip_nation.html($city.data('nation'));
            $tooltip.show();
        },function(event){
            $tooltip.hide();
        });

        return obj;
    }

    function setCityClickable(obj){
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
    $.getJSON( 'result.json', {})
        .then(setMapBackground)
        .then(convertCityObjs)
        .then(isDetailMap?drawDetailWorldMap:drawBasicWorldMap)
        .then(setMouseWork)
        .then(setCityClickable)
        .then(saveCityInfo);    
}

$(function(){

    var isDetailMap = true;
    var clickableAll = false;

    reloadWorldMap(isDetailMap, clickableAll, console.log);

});