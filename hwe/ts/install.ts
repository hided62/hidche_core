import $ from "jquery";
import Popper from 'popper.js';
(window as unknown as {Popper:unknown}).Popper = Popper;//XXX: 왜 popper를 이렇게 불러야 하는가?
import 'bootstrap';
import { setAxiosXMLHttpRequest } from "./util/setAxiosXMLHttpRequest";
import axios from "axios";
import { InvalidResponse } from "./defs";
import { JQValidateForm, NamedRules } from "./util/jqValidateForm";
import { convertFormData } from "./util/convertFormData";
import { exportWindow } from "./util/exportWindow";

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
        $npcEx.html(`+${scenario.npcEx_cnt}명`);
    }

    $nation.html('');
    $.each(scenario.nation, function (idx, nation) {
        $nation.append(`<span style="color:${nation.color}">${nation.name}</span> ${nation.generals}명. ${nation.cities.join(', ')}<br>`);
    });
}

type InstallFormType = {
    turnterm: number,
    sync: 1|0,
    scenario: number,
    fiction: number,
    extend: number,
    npcmode: number,
    show_img_level: number,
    tournament_trig: number,
    join_mode: number,
    autorun_user: string[],
    autorun_user_minutes: number,
    reserve_open?: string,
    pre_reserve_open?: string,
}
const descriptor: NamedRules<InstallFormType> = {
    turnterm: {
        required: true,
        type: "enum",
        enum: [1, 2, 5, 10, 20, 30, 60, 120],
        transform: parseInt,
    },
    sync: {
        required: true,
        type: "enum",
        enum: [1, 0],
        transform: parseInt,
    },
    scenario: {
        required: true,
    },
    fiction: {
        required: true,
        type: "integer",
        transform: parseInt,
    },
    extend: {
        required: true,
        type: "integer",
        transform: parseInt,
    },
    npcmode: {
        required: true,
        type: "integer",
        transform: parseInt,
    },
    show_img_level: {
        required: true,
        type: "integer",
        transform: parseInt,
    },
    tournament_trig: {
        required: true,
        type: "integer",
        transform: parseInt,
    },
    join_mode: {
        required: true,
        type: "enum",
        enum: ["full", "onlyRandom"]
    },
    autorun_user: {
        type: 'array',
        required: false,
        defaultField: {
            type: 'enum',
            enum: ['develop', 'warp', 'recruit', 'recruit_high', 'train', 'battle', 'chief']
        },
    },
    autorun_user_minutes: {
        type: 'integer',
        required: true,
        transform: parseInt,
        min: 0,
        validator: (rule, value, _callback, source) => {
            if(value == 0 && !source.autorun_user?.length){
                return true;
            }
            if(value != 0 && source.autorun_user?.length){
                return true;
            }
            return new Error('유효 시간과 옵션은 동시에 설정해야합니다.');
        }
    },
    reserve_open: {
        type: 'pattern',
        pattern: new RegExp(/\d{4,4}-\d{2,2}-\d{2,2} \d{2,2}:\d{2,2}/)
    },
    pre_reserve_open: {
        type: 'pattern',
        pattern: new RegExp(/\d{4,4}-\d{2,2}-\d{2,2} \d{2,2}:\d{2,2}/)
    }
};

function formSetup() {
    const validator = new JQValidateForm($('#game_form'), descriptor);
    validator.installChangeHandler();

    $('#game_form').on('submit', async function (e) {
        e.preventDefault();
        const values = await validator.validate();
        if (values === undefined) {
            return;
        }

        let result: InvalidResponse;
        try {
            const response = await axios({
                url: 'j_install.php',
                method: 'post',
                responseType: 'json',
                data: convertFormData(values)
            });
            result = response.data;
        }
        catch (e) {
            alert(`알수 없는 에러: ${e}`);
            return;
        }

        if (!result.result) {
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

exportWindow($, '$');