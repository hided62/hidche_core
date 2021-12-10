
import $ from 'jquery';
import { unwrap_any } from '@util/unwrap_any';

type UserItem = {
    val: string,
    name: string,
    city: string,


    태수: boolean,
    군사: boolean,
    종사: boolean,
    is수뇌: boolean,
    $city: CityItem,
    $user: JQuery<HTMLElement>,
}

type CityLevel = '특' | '대' | '중' | '소' | '이' | '진' | '관' | '수'

type OfficerItem = {
    preserved: boolean,
    $obj: JQuery<HTMLElement>
}

type CityItem = {
    지역: string,
    규모: CityLevel,
    이름: string,

    val: string,
    users: JQuery<HTMLElement>,
    userCnt: number,
    obj: JQuery<HTMLElement>,

    태수: OfficerItem,
    군사: OfficerItem,
    종사: OfficerItem,

    warn주민: boolean,
    warn농업: boolean,
    warn상업: boolean,
    warn치안: boolean,
    warn수비: boolean,
    warn성벽: boolean,

    $주민: JQuery<HTMLElement>,
    $농업: JQuery<HTMLElement>,
    $상업: JQuery<HTMLElement>,
    $치안: JQuery<HTMLElement>,
    $수비: JQuery<HTMLElement>,
    $성벽: JQuery<HTMLElement>,

    주민: number,
    농업: number,
    상업: number,
    치안: number,
    수비: number,
    성벽: number,

    max주민: number,
    max농업: number,
    max상업: number,
    max치안: number,
    max수비: number,
    max성벽: number,

    remain주민: number,
    remain농업: number,
    remain상업: number,
    remain치안: number,
    remain수비: number,
    remain성벽: number,
};

type OfficerSelector = '태수' | '군사' | '종사';


const cityList: Record<string, CityItem> = {};
const userList: Record<string, UserItem> = {};

$(function () {
    let basicPath = document.location.pathname;
    basicPath = basicPath.substring(0, basicPath.lastIndexOf('/')) + '/';

    const mergeSort = function <T>(arr: T[], cmpFunc: (lhs: T, rhs: T) => number) {
        const merge = function (left: T[], right: T[]): T[] {
            const retVal = [];

            let leftIdx = 0;
            let rightIdx = 0;



            while (leftIdx < left.length && rightIdx < right.length) {
                const cmpVal = cmpFunc(left[leftIdx], right[rightIdx]);

                if (cmpVal <= 0) {
                    retVal.push(left[leftIdx]);
                    leftIdx++;
                }
                else {
                    retVal.push(right[rightIdx]);
                    rightIdx++;
                }
            }

            return retVal.concat(left.slice(leftIdx)).concat(right.slice(rightIdx));
        };

        const _mergeSort = function (arr: T[]): T[] {
            if (arr.length < 2) {
                return arr;
            }

            const middle = Math.floor(arr.length / 2);

            const left = arr.slice(0, middle);
            const right = arr.slice(middle);

            return merge(_mergeSort(left), _mergeSort(right));
        };

        return _mergeSort(arr);

    };

    const loadDuty = function () {

        try {
            $('.for_duty').remove();
            void $.get(basicPath + 'b_myBossInfo.php', function (rawData) {
                const $html = $(rawData);

                const $tmpTable = $html.filter('#officer_list').eq(0);

                const $selects = $tmpTable.find("select");
                if ($selects.length == 0) {
                    alert("수뇌가 아닙니다!");
                    return false;
                }

                const setUserAvailable = function ($userList: JQuery<HTMLElement>, typeName: OfficerSelector) {
                    $userList.each(function () {
                        const $this = $(this);

                        const val = unwrap_any<string>($this.val());
                        let name = $.trim($this.text());

                        for (let i = name.length - 1; i > 0; i--) {
                            if (name[i] == '【') {
                                name = $.trim(name.substr(0, i));
                                break;
                            }
                        }

                        if (val == '0') {
                            return;
                        }

                        if (userList[name] !== undefined) {
                            userList[name].val = val;
                            userList[name][typeName] = true;
                        }

                    });
                };

                const setCityAvailiable = function ($cityList: JQuery<HTMLElement>, typeName: OfficerSelector) {
                    $cityList.each(function () {

                        const $this = $(this);

                        const val = $.trim(unwrap_any<string>($this.val()));
                        const name = $.trim($this.text());

                        cityList[name].val = val;
                        cityList[name][typeName].preserved = true;
                    });
                }

                for (const cityInfo of Object.values(cityList)) {
                    cityInfo.태수.preserved = false;
                    cityInfo.군사.preserved = false;
                    cityInfo.종사.preserved = false;
                }

                $.each(userList, function (idx, userInfo) {
                    userInfo.태수 = false;
                    userInfo.군사 = false;
                    userInfo.종사 = false;
                });

                setUserAvailable($selects.eq(1).find("option"), "태수");
                setUserAvailable($selects.eq(3).find("option"), "군사");
                setUserAvailable($selects.eq(5).find("option"), "종사");

                setCityAvailiable($selects.eq(0).find("option"), "태수");
                setCityAvailiable($selects.eq(2).find("option"), "군사");
                setCityAvailiable($selects.eq(4).find("option"), "종사");


                $.each(cityList, function (idx, cityInfo) {

                    //console.log(cityInfo.users.children());

                    cityInfo.users.children().each(function () {
                        //console.log(this);
                        const $this = $(this);

                        const username = $this.data('username');

                        const userInfo = userList[username];
                        if (!userInfo) {
                            return;
                        }

                        if (userInfo.val == '-1') {
                            return;
                        }

                        const $name = $this.find('.nameplate');

                        $name.append('<br class="for_duty">');


                        const addBtn = function ($name: JQuery<HTMLElement>, cityInfo: CityItem, userInfo: UserItem, level: number, typeName: OfficerSelector) {

                            const enabled = cityInfo[typeName] && userInfo[typeName];
                            const cityVal = cityInfo.val;
                            const $btn = $('<button type="button">' + typeName.substr(0, 1) + '</button>');
                            $btn.addClass(`mode_${level}`);
                            $btn.addClass('for_duty');

                            if (!enabled) {
                                $btn.prop('disabled', true);
                                $btn.css('background', 'transparent');
                                $btn.css('border', '0');
                            }
                            else {
                                if (userInfo.is수뇌) {
                                    $btn.css('color', 'red');
                                }
                            }

                            $btn.css('padding', '1px 4px');
                            $btn.css('margin', '0');

                            $btn.click(function () {
                                if (userInfo.is수뇌) {
                                    if (!confirm('수뇌입니다. 임명할까요?')) {
                                        return false;
                                    }
                                }

                                void $.post(basicPath + 'j_myBossInfo.php', {
                                    destCityID: cityVal,
                                    destGeneralID: userInfo.val,
                                    officerLevel: level,
                                    action: '임명'
                                }, function () {
                                    cityInfo[typeName].preserved = false;
                                    const $target = cityInfo.users.find(`.mode_${level}`);
                                    $target.prop('disabled', true);
                                    $target.css('background', 'transparent');
                                    $target.css('border', '0');
                                    $target.css('color', '');

                                    cityInfo[typeName].$obj.html(userInfo.name);
                                });
                            });

                            //console.log($btn);
                            $name.append($btn);
                        };

                        addBtn($name, cityInfo, userInfo, 4, '태수');
                        addBtn($name, cityInfo, userInfo, 3, '군사');
                        addBtn($name, cityInfo, userInfo, 2, '종사');
                    });
                });

            });


        }
        catch (a) {
            console.log(a);
        }

        return false;
    };

    const loadUser = function () {
        $.each(cityList, function (idx, val) {
            if (typeof val.users == "undefined") {
                val.obj.append('<tr><td colspan="12"><table align="center" class="tb_layout cityUser bg0">' +
                    '<thead><tr>' +
                    '<td width="100" align="center" class="bg1">이 름</td><td width="100" align="center" class="bg1">통무지</td><td width="100" align="center" class="bg1">부 대</td><td width="60" align="center" class="bg1">자 금</td>' +
                    '<td width="60" align="center" class="bg1">군 량</td><td width="30" align="center" class="bg1">守</td><td width="60" align="center" class="bg1">병 종</td>' +
                    '<td width="60" align="center" class="bg1">병 사</td><td width="50" align="center" class="bg1">훈련</td><td width="50" align="center" class="bg1">사기</td><td width="150" align="center" class="bg1">명 령</td>' +
                    '<td width="60" align="center" class="bg1">삭턴</td><td width="60" align="center" class="bg1">턴</td>' +
                    '</tr></thead>' +
                    '<tbody class="cityUserBody"></tbody></table></td></tr>');

                val.users = val.obj.find(".cityUserBody");
            }
            else {
                val.users.html("");
            }
        });


        void $.get(basicPath + 'b_genList.php', function (rawData) {
            const $helper = $('#helper_genlist');
            $helper.html('').append($.parseHTML(rawData));

            const tmpUsers = $('#general_list tbody tr');

            tmpUsers.each(function () {
                const $this = $(this);

                const $city = $this.children('.i_city');
                $city.remove();
                const cityName = $.trim($city.text());

                const $name = $this.children('.i_name');
                $name.addClass('nameplate');

                const name = $name.find('.t_name').text();

                const $work = $this.children('.i_action');

                const cityInfo = cityList[cityName];
                if (typeof cityInfo == 'undefined') {
                    return;
                }
                if (cityInfo.warn주민) $work.html($work.html().split('정착 장려').join('<span style="color:yellow;">정착 장려</span>'));
                if (cityInfo.warn농업) $work.html($work.html().split('농지 개간').join('<span style="color:yellow;">농지 개간</span>'));
                if (cityInfo.warn상업) $work.html($work.html().split('상업 투자').join('<span style="color:yellow;">상업 투자</span>'));
                if (cityInfo.warn치안) $work.html($work.html().split('치안 강화').join('<span style="color:yellow;">치안 강화</span>'));
                if (cityInfo.warn수비) $work.html($work.html().split('수비 강화').join('<span style="color:yellow;">수비 강화</span>'));
                if (cityInfo.warn성벽) $work.html($work.html().split('성벽 보수').join('<span style="color:yellow;">성벽 보수</span>'));



                const $stat = $this.children('.i_stat');
                const stat = $stat.text();

                const is수뇌 = stat.indexOf('+') >= 0;

                $this.data('username', name);

                if (cityList[cityName].태수.$obj.text() == name) {
                    cityList[cityName].태수.$obj.html(`<span style="color:lightgreen">${name}</span>`);
                }
                if (cityList[cityName].군사.$obj.text() == name) {
                    cityList[cityName].군사.$obj.html(`<span style="color:lightgreen">${name}</span>`);
                }
                if (cityList[cityName].종사.$obj.text() == name) {
                    cityList[cityName].종사.$obj.html(`<span style="color:lightgreen">${name}</span>`);
                }

                userList[name] = {
                    $city: cityInfo,
                    city: cityName,
                    $user: $this,
                    name: name,
                    val: '-1',
                    태수: false,
                    군사: false,
                    종사: false,
                    is수뇌: is수뇌
                };

                if (cityList[cityName]) {
                    cityList[cityName].users.append($this);
                }

            });
        });

        if ($("#loadDutyBtn").length == 0) {

            const $onBossList = $('<button id="loadDutyBtn">인사부 연동</button>');
            $onBossList.click(function () {
                loadDuty();
                return false;
            });

            $('form').append($onBossList);
        }

        $('#by_users').show();
    };

    const mainFunc = function () {
        //대상 추출

        $("form").each(function () {
            const $this = $(this);
            $this.attr('name', 'p' + $this.attr('name'));
        });

        $("table").each(function () {
            const $this = $(this);


            if ($this.attr('class') == 'tb_layout bg2') {
                $this.addClass('cityInfo');
            }
            else {
                return;
            }

            const cityInfo: CityItem = {
                태수:{},
                군사:{},
                종사:{},
            } as CityItem;

            //이름 추출
            {
                const titleText = $this.find('tr:eq(0)>td:eq(0)').text();
                const loc0 = titleText.indexOf("【");
                const loc1 = titleText.indexOf("|");
                const loc2 = titleText.indexOf("】");

                const cityLoc = $.trim(titleText.substring(loc0 + 1, loc1));
                const citySize = $.trim(titleText.substring(loc1 + 1, loc2));
                let cityName = $.trim(titleText.substring(loc2 + 1));

                cityName = cityName.replace("[", "");
                cityName = cityName.replace("]", "");

                $this.data('cityname', cityName);

                cityInfo.지역 = cityLoc;
                cityInfo.규모 = citySize as CityLevel;
                cityInfo.이름 = cityName;

                cityInfo.val = '-1';

                cityInfo.태수.preserved = false;
                cityInfo.군사.preserved = false;
                cityInfo.종사.preserved = false;
            }

            //주민, 농상치성수


            {
                const $baseTr = $this.find('tr:eq(1)');
                cityInfo.$주민 = $baseTr.find('td:eq(1)');
                cityInfo.$농업 = $baseTr.find('td:eq(3)');
                cityInfo.$상업 = $baseTr.find('td:eq(5)');
                cityInfo.$치안 = $baseTr.find('td:eq(7)');
                cityInfo.$수비 = $baseTr.find('td:eq(9)');
                cityInfo.$성벽 = $baseTr.find('td:eq(11)');

                let tmpVal;

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

                if (cityInfo.주민 > cityInfo.max주민 * 0.9) { cityInfo.$주민.css('color', 'lightgreen'); }
                else if (cityInfo.주민 > cityInfo.max주민 * 0.7) { cityInfo.$주민.css('color', 'yellow'); }
                else { cityInfo.$주민.css('color', 'orangered'); }

                if (cityInfo.농업 > cityInfo.max농업 * 0.8) { cityInfo.$농업.css('color', 'lightgreen'); }
                else if (cityInfo.농업 > cityInfo.max농업 * 0.4) { cityInfo.$농업.css('color', 'yellow'); }
                else { cityInfo.$농업.css('color', 'orangered'); }

                if (cityInfo.상업 > cityInfo.max상업 * 0.8) { cityInfo.$상업.css('color', 'lightgreen'); }
                else if (cityInfo.상업 > cityInfo.max상업 * 0.4) { cityInfo.$상업.css('color', 'yellow'); }
                else { cityInfo.$상업.css('color', 'orangered'); }

                if (cityInfo.치안 > cityInfo.max치안 * 0.8) { cityInfo.$치안.css('color', 'lightgreen'); }
                else if (cityInfo.치안 > cityInfo.max치안 * 0.4) { cityInfo.$치안.css('color', 'yellow'); }
                else { cityInfo.$치안.css('color', 'orangered'); }

                if (cityInfo.수비 > cityInfo.max수비 * 0.6) { cityInfo.$수비.css('color', 'lightgreen'); }
                else if (cityInfo.수비 > cityInfo.max수비 * 0.3) { cityInfo.$수비.css('color', 'yellow'); }
                else { cityInfo.$수비.css('color', 'orangered'); }

                if (cityInfo.성벽 > cityInfo.max성벽 * 0.6) { cityInfo.$성벽.css('color', 'lightgreen'); }
                else if (cityInfo.성벽 > cityInfo.max성벽 * 0.3) { cityInfo.$성벽.css('color', 'yellow'); }
                else { cityInfo.$성벽.css('color', 'orangered'); }


                cityInfo.remain주민 = cityInfo.주민 - cityInfo.max주민;
                cityInfo.remain농업 = cityInfo.농업 - cityInfo.max농업;
                cityInfo.remain상업 = cityInfo.상업 - cityInfo.max상업;
                cityInfo.remain치안 = cityInfo.치안 - cityInfo.max치안;
                cityInfo.remain수비 = cityInfo.수비 - cityInfo.max수비;
                cityInfo.remain성벽 = cityInfo.성벽 - cityInfo.max성벽;

                cityInfo.warn주민 = false;
                cityInfo.warn농업 = false;
                cityInfo.warn상업 = false;
                cityInfo.warn치안 = false;
                cityInfo.warn수비 = false;
                cityInfo.warn성벽 = false;

                if (cityInfo.remain주민 > -10 * 2000) cityInfo.warn주민 = true;
                if (cityInfo.주민 > 0.92 * cityInfo.max주민) cityInfo.warn주민 = true;
                if (cityInfo.remain농업 > -10 * 100) cityInfo.warn농업 = true;
                if (cityInfo.remain상업 > -10 * 100) cityInfo.warn상업 = true;
                if (cityInfo.remain치안 > -10 * 100) cityInfo.warn치안 = true;
                if (cityInfo.remain수비 > -10 * 70) cityInfo.warn수비 = true;
                if (cityInfo.remain성벽 > -10 * 70) cityInfo.warn성벽 = true;

                if (cityInfo.warn농업) cityInfo.$농업.append('<span class="remain" style="color:yellow;">[' + cityInfo.remain농업 + ']</span>');
                if (cityInfo.warn상업) cityInfo.$상업.append('<span class="remain" style="color:yellow;">[' + cityInfo.remain상업 + ']</span>');
                if (cityInfo.warn치안) cityInfo.$치안.append('<span class="remain" style="color:yellow;">[' + cityInfo.remain치안 + ']</span>');
                if (cityInfo.warn수비) cityInfo.$수비.append('<span class="remain" style="color:yellow;">[' + cityInfo.remain수비 + ']</span>');
                if (cityInfo.warn성벽) cityInfo.$성벽.append('<span class="remain" style="color:yellow;">[' + cityInfo.remain성벽 + ']</span>');

            }

            //태수,군사,종사
            {
                const $baseTr = $this.find('tr:eq(2)');
                cityInfo.태수.$obj = $baseTr.find('td:eq(7)');
                cityInfo.군사.$obj = $baseTr.find('td:eq(9)');
                cityInfo.종사.$obj = $baseTr.find('td:eq(11)');
            }

            //기타
            {

                cityInfo.userCnt = $this.find('tr:eq(3) td:eq(1)').text().split(',').length - 1;
            }

            cityInfo.obj = $this;
            cityList[cityInfo.이름] = cityInfo as CityItem;
        });


        const $onGenList = $('<button type="button">암행부 연동</button>');
        $onGenList.click(function () {
            loadUser();
            return false;
        });
        $('form').append($onGenList);


        $('table:eq(0) tr:last').after('<tr><td id="sort_more"></td></tr>');


        const $sort_more = $('#sort_more');
        $sort_more.html('재 정렬 순서 :');

        const sortIt = function(callback: (lhs:CityItem, rhs:CityItem)=>number) {
            let arCity: CityItem[] = [];
            $('.cityInfo').each(function () {
                const $this = $(this);
                const cityName = $this.data('cityname');

                const cityInfo = cityList[cityName];
                arCity.push(cityInfo);
            });

            arCity = mergeSort(arCity, callback);
            //console.log(arCity);

            const $anchor = $('.anchor');
            //console.log($anchor);

            $('body > br').remove();

            $('.cityInfo').detach();

            $.each(arCity, function (idx, val) {
                $anchor.before('<br>');
                $anchor.before(val.obj);
            });
            $anchor.before('<br>');

        };

        let $btn: JQuery<HTMLElement>;

        $btn = $('<button type="button">도시명</button>').click(function () {
            sortIt(function (a, b) {
                return a.이름.localeCompare(b.이름);
            });
        });
        $sort_more.append($btn);

        $btn = $('<button type="button">인구율</button>').click(function () {
            sortIt(function (a, b) {
                return 1.0 * a.주민 / a.max주민 - 1.0 * b.주민 / b.max주민;
            });
        });
        $sort_more.append($btn);

        $btn = $('<button type="button">남은 주민</button>').click(function () {
            sortIt(function (a, b) {
                return a.remain주민 - b.remain주민;
            });
        });
        $sort_more.append($btn);

        $btn = $('<button type="button">남은 농업</button>').click(function () {
            sortIt(function (a, b) {
                return a.remain농업 - b.remain농업;
            });
        });
        $sort_more.append($btn);

        $btn = $('<button type="button">남은 상업</button>').click(function () {
            sortIt(function (a, b) {
                return a.remain상업 - b.remain상업;
            });
        });
        $sort_more.append($btn);

        $btn = $('<button type="button">남은 치안</button>').click(function () {
            sortIt(function (a, b) {
                return a.remain치안 - b.remain치안;
            });
        });
        $sort_more.append($btn);

        $btn = $('<button type="button">남은 수비</button>').click(function () {
            sortIt(function (a, b) {
                return a.remain수비 - b.remain수비;
            });
        });
        $sort_more.append($btn);

        $btn = $('<button type="button">남은 성벽</button>').click(function () {
            sortIt(function (a, b) {
                return a.remain성벽 - b.remain성벽;
            });
        });
        $sort_more.append($btn);

        $btn = $('<button type="button">배치 장수 수</button>').click(function () {
            sortIt(function (a, b) {
                return b.userCnt - a.userCnt;
            });
        });
        $sort_more.append($btn);
    };

    mainFunc();
});