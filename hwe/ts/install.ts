import $ from "jquery";
import 'bootstrap';
import { setAxiosXMLHttpRequest } from "./util/setAxiosXMLHttpRequest";
import axios from "axios";
import { InvalidResponse } from "./defs";
import { Rules } from "async-validator";
import { isArray } from "lodash";
import { JQValidateForm } from "./util/jqValidateForm";
import { unwrap } from "./util";
import { convertFormData } from "./util/convertFormData";

type ResponseScenarioItem = {
    year?: number,
    title: string,
    npc_cnt: number,
    npcEx_cnt: number,
    npcNeutral_cnt: number,
    nation: Record<number, {
        id: number,
        name: string,
        color: string,
        gold: number,
        rice: number,
        infoText: string,
        tech: number,
        type: string,
        nationLevel: number,
        cities: string[],
        generals: number,
        generalsEx: number,
        generalsNeutral: number
    }>
}

type ExpandedScenarioItem = ResponseScenarioItem & {
    category: string,
    idx: string,
    year: number,
}

type ResponseScenario = {
    result: true,
    scenario: Record<number, ResponseScenarioItem>
}

async function loadScenarios() {

    let result: InvalidResponse | ResponseScenario;
    try {
        const response = await axios({
            url: 'j_load_scenarios.php',
            method: 'post',
            responseType: 'json'
        });
        result = response.data;
    }
    catch (e) {
        alert(`시나리오를 불러오는데 실패했습니다.: ${e}`);
        return;
    }

    if (!result.result) {
        alert(result.reason);
        return;
    }

    const list: Record<string, Record<string, ExpandedScenarioItem>> = {};
    const pat = /【(.*?)[0-9\-_.a-zA-Z]*】/;

    for (const [idx, value] of Object.entries(result.scenario)) {
        const title = value.title || "-";
        const categoryRes = pat.exec(title);
        const category = categoryRes ? categoryRes[1] : '-';

        if (!(category in list)) {
            list[category] = {};
        }

        list[category][idx] = {
            ...value,
            title: title,
            category: category,
            idx: idx,
            year: value.year ?? 180
        };
    }


    const $select = $('#scenario_sel');
    for (const [category, items] of Object.entries(list)) {
        const $optgroup = $('<optgroup>').attr('label', category);
        for (const [idx, scenario] of Object.entries(items)) {
            const $option = $('<option>')
                .data('scenario', scenario)
                .val(idx)
                .html(scenario.title);
            $optgroup.append($option);
        }
        $select.append($optgroup);

    }

    $select.val(0);
    $select.trigger('change');
}

function scenarioPreview() {
    const $select = $('#scenario_sel');
    const $option = $select.find('option:selected');

    const $year = $('#scenario_begin');
    const $npc = $('#scenario_npc');
    const $npcEx = $('#scenario_npc_extend');
    const $nation = $('#scenario_nation');

    const scenario = $option.data('scenario') as ExpandedScenarioItem;
    console.log(scenario.idx, scenario.title);

    $year.html(`${scenario.year}년`);
    $npc.html(`${scenario.npc_cnt}명`);
    if (scenario.npcEx_cnt == 0) {
        $npcEx.html('');
    } else {
        $npcEx.html('+{0}명'.format(scenario.npcEx_cnt));
    }

    $nation.html('');
    $.each(scenario.nation, function (idx, nation) {
        $nation.append('<span style="color:{0}">{1}</span> {2}명. {3}<br>'.format(
            nation.color, nation.name, nation.generals, nation.cities.join(', ')
        ));
    });
}

const descriptor: Rules = {
    turnterm: {
        required: true,
    },
    sync: {
        required: true,
    },
    scenario: {
        required: true,
    },
    fiction: {
        required: true,
    },
    extend: {
        required: true,
    },
    npcmode: {
        required: true,
    },
    show_img_level: {
        required: true,
    },
    tournament_trig: {
        required: true,
    },
    join_mode: {
        required: true,
    },
    autorun_user: {
        type: 'array',
        defaultField: {
            type: 'enum',
            enum: ['develop', 'warp', 'recruit', 'recruit-high', 'train', 'battle']
        },
    },
    autorun_user_minutes: {
        type: 'number',
        required: true,
        min: 0,
        validator: (rule, value, _callback, source) => {
            if (!isArray(source.autorun_user)) {
                return new Error('옵션이 올바른 타입이 아닙니다.');
            }
            if (source.autorun_user.length == 0 && parseInt(value) > 0) {
                return new Error('유효 시간과 옵션은 동시에 설정해야합니다.');
            }
        }
    }
};

function formSetup() {

    const autorunMap: Map<string, string> = new Map([
        ['autorun_develop', 'develop'],
        ['autorun_warp', 'warp'],
        ['autorun_recruit', 'recruit'],
        ['autorun_recruit_high', 'recruit_high'],
        ['autorun_train', 'train'],
        ['autorun_battle', 'battle'],
        ['autorun_chief', 'chief'],
    ]);
    const validator = new JQValidateForm($('#game_form'), descriptor, {
        postParse: (values) => {
            const newValues: Record<string, string | string[]> & { autorun_user: string[] } = {
                autorun_user: []
            };
            for (const [key, value] of Object.entries(values)) {
                if (!autorunMap.has(key)) {
                    newValues[key] = value;
                    continue;
                }

                const convValue = unwrap(autorunMap.get(key));
                newValues.autorun_user.push(convValue);
            }
            return newValues;
        }
    });

    $('#game_form').on('submit', async function (e) {
        e.preventDefault();
        const values = await validator.validate();
        if (values === undefined) {
            return;
        }

        let result: InvalidResponse;
        try{
            const response = await axios({
                url: 'j_install.php',
                method: 'post',
                responseType: 'json',
                data: convertFormData(values)
            });
            result = response.data;
        }
        catch(e){
            alert(`알수 없는 에러: ${e}`);
            return;
        }

        if(!result.result){
            alert(result.reason);
            return;
        }

        alert('게임이 리셋되었습니다.');
        location.href = '..';
    });
}

$(async function () {
    setAxiosXMLHttpRequest();
    void loadScenarios();
    $('#scenario_sel').on('change', scenarioPreview);
    formSetup();
})