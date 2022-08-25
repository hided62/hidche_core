import $ from 'jquery';
import 'bootstrap';
import download from 'downloadjs';
import { unwrap } from "@util/unwrap";
import { isInteger } from 'lodash-es';
import { errUnknown } from '@/common_legacy';
import { getNPCColor } from './utilGame';
import { combineArray } from "@util/combineArray";
import { isBrightColor } from "@util/isBrightColor";
import { numberWithCommas } from "@util/numberWithCommas";
import { unwrap_any } from '@util/unwrap_any';
import type { BasicGeneralListResponse, InvalidResponse } from '@/defs';
import { formatTime } from '@util/formatTime';
import { Modal } from 'bootstrap';

type CityAttackerInfo = {
    level: number,
}

type CityBasicInfo = {
    nation?: number,
    level: number,
    def: number,
    wall: number,
    city?: number,
}

type NationBasicInfo = {
    level: number,
    type: string,
    tech: number,
    capital: number,
}

type GeneralInfo = {
    no: number,
    npc?: number
    name: string,
    officer_level: number,
    explevel: number,

    leadership: number,
    horse: string,
    strength: number,
    weapon: string,
    intel: number,
    book: string,
    item: string,

    injury: number,

    rice: number,

    personal: string,
    special2: string,

    crew: number,
    crewtype: number,
    atmos: number,
    train: number,

    dex1: number,
    dex2: number,
    dex3: number,
    dex4: number,
    dex5: number,

    defence_train: number,

    warnum: number,
    killnum: number,
    killcrew: number,

    officer_city?: number,
};

type BattleInfo = {
    attackerGeneral: GeneralInfo,
    attackerCity: CityAttackerInfo | CityBasicInfo,
    attackerNation: NationBasicInfo,
    defenderGenerals: GeneralInfo[],
    defenderCity: CityBasicInfo,
    defenderNation: NationBasicInfo,
    year: number,
    month: number,
    repeatCnt: number,
};

type ExportedGeneralInfo = {
    objType: 'general',
    data: GeneralInfo,
}

type ExportedBattleInfo = {
    objType: 'battle',
    data: BattleInfo
}
type ExportedInfo = ExportedGeneralInfo | ExportedBattleInfo;

type BattleResult = {
    result: true,
    datetime: string,
    lastWarLog: {
        generalBattleResultLog: string,
        generalBattleDetailLog: string,
    },
    avgWar: number,
    phase: number,
    killed: number,
    maxKilled: number,
    minKilled: number,
    dead: number,
    maxDead: number,
    minDead: number,
    attackerRice: number,
    defenderRice: number,
    attackerSkills: Record<string, number>,
    defendersSkills: Record<string, number>[],
}

declare global {
    interface Window {
        nation?: NationBasicInfo,
        city?: CityBasicInfo,
        defaultSpecialDomestic: string,
    }
}

let modalImport: Modal|undefined = undefined;

$(function ($) {


    const $generalForm = $('.form_sample .general_detail');
    const $defenderHeaderForm = $('.form_sample .card-header');
    const $defenderColumn = $('.defender-column');

    const defenderNoList: Record<number, JQuery<HTMLElement>> = {};

    const $attackerCard = $('.attacker_form');

    const initBasicEvent = function () {

        if (window.nation && window.city) {
            $('.form_city_level').val(window.city.level);
            $('.form_def').val(window.city.def);
            $('.form_wall').val(window.city.wall);
            $('.form_nation_type').val(window.nation.type);
            $('.form_nation_level').val(window.nation.level);
            $('.form_tech').val(window.nation.tech / 1000);
            if (window.nation.capital == window.city.city) {
                $('.attacker_nation .form_is_capital:first').trigger('click');
                $('.defender_nation .form_is_capital:first').trigger('click');
            } else {
                $('.attacker_nation .form_is_capital:last').trigger('click');
                $('.defender_nation .form_is_capital:last').trigger('click');
            }
        } else {
            $('.attacker_nation .form_is_capital:last').trigger('click');
            $('.defender_nation .form_is_capital:last').trigger('click');
        }

        $('.form_injury').on('change', function () {
            const $this = $(this) as JQuery<HTMLInputElement>;
            const $general = getGeneralDetail($this);
            const $helptext = $general.find('.injury_helptext');

            const injury = parseInt($this.val() as string);
            //FIXME: PHP 코드와 항상 일치하도록 변경
            let text = '건강';
            let color = 'white';
            if (injury > 60) {
                text = '위독';
                color = 'red';
            } else if (injury > 40) {
                text = '심각';
                color = 'magenta';
            } else if (injury > 20) {
                text = '중상';
                color = 'orange';
            } else if (injury > 0) {
                text = '경상';
                color = 'yellow';
            }
            $helptext.html(text).css('color', color);
        });

        $('.export_general').on('click', function () {
            const $btn = $(this);
            const $general = getGeneralDetail($btn);

            const values = exportGeneralInfo($general);
            console.log(values);
        });
        $('.delete-defender').on('click', function () {
            const $card = getGeneralFrame($(this));
            deleteDefender($card);
        });
        $('.copy-defender').on('click', function () {
            const $card = getGeneralFrame($(this));
            copyDefender($card);
        });

        $('.add-defender').on('click', function () {
            addDefender();
        });

        $('.btn-general-load').on('click', function () {
            const $file = $(this).prev();
            $file[0].click();
        })

        $<HTMLInputElement>('.form_load_general_file').on('change', function (e) {
            e.preventDefault();
            const $this = $(this);
            const $card = getGeneralFrame($this);

            const files = unwrap(e.target.files);

            importGeneralInfoByFile(files, $card);
            return false;
        });

        $('.btn-general-import-server').on('click', function () {
            const $this = $(this);
            const $card = getGeneralFrame($this);

            const $modal = $('#importModal');
            $modal.data('target', $card);
            if(!modalImport){
                modalImport = new Modal($modal[0]);
            }
            modalImport.show();
        });

        $('.btn-general-save').on('click', function () {
            const $this = $(this);
            const $general = getGeneralDetail($this);
            const generalData = exportGeneralInfo($general);

            const filename = `general_${generalData.name}.json`;
            const saveData = JSON.stringify({
                objType: 'general',
                data: generalData
            }, null, 4);

            download(saveData, filename, 'application/json');
        });

        $('.btn-battle-load').on('click', function () {
            const $file = $(this).prev();
            $file[0].click();
        })

        $<HTMLInputElement>('.form_load_battle_file').on('change', function (e) {
            e.preventDefault();
            const files = unwrap(e.target.files);

            importBattleInfoByFile(files);
            return false;
        });

        $('.btn-battle-save').on('click', function () {
            const battleData = exportAllData();
            const dateText = formatTime(new Date(), 'yyyyMMdd_HHmmss');
            const filename = `battle_${dateText}.json`;
            const saveData = JSON.stringify({
                objType: 'battle',
                data: battleData
            }, null, 4);

            download(saveData, filename, 'application/json');
        })

        const $generals = $('.general_detail');
        $generals.on('dragover dragleave', function (e) {
            e.stopPropagation()
            e.preventDefault()
        })
        $generals.on('drop', function (e) {
            e.preventDefault();
            const $this = $(this);
            const $card = getGeneralFrame($this);

            const files = unwrap((unwrap(e.originalEvent) as DragEvent).dataTransfer).files;

            importGeneralInfoByFile(files, $card);
            return false;
        });

        const $battlePad = $('.dragpad_battle');
        $battlePad.on('dragover dragleave', function (e) {
            e.stopPropagation()
            e.preventDefault()
        })
        $battlePad.on('drop', function (e) {
            e.preventDefault();

            const files = unwrap((unwrap(e.originalEvent) as DragEvent).dataTransfer).files;

            importBattleInfoByFile(files);
            return false;
        });
    }

    const importGeneralInfo = function ($general: JQuery<HTMLElement>, data: GeneralInfo) {
        const setVal = function (query: string, val: string | number) {
            $general.find(query).val(val).trigger('change');
        }

        setVal('.form_general_name', data.name);

        setVal('.form_officer_level', data.officer_level);
        setVal('.form_exp_level', data.explevel);

        setVal('.form_leadership', data.leadership);
        setVal('.form_general_horse', data.horse);
        setVal('.form_strength', data.strength);
        setVal('.form_general_weap', data.weapon);
        setVal('.form_intel', data.intel);
        setVal('.form_general_book', data.book);
        setVal('.form_general_item', data.item);

        setVal('.form_injury', data.injury);

        setVal('.form_rice', data.rice);

        setVal('.form_general_character', data.personal);
        setVal('.form_general_special_war', data.special2);

        setVal('.form_crew', data.crew);
        setVal('.form_crewtype', data.crewtype);
        setVal('.form_atmos', data.atmos);
        setVal('.form_train', data.train);

        setVal('.form_dex1', data.dex1);
        setVal('.form_dex2', data.dex2);
        setVal('.form_dex3', data.dex3);
        setVal('.form_dex4', data.dex4);
        setVal('.form_dex5', data.dex5);
        setVal('.form_defence_train', data.defence_train);

        setVal('.form_warnum', data.warnum);
        setVal('.form_killnum', data.killnum);
        setVal('.form_killcrew', data.killcrew);

        if (!setGeneralNo($general, data.no)) {
            setGeneralNo($general, generateNewGeneralNo());
        }
    }

    const exportGeneralInfo = function ($general: JQuery<HTMLElement>): GeneralInfo {
        const getInt = function (query: string): number {
            return parseInt(unwrap_any<string>($general.find(query).val()));
        }

        const getVal = function (query: string): string {
            return unwrap_any<string>($general.find(query).val());
        }

        return {
            no: getGeneralNo($general),
            name: getVal('.form_general_name'),
            officer_level: getInt('.form_officer_level'),
            explevel: getInt('.form_exp_level'),

            leadership: getInt('.form_leadership'),
            horse: getVal('.form_general_horse'),
            strength: getInt('.form_strength'),
            weapon: getVal('.form_general_weap'),
            intel: getInt('.form_intel'),
            book: getVal('.form_general_book'),
            item: getVal('.form_general_item'),

            injury: getInt('.form_injury'),

            rice: getInt('.form_rice'),

            personal: getVal('.form_general_character'),
            special2: getVal('.form_general_special_war'),

            crew: getInt('.form_crew'),
            crewtype: getInt('.form_crewtype'),
            atmos: getInt('.form_atmos'),
            train: getInt('.form_train'),

            dex1: getInt('.form_dex1'),
            dex2: getInt('.form_dex2'),
            dex3: getInt('.form_dex3'),
            dex4: getInt('.form_dex4'),
            dex5: getInt('.form_dex5'),

            defence_train: getInt('.form_defence_train'),

            warnum: getInt('.form_warnum'),
            killnum: getInt('.form_killnum'),
            killcrew: getInt('.form_killcrew'),

        };
    }

    const importBattleInfoByFile = function (files: FileList) {
        if (files === null) {
            alert('파일 에러!');
            return false;
        }

        if (files.length < 1) {
            alert("파일 에러!");
            return false;
        }


        const file = files[0];
        if (file.size > 1024 * 1024) {
            alert('파일이 너무 큽니다!');
            return false;
        }
        if (file.type === '') {
            alert('폴더를 업로드할 수 없습니다!');
            return false;
        }

        const reader = new FileReader();
        reader.onload = function () {
            let battleData: ExportedInfo;
            try {
                battleData = JSON.parse(unwrap_any<string>(reader.result));
            } catch (e) {
                alert('올바르지 않은 파일 형식입니다');
                return false;
            }

            if (!('objType' in battleData)) {
                alert('파일 형식을 확인할 수 없습니다');
                return false;
            }

            if (battleData.objType != 'battle') {
                alert('전투 데이터가 아닙니다');
                return false;
            }

            importBattleInfo(battleData.data);
            return true;
        };

        try {

            reader.readAsText(file);
        } catch (e) {
            alert('파일을 읽는데 실패했습니다.');
            return false;
        }

        return true;
    }

    const importGeneralInfoByFile = function (files: FileList, $card: JQuery<HTMLElement>) {
        if ($card === undefined) {
            $card = addDefender();
        }

        if (files === null) {
            alert('파일 에러!');
            return false;
        }

        if (files.length < 1) {
            alert("파일 에러!");
            return false;
        }


        const file = files[0];
        if (file.size > 1024 * 1024) {
            alert('파일이 너무 큽니다!');
            return false;
        }
        if (file.type === '') {
            alert('폴더를 업로드할 수 없습니다!');
            return false;
        }

        const reader = new FileReader();
        reader.onload = function () {
            let generalData: ExportedInfo;
            try {
                generalData = JSON.parse(unwrap_any<string>(reader.result));
            } catch (e) {
                alert('올바르지 않은 파일 형식입니다');
                return false;
            }

            if (!('objType' in generalData)) {
                alert('파일 형식을 확인할 수 없습니다');
                return false;
            }

            if (generalData.objType == 'battle') {
                importBattleInfo(generalData.data);
                return true;
            }
            if (generalData.objType != 'general') {
                alert('장수 데이터가 아닙니다');
                return false;
            }

            $card.find('.form_load_general_file').val('');

            importGeneralInfo($card, generalData.data);
            return true;
        };

        try {

            reader.readAsText(file);
        } catch (e) {
            alert('파일을 읽는데 실패했습니다.');
            return false;
        }

        return true;
    }

    const extendGeneralInfoForDB = function (generalData: GeneralInfo) {

        const dbVal = {
            nation: (generalData.no) <= 1 ? 1 : 2,
            city: (generalData.no) <= 1 ? 1 : 3,
            turntime: '2018-08-26 12:00',
            special: window.defaultSpecialDomestic,
            leadership_exp: 0,
            strength_exp: 0,
            intel_exp: 0,

            gold: 10000,

            dedication: 0,

            recent_war: '2018-08-26 12:00',
            experience: Math.pow(generalData.explevel, 2),
        };

        return $.extend({}, generalData, dbVal);
    }

    const getGeneralFrame = function ($btn: JQuery<HTMLElement>) {
        const $card = $btn.closest('.general_form');
        return $card;
    }

    const getGeneralDetail = function ($btn: JQuery<HTMLElement>) {
        const $card = getGeneralFrame($btn);
        const $general = $card.find('.general_detail');
        return $general;
    }

    const getGeneralNo = function ($btn: JQuery<HTMLElement>) {
        return parseInt(getGeneralFrame($btn).data('general_no'));
    }

    const setGeneralNo = function ($btn: JQuery<HTMLElement>, value: number) {
        if (value == 1) {
            //1번은 무조건 공격자임
            return false;
        }
        if (value in defenderNoList) {
            return false;
        }
        const $card = getGeneralFrame($btn);
        $card.data('general_no', value);
        defenderNoList[value] = $card;
        return true;
    }

    const generateNewGeneralNo = function () {
        for (; ;) {
            const newGeneralNo = Math.floor(Math.random() * (1 << 24)) + 2;
            if (newGeneralNo in defenderNoList) {
                continue;
            }
            return newGeneralNo;
        }
    }

    const deleteGeneralNo = function ($btn: JQuery<HTMLElement>) {
        const $card = getGeneralFrame($btn);
        $card.removeData('general_no');
        const generalNo = getGeneralNo($card);
        delete defenderNoList[generalNo];
    }

    const addDefender = function ($cardAfter?: JQuery<HTMLElement>) {
        const $newCard = $('<div class="card mb-2 defender_form general_form"></div>');

        if ($cardAfter === undefined) {
            $defenderColumn.append($newCard);
        } else {
            $cardAfter.after($newCard);
        }

        $newCard.append($defenderHeaderForm.clone(true, true));
        $newCard.append($generalForm.clone(true, true));

        //$newGeneral = getGeneralDetail($newCard);
        setGeneralNo($newCard, generateNewGeneralNo());

        return $newCard;
    }

    const deleteDefender = function ($card: JQuery<HTMLElement>) {
        deleteGeneralNo($card);
        $card.detach();
    }

    const copyDefender = function ($card: JQuery<HTMLElement>) {
        const $general = getGeneralDetail($card);

        const generalData = exportGeneralInfo($general);
        const $newObj = addDefender($card);
        importGeneralInfo(getGeneralDetail($newObj), generalData);
    }

    const importBattleInfo = function (battleData: BattleInfo) {

        $('.form_load_battle_file').val('');
        console.log(battleData);

        const $attackerNation = $('.attacker_nation');
        const $defenderNation = $('.defender_nation');

        const attackerGeneral = battleData.attackerGeneral;
        const attackerCity = battleData.attackerCity;
        const attackerNation = battleData.attackerNation;

        const defenderGenerals = battleData.defenderGenerals;
        const defenderCity = battleData.defenderCity;
        const defenderNation = battleData.defenderNation;

        $('#year').val(battleData.year);
        $('#month').val(battleData.month);
        $('#repeat_cnt').val(battleData.repeatCnt);

        $('.delete-defender').trigger('click');

        $attackerNation.find('.form_nation_type').val(attackerNation.type);
        $attackerNation.find('.form_tech').val(Math.floor(attackerNation.tech / 1000));
        $attackerNation.find('.form_nation_level').val(attackerNation.level);
        if (attackerNation.capital == 1) {
            $attackerNation.find('.form_is_capital:first').trigger('click');
        } else {
            $attackerNation.find('.form_is_capital:last').trigger('click');
        }
        $attackerNation.find('.form_city_level').val(attackerCity.level);

        importGeneralInfo($('.attacker_form'), attackerGeneral);

        $defenderNation.find('.form_nation_type').val(defenderNation.type);
        $defenderNation.find('.form_tech').val(Math.floor(defenderNation.tech / 1000));
        $defenderNation.find('.form_nation_level').val(defenderNation.level);
        if (defenderNation.capital == 1) {
            $defenderNation.find('.form_is_capital:first').trigger('click');
        } else {
            $defenderNation.find('.form_is_capital:last').trigger('click');
        }
        $defenderNation.find('.form_city_level').val(defenderCity.level);
        $('#city_def').val(defenderCity.def);
        $('#city_wall').val(defenderCity.wall);

        $.each(defenderGenerals, function (idx, defenderGeneral) {
            const $card = addDefender();
            importGeneralInfo($card, defenderGeneral);
        });
    }

    const exportAllData = function (): BattleInfo {
        const $attackerNation = $('.attacker_nation');
        const $defenderNation = $('.defender_nation');

        const attackerGeneral = exportGeneralInfo($('.attacker_form'));

        const attackerCity = {
            level: parseInt($attackerNation.find('.form_city_level').val() as string),
        };

        const attackerNation = {
            type: unwrap_any<string>($attackerNation.find('.form_nation_type').val()),
            tech: parseInt($attackerNation.find('.form_tech').val() as string) * 1000,
            level: parseInt($attackerNation.find('.form_nation_level').val() as string),
            capital: $attackerNation.find('.form_is_capital:checked').val() == '1' ? 1 : 2,
        }

        const defenderGenerals = $('.defender_form').map(function () {
            return exportGeneralInfo($(this));
        }).toArray();

        const defenderCity = {
            def: parseInt($('#city_def').val() as string),
            wall: parseInt($('#city_wall').val() as string),
            level: parseInt($defenderNation.find('.form_city_level').val() as string),
        };

        const defenderNation = {
            type: unwrap_any<string>($defenderNation.find('.form_nation_type').val()),
            tech: parseInt(unwrap_any<string>($defenderNation.find('.form_tech').val())) * 1000,
            level: parseInt(unwrap_any<string>($defenderNation.find('.form_nation_level').val())),
            capital: $defenderNation.find('.form_is_capital:checked').val() == '1' ? 3 : 4,
        }

        const year = parseInt(unwrap_any<string>($('#year').val()));
        const month = parseInt(unwrap_any<string>($('#month').val()));
        const repeatCnt = parseInt(unwrap_any<string>($('#repeat_cnt').val()));

        return {
            attackerGeneral: attackerGeneral,
            attackerCity: attackerCity,
            attackerNation: attackerNation,

            defenderGenerals: defenderGenerals,
            defenderCity: defenderCity,
            defenderNation: defenderNation,

            year: year,
            month: month,
            repeatCnt: repeatCnt,
        };
    }

    const extendAllDataForDB = function (allData: BattleInfo): BattleInfo {
        const defaultNation = {
            nation: 0,
            name: '재야',
            capital: 0,
            level: 0,
            gold: 0,
            rice: 2000,
            type: 'None',
            tech: 0,
            gennum: 200,
        };

        const defaultCity = {
            nation: 0,
            supply: 1,
            name: '도시',

            pop: 500000,
            agri: 10000,
            comm: 10000,
            secu: 10000,
            def: 1000,
            wall: 1000,

            trust: 100,

            pop_max: 600000,
            agri_max: 12000,
            comm_max: 12000,
            secu_max: 10000,
            def_max: 12000,
            wall_max: 12000,

            dead: 0,

            state: 0,

            conflict: '{}',
        };

        const attackerNation = $.extend({}, defaultNation, allData.attackerNation);
        attackerNation.nation = 1;
        attackerNation.name = '출병국';

        const attackerCity = $.extend({}, defaultCity, allData.attackerCity) as CityBasicInfo;
        attackerCity.nation = 1;
        attackerCity.city = 1;

        const attackerGeneral = extendGeneralInfoForDB(allData.attackerGeneral);
        if (2 <= attackerGeneral.officer_level && attackerGeneral.officer_level <= 4) {
            attackerGeneral.officer_city = 1;
        } else {
            attackerGeneral.officer_city = 0;
        }

        const defenderNation = $.extend({}, defaultNation, allData.defenderNation);
        defenderNation.nation = 2;
        defenderNation.name = '수비국';

        const defenderCity = $.extend({}, defaultCity, allData.defenderCity);
        defenderCity.nation = 2;
        defenderCity.city = 3;
        defenderCity.wall_max = Math.floor(defenderCity.wall / 5 * 6);
        defenderCity.def_max = Math.floor(defenderCity.def / 5 * 6);

        const defenderGenerals: GeneralInfo[] = [];
        $.each(allData.defenderGenerals, function () {
            const defenderGeneral = extendGeneralInfoForDB(this);
            if (2 <= defenderGeneral.officer_level && defenderGeneral.officer_level <= 4) {
                defenderGeneral.officer_city = 3;
            } else {
                defenderGeneral.officer_city = 0;
            }

            defenderGenerals.push(defenderGeneral);
        });


        return $.extend({}, allData, {
            attackerGeneral: attackerGeneral,
            attackerCity: attackerCity,
            attackerNation: attackerNation,

            defenderGenerals: defenderGenerals,
            defenderCity: defenderCity,
            defenderNation: defenderNation,
        });
    }

    const parseSkillCount = function (skills: Record<string, number>) {
        const result: string[] = [];
        for (const [key, value] of Object.entries(skills)) {
            result.push(`${key}(${toPretty(value)}회)`);
        }

        if (result.length == 0) {
            return '-';
        }
        return result.join(', ');
    }

    const toPretty = function (number: number) {
        if (isInteger(number)) {
            number = Math.floor(number);
        } else {
            number = parseFloat(number.toFixed(2));
        }
        return numberWithCommas(number);
    }

    const showBattleResult = function (result: BattleResult) {
        $('#result_datetime').html(result.datetime);
        $('#result_warcnt').html(toPretty(result.avgWar));
        $('#result_phase').html(toPretty(result.phase));
        $('#result_killed').html(toPretty(result.killed));
        if (result.minKilled != result.maxKilled) {
            $('#result_maxKilled').html(toPretty(result.maxKilled));
            $('#result_minKilled').html(toPretty(result.minKilled));
            $('#result_varKilled').show();
        } else {
            $('#result_varKilled').hide();
        }
        $('#result_dead').html(toPretty(result.dead));
        if (result.minDead != result.maxDead) {
            $('#result_maxDead').html(toPretty(result.maxDead));
            $('#result_minDead').html(toPretty(result.minDead));
            $('#result_varDead').show();
        } else {
            $('#result_varDead').hide();
        }

        $('#result_attackerRice').html(toPretty(result.attackerRice));
        $('#result_defenderRice').html(toPretty(result.defenderRice));
        $('#result_attackerSkills').html(parseSkillCount(result.attackerSkills));

        $('.result_defenderSkills').detach();

        const $summary = $('#battle_result_summary');

        for (const [idx, defenderSkills] of Object.entries(result.defendersSkills)) {
            console.log(defenderSkills);
            const $result = $(`<tr class='result_defenderSkills'><th>수비자${parseInt(idx) + 1} 스킬</th><td></td></tr>`);
            $result.find('td').html(parseSkillCount(defenderSkills));
            $summary.append($result);
        }

        $('#generalBattleResultLog').html(result.lastWarLog.generalBattleResultLog);
        $('#generalBattleDetailLog').html(result.lastWarLog.generalBattleDetailLog);

    }

    const beginBattle = function () {
        const data = extendAllDataForDB(exportAllData());
        console.log(data);
        $.ajax({
            type: 'post',
            url: 'j_simulate_battle.php',
            dataType: 'json',
            data: {
                action: 'battle',
                query: JSON.stringify(data),
            }
        }).then(function (result) {
            console.log(result);
            if (!result.result) {
                alert(result.reason);
                return;
            }
            showBattleResult(result);

        }, function () {
            alert('전투 개시 실패!');
        });
    }


    const reorderDefender = function (defenderOrder: number[]) {
        for (const generalNo of defenderOrder) {

            if (!(generalNo in defenderNoList)) {
                //음..?
                alert(`${generalNo}이 수비자 리스트에 없습니다. 버그인 듯 합니다.`);
                return true;
            }

            const $defenderObj = defenderNoList[generalNo];
            $defenderObj.detach();
            $defenderColumn.append($defenderObj);
        }
    }

    const requestReorderDefender = function () {
        const data = extendAllDataForDB(exportAllData());
        console.log(data);
        $.ajax({
            type: 'post',
            url: 'j_simulate_battle.php',
            dataType: 'json',
            data: {
                action: 'reorder',
                query: JSON.stringify(data),
            }
        }).then(function (result) {
            console.log(result);
            if (!result.result) {
                alert(result.reason);
                return;
            }
            reorderDefender(result.order);

        }, function () {
            alert('재정렬 실패!');
        });
    }

    let initGeneralList = false;

    $('#importFromDB').on('click', function () {
        const generalID = $('#modalSelector').val();
        console.log(generalID);


        $.post({
            url: 'j_export_simulator_object.php',
            dataType: 'json',
            data: {
                destGeneralID: generalID
            }
        }).then(function (data) {

            if (!data.result) {
                alert(data.reason);
                return false;
            }

            const $modal = $('#importModal');
            const $card = $modal.data('target');
            importGeneralInfo($card, data.general);

            if(modalImport){
                modalImport.hide();
            }
        }, errUnknown);


    });

    unwrap(document.querySelector('#importModal')).addEventListener('show.bs.modal', function () {
        if (!initGeneralList) {
            const $list = $('#modalSelector');

            const addNation = function (generalList: GeneralInfo[], nationName: string, color: string) {

                generalList.sort(function (lhs, rhs) {
                    if (lhs.npc != rhs.npc) {
                        return (lhs.npc ?? 0) - (rhs.npc ?? 0);
                    }
                    if (lhs.name < rhs.name) {
                        return -1;
                    }
                    if (lhs.name > rhs.name) {
                        return 1;
                    }
                    return 0;
                })
                const $optGroup = $(`<optgroup label="${nationName}"></optgroup>`);
                $optGroup.css('background-color', color);
                $optGroup.css('color', isBrightColor(color) ? 'black' : 'white');

                for (const general of generalList) {
                    const $item = $(`<option value="${general.no}">${general.name}</option>`);
                    if (general.npc) {
                        $item.css('color', unwrap(getNPCColor(general.npc)));
                    } else {
                        $item.css('color', 'white');
                    }
                    $item.css('background-color', 'black');
                    $optGroup.append($item);
                }
                $list.append($optGroup);
            }
            $.post({
                url: 'j_get_basic_general_list.php',
                dataType: 'json',
                data: {
                    req: 2,
                }
            }).then(function (data: BasicGeneralListResponse | InvalidResponse) {
                if (!data.result) {
                    alert(data.reason);
                    if(modalImport){
                        modalImport.hide();
                    }
                    return false;
                }

                const nations = data.nation;
                const myNationID = data.nationID;

                $list.empty();
                //자국 먼저

                if (0 in data.list) {
                    nations[0] = {
                        nation: 0,
                        name: '재야',
                        color: '#000000'
                    };
                }



                addNation(combineArray(data.list[myNationID], data.column) as GeneralInfo[], nations[myNationID].name, nations[myNationID].color);

                for (const nationID of Object.keys(data.list)) {
                    if (parseInt(nationID) == myNationID) {
                        continue;
                    }
                    addNation(combineArray(data.list[nationID], data.column) as GeneralInfo[], nations[nationID].name, nations[nationID].color);
                }
                initGeneralList = true;
            }, errUnknown);
        }

    });

    initBasicEvent();
    $attackerCard.append($generalForm.clone(true, true));
    addDefender();

    $('.btn-begin_battle').on('click', function () {
        beginBattle();
    });

    $('.btn-reorder_defender').on('click', function () {
        requestReorderDefender();
    })
});