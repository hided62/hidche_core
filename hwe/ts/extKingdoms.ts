import $ from 'jquery';
import { unwrap } from '@util/unwrap';
import axios from 'axios';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { getNPCColor } from '@/utilGame';

declare const killturn: number;
declare const autorun_user: undefined|null|{
    limit_minutes: number;
    options: Record<string, number>;
};
declare const turnterm: number;

type KingdomGeneral = {
    html: JQuery<HTMLElement>,
    장수명: string
    국가: string
    벌점: number,
    통: number,
    무: number,
    지: number,
    삭턴: number,
    종류: UserType,
    NPC: number,
}

type UserType = "통" | "무" | "지" | "만능" | "평범" | "무능" | "무지";

type NationInfo = {
    [v in UserType]: KingdomGeneral[]
}

$(function () {
    setAxiosXMLHttpRequest();

    const $userFrame: JQuery<HTMLElement> = $('<div id="on_mover" style="position:absolute;">' +
        '<table class="tb_layout bg0" style="width:100%;"><thead><tr>' +
        '<td width="64" align="center" class="bg1">얼 굴</td>' +
        '<td width="100" align="center" class="bg1">이 름</td>' +
        '<td width="50" align="center" class="bg1">연령</td>' +
        '<td width="50" align="center" class="bg1">성격</td>' +
        '<td width="90" align="center" class="bg1">특기</td>' +
        '<td width="50" align="center" class="bg1">레 벨</td>' +
        '<td width="100" align="center" class="bg1">국 가</td>' +
        '<td width="60" align="center" class="bg1">명 성</td>' +
        '<td width="60" align="center" class="bg1">계 급</td>' +
        '<td width="80" align="center" class="bg1">관 직</td>' +
        '<td width="45" align="center" class="bg1">통솔</td>' +
        '<td width="45" align="center" class="bg1">무력</td>' +
        '<td width="45" align="center" class="bg1">지력</td>' +
        '<td width="45" align="center" class="bg1">삭턴</td>' +
        '<td width="84" align="center" class="bg1">벌점</td>' +
        '</tr></thead><tbody class="content"></tbody></table></div>');
    $userFrame.find('thead td');
    $userFrame.css('width', '1000px').css('margin', '0').css('padding', '0').css('left', '50%').css('margin-left', '-500px');
    $userFrame.css('box-shadow', '0px 0px 7px 3px rgba(255,255,255,50)');
    $userFrame.hide();

    const 국가테이블 = $('table:gt(0):lt(-2)');

    const getUserType = function (통: number, 무: number, 지: number): UserType {
        //const 총 = 통 + 무 + 지;
        if (통 < 40) {
            if (무 + 지 < 40) {
                return "무능";
            }
            return "무지";
        }

        const 최대능력치 = Math.max(통, 무, 지);
        const 능력치2합 = Math.min(통 + 무, 무 + 지, 지 + 통);
        if (최대능력치 >= 70 && 능력치2합 >= 최대능력치 * 1.7) {
            return "만능";
        }

        if (무 >= 60 && 지 < 무 * 0.8) {
            return "무";
        }
        if (지 >= 60 && 무 < 지 * 0.8) {
            return "지";
        }
        if (통 >= 60 && 무 + 지 < 통) {
            return "통";
        }

        return "평범";
    };

    function formatScore(x: number) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    const runAnalysis = async function () {
        let realKillturn = killturn;
        if(autorun_user && autorun_user.limit_minutes){
            realKillturn -= autorun_user.limit_minutes / turnterm;
        }
        const $content = $('#on_mover .content');
        try {
            const response = await axios({ url: 'a_genList.php', method: 'get', responseType: 'text' });
            const rawData = response.data; const $html = $(rawData);

            let $장수일람: JQuery<HTMLElement> | undefined;

            const 국가별: Record<string, NationInfo> = {};
            let cnt = 0;
            $html.each(function () {
                if (this.tagName == "TABLE") {
                    cnt += 1;
                    if (cnt == 2) {
                        $장수일람 = $(this);
                        return false;
                    }
                }
            });
            if ($장수일람 !== undefined) {
                $장수일람.find('tr:gt(0)').each(function () {
                    const 장수 = {} as KingdomGeneral;
                    const $this = $(this);
                    const $tds = $this.find('td');

                    const 장수명 = $.trim($tds.eq(1).text());
                    const 국가 = $.trim($tds.eq(6).text());

                    //const 부상 = $this.data('general-wounded');

                    장수.html = $this.clone();
                    장수.장수명 = 장수명;
                    장수.국가 = 국가;
                    장수.벌점 = parseInt($tds.eq(-1).text());
                    장수.통 = parseInt($this.data('general-leadership'));
                    장수.무 = parseInt($this.data('general-strength'));
                    장수.지 = parseInt($this.data('general-intel'));
                    장수.삭턴 = parseInt($tds.eq(-2).text());
                    장수.종류 = getUserType(장수.통, 장수.무, 장수.지);
                    장수.NPC = $this.data('npc-type');

                    if (!(국가 in 국가별)) {
                        국가별[국가] = {
                            만능: [],
                            통: [],
                            무: [],
                            지: [],
                            평범: [],
                            무지: [],
                            무능: [],
                            //NPC:[],
                        };
                    }

                    //if(장수.NPC) 국가별[국가].NPC.push(장수);
                    국가별[국가][장수.종류].push(장수);

                    장수.html.hide();
                    $content.append(장수.html);


                });
            }


            국가테이블.each(function () {
                const $this = $(this);
                const $tbl = $this;
                const $td = $this.find('td:last');
                let name = $.trim($this.find('td:first').text());
                name = name.substr(2, name.length - 4);

                const 국가정보 = 국가별[name];

                let total = 0;
                let 전투유저장수 = 0;
                let 통솔합 = 0;
                let 삭턴장수 = 0;

                let 전투N장수 = 0;
                let N장통솔합 = 0;
                $td.html('<p class="sum" style="margin:0;font-weight:bold;color:yellow;text-align:center"></p>');
                $td.css('text-indent', '-5.8em').css('padding-left', '5.8em');
                for (const [종류명, 테이블] of Object.entries(국가정보)) {

                    const $p = $("<p></p>").css('margin', '0');

                    if (테이블.length == 0) continue;

                    테이블.sort(function (좌, 우) {
                        if (우.벌점 == 좌.벌점) {
                            return 좌.장수명 > 우.장수명 ? 1 : 0;
                        }
                        return 우.벌점 - 좌.벌점
                    });


                    let text = "　　" + 종류명;
                    text = text.substr(text.length - 2);
                    $p.append(text + '장(');

                    text = "" + 테이블.length;

                    $p.append(text + ')');
                    if (text.length < 3) {
                        $p.append("<span style='display:inline-block;width:" + (3 - text.length) / 2 + "em;'>&nbsp;</span>");
                    }
                    $p.append(': ');

                    total += 테이블.length;

                    $.each(테이블, function (idx, val) {
                        //const 종능 = val.통 + val.무 + val.지;
                        console.log(val);
                        if (종류명 != '무능' && 종류명 != '무지') {
                            if (val.삭턴 >= realKillturn && val.NPC < 2) {
                                전투유저장수 += 1;

                                통솔합 += val.통;
                            }

                            if (val.삭턴 > 5 && val.NPC >= 2) {
                                전투N장수 += 1;

                                N장통솔합 += val.통;
                            }
                        }

                        const $obj = $('<span></span>');
                        const $obj2 = $('<span></span>');
                        $obj.html(val.장수명);

                        if (val.NPC < 2 && val.삭턴 < realKillturn) {
                            $obj.css('text-decoration', 'line-through');
                            삭턴장수 += 1;
                        }
                        const colorNPC = getNPCColor(val.NPC);
                        if (colorNPC !== undefined) {
                            $obj.css('color', colorNPC);
                        }
                        if (val.벌점 >= 1500) $obj.css('color', 'yellow');
                        else if (val.벌점 >= 200) $obj.css('color', 'lightgreen');

                        $obj2.append($obj);
                        if (idx < 테이블.length - 1) {
                            $obj2.append(', ');
                        }
                        $p.append($obj2);
                        $obj2.hover(function () {
                            const top = unwrap($tbl.offset()).top + unwrap($tbl.outerHeight()) + 3;
                            $userFrame.css('top', top);
                            val.html.show();
                            $userFrame.show();
                            console.log('올림!' + val.장수명);
                        }, function () {
                            $userFrame.hide();
                            val.html.hide();
                            console.log('내림!' + val.장수명);
                        });



                    });
                    $td.append($p);
                }

                const result = "* 총(" + total + "), 전투장(" + 전투유저장수 + ", 약 " + formatScore(통솔합 * 100) + "명), 전투N장(" + 전투N장수 + ", 약 " + formatScore(N장통솔합 * 100) + "명), 삭턴장(" + 삭턴장수 + ") *";
                $tbl.find('.sum').html(result);


            });
        }
        catch (err) {
            console.error(err);
        }

    }



    $('body').append($userFrame);

    const $frame = $('table:eq(0) td:eq(0)');
    $frame.find('br:last').remove();

    const $btn = $('<input type="button" value="장수 일람 연동">');
    $btn.on('click', function () {
        void runAnalysis();
        $btn.prop("disabled", true);
        const $tr0 = $('table:eq(0) tr:eq(0)');
        $tr0.append('<td><strong>*벌점 순 정렬*</strong><br><span style="color:yellow">벌점 1500점 이상</span>, <span style="color:lightgreen">벌점 200점 이상</span>, ' +
            '<span style="text-decoration:line-through">삭턴장</span>, <span style="color:cyan">ⓝ장</span>' + '<br><strong>전투장 :</strong> 만능장 + 무장 + 지장 + 평범장 - 삭턴자(만능, 무, 지, 평범) </td>');
    });



    $frame.append($btn);
});