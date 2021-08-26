import axios from 'axios';
import $ from 'jquery';
import { extend, isNumber } from 'lodash';
import { convColorValue, convertDictById, convertSet, stringFormat } from './common_legacy';
import { InvalidResponse } from './defs';
import { unwrap } from "./util/unwrap";
import { convertFormData } from './util/convertFormData';

declare const serverNick: string;
declare const serverID: string;

type CityPositionMap = {
    [cityID: number]: [string, number, number];
}

declare global {
    interface Window {
        sam_toggleSingleTap?: boolean,
        reloadWorldMap: (option: loadMapOption, drawTarget?: string) => Promise<void>;
        getCityPosition: () => CityPositionMap;
        formatCityInfo: (city: MapCityParsedRaw)=>MapCityParsedRegionLevelText
    }
}

type MapCityCompact = [number, number, number, number, number, number];
type MapNationCompact = [number, string, string, number];

type MapCityParsedRaw = {
    id: number,
    level: number,
    state: number,
    nationID?: number,
    region: number,
    supply: boolean
};

type MapCityParsedName = MapCityParsedRaw & {
    name: string,
    x: number,
    y: number,
};

type MapCityParsedNation = MapCityParsedName & {
    nationID?: number;
    nation?: string;
    color?: string;
    isCapital: boolean
}

type MapCityParsedClickable = MapCityParsedNation & {
    clickable: number
}

type MapCityParsedRegionLevelText = MapCityParsedClickable & {
    region_str: string,
    level_str: string,
    text: string,
}

type MapCityParsed = MapCityParsedRegionLevelText;

type MapCityDrawable = {
    cityList: MapCityParsed[],
    myCity?: number
};

type MapNationParsed = {
    id: number,
    name: string,
    color: string,
    capital: number
}

type MapResult = {
    result: true,
    startYear: number,
    year: number,
    month: number,
    cityList: MapCityCompact[],
    nationList: MapNationCompact[],
    spyList: Record<number, number>,
    shownByGeneralList: number[],
    myCity?: number,
    myNation?: number,

    theme?: string,
    history?: string[],
}
type MapRawResult = InvalidResponse | MapResult;

function is_touch_device(): boolean {
    const prefixes = ' -webkit- -moz- -o- -ms- '.split(' ');
    const mq = (query: string) => {
        return window.matchMedia(query).matches;
    }

    type TouchWindow = { DocumentTouch?: () => void | Document };
    const tWindow = window as unknown as TouchWindow;
    if (('ontouchstart' in window) || (tWindow.DocumentTouch && (document instanceof tWindow.DocumentTouch))) {
        return true;
    }


    // include the 'heartz' as a way to have a non matching MQ to help terminate the join
    // https://git.io/vznFH
    const query = ['(', prefixes.join('touch-enabled),('), 'heartz', ')'].join('');
    return mq(query);
}

type loadMapOption = {
    isDetailMap?: boolean, //복잡 지도, 단순 지도
    clickableAll?: boolean, //어떤 경우든 클릭을 가능하게 함. 해당 동작의 동작 가능성 여부와는 별도.
    selectCallback?: (city: MapCityParsed) => void, //callback을 지정시 clickable과 관계 없이 해당 함수를 실행.
    hrefTemplate?: string, //도시가 클릭가능할 경우 지정할 href값. {0}은 도시 id로 변환됨
    useCachedMap?: boolean, //맵 캐시를 사용

    //아래부터는 post query에 들어갈 녀석
    year?: number, //year값, 연감등에 사용
    month?: number, //month값, 연감등에 사용
    aux?: Record<number | string, unknown>, //기타 넣고 싶은 값을 입력
    neutralView?: boolean, //clickable, 소속 국가, 첩보 여부 등을 반환여부를 설정
    showMe?: boolean, //반환 값에 본인이 위치한 도시 값을 반환하도록 설정. neutralView와 별개

    targetJson?: string,
    reqType?: 'get' | 'post',
    dynamicMapTheme?: boolean,
    callback?: (a: MapCityDrawable, rawObejct: MapRawResult) => void,

    //기타 보조 값
    startYear?: number,
}

export async function reloadWorldMap(option: loadMapOption, drawTarget = '.world_map'): Promise<void> {
    const $world_map = $(drawTarget);

    if ($world_map.length == 0) {
        return;
    }

    const defaultOption: loadMapOption = {
        isDetailMap: true, //복잡 지도, 단순 지도
        clickableAll: false, //어떤 경우든 클릭을 가능하게 함. 해당 동작의 동작 가능성 여부와는 별도.
        selectCallback: undefined, //callback을 지정시 clickable과 관계 없이 해당 함수를 실행.
        hrefTemplate: '#', //도시가 클릭가능할 경우 지정할 href값. {0}은 도시 id로 변환됨
        useCachedMap: false, //맵 캐시를 사용

        //아래부터는 post query에 들어갈 녀석
        year: undefined, //year값, 연감등에 사용
        month: undefined, //month값, 연감등에 사용
        aux: undefined, //기타 넣고 싶은 값을 입력
        neutralView: false, //clickable, 소속 국가, 첩보 여부 등을 반환여부를 설정
        showMe: true, //반환 값에 본인이 위치한 도시 값을 반환하도록 설정. neutralView와 별개

        targetJson: 'j_map.php',
        reqType: 'post',
        dynamicMapTheme: false,
        callback: undefined,

        //기타 보조 값
        startYear: undefined,
    };

    option = extend({}, defaultOption, option);

    const useCachedMap = option.useCachedMap;
    const isDetailMap = option.isDetailMap;
    const clickableAll = option.clickableAll;
    const selectCallback = option.selectCallback;
    const hrefTemplate = unwrap(option.hrefTemplate);

    const cityPosition = window.getCityPosition();

    const storedOldMapKey = `sam.${serverNick}.map`;
    const storedStartYear = `am.${serverNick}.startYear`;
    //OBJ : startYear, year, month, cityList, nationList, spyList, shownByGeneralList, myCity

    async function checkReturnObject(obj: MapRawResult): Promise<MapResult> {
        if (!obj.result) {
            throw `fail: ${obj.reason}`;
        }

        if (!isNumber(obj.startYear) ||
            !isNumber(obj.year) ||
            !isNumber(obj.month)
        ) {
            throw 'fail: date type';
        }

        if (useCachedMap) {
            localStorage.setItem(storedOldMapKey, JSON.stringify([serverID, obj]));
            localStorage.setItem(storedStartYear, JSON.stringify(obj.startYear));
        }

        $world_map.removeClass('draw_required');

        return obj;
    }



    async function setMapBackground(obj: MapResult): Promise<MapResult> {
        function setTheme() {
            const oldTheme = $world_map.data('currentTheme');
            const newTheme = obj.theme;
            if (oldTheme === newTheme) {
                return;
            }
            if (oldTheme) {
                $world_map.removeClass('map_theme_' + oldTheme);
            }
            $world_map.addClass('map_theme_' + newTheme);
            $world_map.data('currentTheme', newTheme ?? '_current');//FIXME: 뭔가 틀렸음. 전송시에 theme가 있어야하나?
        }
        if (option.dynamicMapTheme) {
            setTheme();
        }
        const startYear = obj.startYear;
        const year = obj.year;
        const month = obj.month;

        if (isDetailMap) {
            $world_map.addClass('map_detail').removeClass('map_basic');
        } else {
            $world_map.addClass('map_basic').removeClass('map_detail');
        }

        const $map_title = $('.map_title_text');
        if (year < startYear + 1) {
            $map_title.css('color', 'magenta');
        } else if (year < startYear + 2) {
            $map_title.css('color', 'orange');
        } else if (year < startYear + 3) {
            $map_title.css('color', 'yellow');
        }

        const $map_title_tooltip = $('.map_title .tooltiptext');
        $map_title_tooltip.empty();

        const tooltipTexts = [];
        if (year < startYear + 3) {
            const startYearText = [];
            let remainYear = startYear + 3 - year;
            const remainMonth = 12 - month + 1;
            if (remainMonth > 0) {
                remainYear -= 1;
            }
            if (remainYear) {
                startYearText.push(`${remainYear}년`);
            }
            if (remainMonth) {
                startYearText.push(`${remainMonth}개월`);
            }

            tooltipTexts.push(`초반제한 기간 : ${startYearText.join(' ')} (${startYear + 3}년)`);
        }

        const currentTechLimit = Math.floor(Math.max(0, year - startYear) / 5) + 1;
        const nextTechLimitYear = currentTechLimit * 5 + startYear;

        tooltipTexts.push(`기술등급 제한 : ${currentTechLimit}등급 (${nextTechLimitYear}년 해제)`);
        $map_title_tooltip.html(tooltipTexts.join('<br>'));

        $world_map.removeClass('map_string map_summer map_fall map_winter');
        if (month <= 3) {
            $world_map.addClass('map_spring');
        } else if (month <= 6) {
            $world_map.addClass('map_summer');
        } else if (month <= 9) {
            $world_map.addClass('map_fall');
        } else {
            $world_map.addClass('map_winter');
        }

        $map_title.html(`${year}年 ${month}月`);

        return obj;
    }

    async function convertCityObjs(obj: MapResult): Promise<MapCityDrawable> {
        //원본 Obj는 굉장히 간소하게 온다, Object 형태로 변환해서 사용한다.

        function toCityObj([id, level, state, nationID, region, supply]: MapCityCompact): MapCityParsedRaw {
            return {
                id: id,
                level: level,
                state: state,
                nationID: nationID > 0 ? nationID : undefined,
                region: region,
                supply: supply != 0
            };
        }

        function toNationObj([id, name, color, capital]: MapNationCompact): MapNationParsed {
            return {
                id,
                name,
                color,
                capital
            };
        }

        const nationList = convertDictById(obj.nationList.map(toNationObj)); //array of object -> dict

        const spyList = obj.spyList;
        const shownByGeneralList = new Set(obj.shownByGeneralList);

        const myCity = obj.myCity;
        const myNation = obj.myNation;


        function mergePositionInfo(city: MapCityParsedRaw): MapCityParsedName {
            const id = city.id;
            if (!(id in cityPosition)) {
                throw TypeError(`알수 없는 cityID: ${id}`);
            }
            const [name, x, y] = cityPosition[id];

            return {
                ...city,
                name,
                x,
                y,
            };
        }

        function mergeNationInfo(city: MapCityParsedName):MapCityParsedNation{
            //nationID 값으로 isCapital, color, nation을 통합

            const nationID = city.nationID;
            if (nationID === undefined || !(nationID in nationList)) {
                return {
                    ...city,
                    isCapital: false,
                };
            }

            const nationObj = nationList[nationID];
            return {
                ...city,
                nation: nationObj.name,
                color: nationObj.color,
                isCapital: nationObj.capital == city.id
            };
        }

        function mergeClickable(city:MapCityParsedNation): MapCityParsedClickable {
            //clickable = (defaultCity << 4 ) | (remainSpy << 3) | (ourCity << 2) | (shownByGeneral << 1) | clickableAll
            const id = city.id;
            const nationID = city.nationID;
            let clickable = 16;
            if (id in spyList) {
                clickable |= spyList[id] << 3;
            }
            if (myNation !== null && nationID == myNation) {
                clickable |= 4;
            }
            if (shownByGeneralList.has(id)) {
                clickable |= 2;
            }
            if (myCity !== null && id == myCity) {
                clickable |= 2;
            }
            if (clickableAll) {
                clickable |= 1;
            }


            return {
                ...city,
                clickable
            };
        }

        const cityList = obj.cityList.map(toCityObj)
            .map(mergePositionInfo)
            .map(mergeNationInfo)
            .map(mergeClickable)
            .map(window.formatCityInfo);

        return {
            'cityList': cityList,
            'myCity': myCity
        };
    }

    function drawDetailWorldMap(obj: MapCityDrawable) {

        const $map_body = $(drawTarget + ' .map_body');

        const cityList = obj.cityList;
        const myCity = obj.myCity;

        cityList.forEach(function (city) {
            const id = city.id;
            $(`.city_base_${id}`).detach();
            //이전 도시는 지운다.

            const $cityObj = $(`<div class="city_base city_base_${id}"></div>`);
            $cityObj.addClass(`city_level_${city.level}`);
            $cityObj.data('obj', city).css({ 'left': city.x - 20, 'top': city.y - 15 });

            if (city.color !== undefined) {
                const $bgObj = $('<div class="city_bg"></div>');
                $cityObj.append($bgObj);
                $bgObj.css({ 'background-image': `url(${window.pathConfig.gameImage}/b${convColorValue(city.color)}.png)` });
            }

            const $linkObj = $('<a class="city_link"></a>');
            $linkObj.data({ 'text': city.text, 'nation': city.nation, 'id': city.id });
            $cityObj.append($linkObj);

            const $imgObj = $(`<div class="city_img"><img src="${window.pathConfig.gameImage}/cast_${city.level}.gif"><div class="city_filler"></div></div>`);
            $linkObj.append($imgObj);

            if (city.state > 0) {
                const $stateObj = $(`<div class="city_state"><img src="${window.pathConfig.gameImage}/event${city.state}.gif"></div>`);
                $linkObj.append($stateObj);
            }

            if (city.nationID && city.nationID > 0) {
                const flagType = city.supply ? 'f' : 'd';
                const $flagObj = $(`<div class="city_flag"><img src="${window.pathConfig.gameImage}/${flagType}${convColorValue(unwrap(city.color))}.gif"></div>`);
                if (city.isCapital) {
                    const $capitalObj = $(`<div class="city_capital"><img src="${window.pathConfig.gameImage}/event51.gif"></div>`);
                    $flagObj.append($capitalObj);
                }
                $imgObj.append($flagObj);
            }

            const $nameObj = $(`<span class="city_detail_name">${city.name}</span>`);
            $imgObj.append($nameObj);


            $map_body.append($cityObj);


        });

        if(myCity){
            $world_map.find(`.city_base_${myCity} .city_filler`).addClass('my_city');
        }


        return obj;
    }

    function drawBasicWorldMap(obj:MapCityDrawable) {

        const $map_body = $(`${drawTarget} .map_body`);

        const cityList = obj.cityList;
        const myCity = obj.myCity;

        cityList.forEach(function (city) {
            const id = city.id;
            $(`.city_base_${id}`).detach();
            //이전 도시는 지운다.

            const $cityObj = $(`<div class="city_base city_base_${id}"></div>`);
            $cityObj.addClass(`city_level_${city.level}`);
            $cityObj.data('obj', city).css({ 'left': city.x - 20, 'top': city.y - 15 });

            const $linkObj = $('<a class="city_link"></a>');
            $linkObj.data({ 'text': city.text, 'nation': city.nation, 'id': city.id });
            $cityObj.append($linkObj);

            const $imgObj = $('<div class="city_img"><div class="city_filler"></div></div>');
            if (city.color !== undefined) {
                $imgObj.css({ 'background-color': city.color });
            }
            $linkObj.append($imgObj);

            if (city.state > 0) {
                let state_text = 'wrong';
                if (city.state < 10) {
                    state_text = 'good';
                } else if (city.state < 40) {
                    state_text = 'bad';
                } else if (city.state < 50) {
                    state_text = 'war';
                }

                const $stateObj = $(`<div class="city_state city_state_${state_text}"></div>`);
                $imgObj.append($stateObj);
            }

            //단순 표기에서는 깃발 여부가 없음
            if (city.isCapital) {
                const $capitalObj = $('<div class="city_capital"></div>');
                $imgObj.append($capitalObj);
            }

            const $nameObj = $(`<span class="city_detail_name">${city.name}</span>`);
            $imgObj.append($nameObj);

            $map_body.append($cityObj);
        });

        if(myCity){
            $world_map.find(`.city_base_${myCity} .city_filler`).addClass('my_city');
        }


        return obj;
    }

    function setMouseWork(obj:MapCityDrawable) {
        const $tooltip = $(drawTarget + ' .city_tooltip');
        const $tooltip_city = $tooltip.find('.city_name');
        const $tooltip_nation = $tooltip.find('.nation_name');

        const $objs = $(drawTarget + ' .city_link');

        const $map_body = $(drawTarget + ' .map_body');

        //터치스크린 탭

        if (!option.neutralView && is_touch_device()) {
            $objs.on('touchstart', function (e) {
                if (window.sam_toggleSingleTap) {
                    return true;
                }
                const $this = $(this);

                const touchMode = $this.data('touchMode') as number;
                if ($tooltip_city.data('target') != $this.data('id')) {
                    $this.data('touchMode', 1);
                } else if (touchMode === undefined) {
                    $this.data('touchMode', 1);
                } else {
                    $this.data('touchMode', touchMode + 1);
                }
                $map_body.data('touchMode', 1);

                $tooltip_city.data('target', $this.data('id'));


            });

            $objs.on('touchend', function () {
                if (window.sam_toggleSingleTap) {
                    return true;
                }
                const $this = $(this);
                const position = $this.parent().position();
                $tooltip_city.html($this.data('text') as string);

                const nation_text = $this.data('nation') as string;
                if (nation_text) {
                    $tooltip_nation.html(nation_text).show();
                } else {
                    $tooltip_nation.html('').hide();
                }

                let left = position.left;
                let top = position.top;

                const scale = $map_body.data('scale');
                if (scale) {
                    left /= scale;
                    top /= scale;
                }

                $tooltip.css({ 'top': top + 25, 'left': left + 35 }).show();

                const touchMode = $this.data('touchMode') as number;
                if (touchMode <= 1) {
                    return false;
                }

                //xxx: touchend 다음 click 이벤트가 갈 수도 있고, 안 갈 수도 있다.
                $this.data('touchMode', 0);
            });

            $map_body.on('touchend', function () {
                if (window.sam_toggleSingleTap) {
                    return true;
                }
                //위의 touchend bind에 해당하지 않는 경우 -> 빈 지도 터치
                $tooltip.hide();
            });

        }

        //Mouse over 모드 작동

        $map_body.on('mousemove', function (e) {
            if ($(this).data('touchMode')) {
                return true;
            }

            const rect = this.getBoundingClientRect();
            let left = (e.clientX - rect.left - this.clientLeft + this.scrollLeft);
            let top = (e.clientY - rect.top - this.clientTop + this.scrollTop);

            const scale = $map_body.data('scale');
            if (scale) {
                left /= scale;
                top /= scale;
            }

            $tooltip.css({ 'top': top + 10, 'left': left + 10 });
        });

        $objs.on('mouseenter', function () {
            if ($map_body.data('touchMode')) {
                return true;
            }

            const $this = $(this);

            $tooltip_city.data('target', $this.data('id'));
            $tooltip_city.html($this.data('text'));
            const nation_text = $this.data('nation');
            if (nation_text) {
                $tooltip_nation.html(nation_text).show();
            } else {
                $tooltip_nation.html('').hide();
            }

            $tooltip.show();
        });

        $objs.on('mouseleave', function () {
            $tooltip.hide();
        });

        $objs.on('click', function () {
            //xxx: touchend 다음 click 이벤트가 갈 수도 있고, 안 갈 수도 있다.
            const touchMode = $(this).data('touchMode') as number|undefined;
            if (touchMode === undefined) {
                return;
            }

            if (touchMode === 1) {
                return false;
            }
        });


        return obj;
    }

    function setCityClickable(obj:MapCityDrawable) {

        obj.cityList.forEach(function (city) {
            const $cityLink = $world_map.find(`.city_base_${city.id} .city_link`);

            if ('clickable' in city && city.clickable > 0) {
                $cityLink.attr('href', stringFormat(hrefTemplate, city.id));
            }

            if (selectCallback) {
                $cityLink.on('click', function () {
                    return selectCallback(city);
                });
            }
        });

        return obj;
    }

    function saveCityInfo(obj: MapCityDrawable) {
        $world_map.data('cityInfo', obj);
        return obj;
    }

    const $hideCityNameBtn = $world_map.find('.map_toggle_cityname');
    if (localStorage.getItem('sam.hideMapCityName') == 'yes') {
        $world_map.addClass('hide_cityname');
        $hideCityNameBtn.addClass('active').attr('aria-pressed', 'true');
    }

    $hideCityNameBtn.click(function () {
        //이전 상태 확인
        const state = !$hideCityNameBtn.hasClass('active');
        if (state) {
            $world_map.addClass('hide_cityname');
            localStorage.setItem('sam.hideMapCityName', 'yes');
        } else {
            $world_map.removeClass('hide_cityname');
            localStorage.setItem('sam.hideMapCityName', 'no');
        }
    });

    const $toggleSingleTapBtn = $world_map.find('.map_toggle_single_tap');
    if (localStorage.getItem('sam.toggleSingleTap') == 'yes') {
        window.sam_toggleSingleTap = true;
        $toggleSingleTapBtn.addClass('active').attr('aria-pressed', 'true');
    } else {
        window.sam_toggleSingleTap = false;
    }

    const $map_body = $(drawTarget + ' .map_body');

    $toggleSingleTapBtn.click(function () {
        //이전 상태 확인
        const state = !$toggleSingleTapBtn.hasClass('active');
        if (state) {
            $map_body.removeData('touchMode');
            localStorage.setItem('sam.toggleSingleTap', 'yes');
            window.sam_toggleSingleTap = true;
        } else {
            localStorage.setItem('sam.toggleSingleTap', 'no');
            window.sam_toggleSingleTap = false;
        }
    });

    if (isDetailMap) {
        $world_map.addClass('map_detail');
    } else {
        $world_map.removeClass('map_datail');
    }

    const responseP = axios({
        url: unwrap(option.targetJson),
        method: unwrap(option.reqType),
        responseType: 'json',
        data: convertFormData({
            data: JSON.stringify({
                neutralView: option.neutralView,
                year: option.year,
                month: option.month,
                showMe: option.showMe,
                aux: option.aux
            })
        })
    });

    const response = await responseP;

    const rawObject: MapRawResult = response.data;

    const computedResult = await checkReturnObject(rawObject)
        .then(setMapBackground)
        .then(convertCityObjs)
        .then(isDetailMap ? drawDetailWorldMap : drawBasicWorldMap)
        .then(setMouseWork)
        .then(setCityClickable)
        .then(saveCityInfo);

    if (option.callback) {
        option.callback(computedResult, rawObject);
    }

    if ($world_map.hasClass('draw_required')) {
        if (useCachedMap) {
            //일단 불러옴
            await (async ()=>{
                const rawStoredMap = localStorage.getItem(storedOldMapKey);
                if (!rawStoredMap) {
                    return;
                }
                const [storedServerID, storedMap] = JSON.parse(rawStoredMap) as [string, MapResult];
                if (storedServerID != serverID) {
                    return;
                }

                await setMapBackground(storedMap)
                .then(convertCityObjs)
                .then(isDetailMap?drawDetailWorldMap:drawBasicWorldMap)
                .then(setMouseWork)
                .then(setCityClickable)
                .then(saveCityInfo);
            })();
        } else if (option.year && option.month) {
            const rawStartYear = localStorage.getItem(storedStartYear) as string|undefined;
            let startYear: number;
            if (rawStartYear) {
                startYear = JSON.parse(rawStartYear);
            } else {
                startYear = option.year;
            }
            await setMapBackground({
                year: option.year,
                month: option.month,
                startYear: startYear
            } as unknown as MapResult);
        }


    }
}

window.reloadWorldMap = reloadWorldMap;
$(function ($) {
    if (is_touch_device()) {
        $('.map_body .map_toggle_single_tap').show();
    }
})