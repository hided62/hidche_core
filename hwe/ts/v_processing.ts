import '@scss/processing.scss';

import $ from 'jquery';
exportWindow($, '$');
import { exportWindow } from '@util/exportWindow';
import axios from 'axios';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { convertFormData } from '@util/convertFormData';
import { InvalidResponse } from '@/defs';
import { unwrap } from "@util/unwrap";
import { defaultSelectCityByMap } from '@/defaultSelectCityByMap';
import { defaultSelectNationByMap } from '@/defaultSelectNationByMap';
import { colorSelect } from '@/colorSelect';
import { recruitCrewForm } from '@/recruitCrewForm';
import BootstrapVue3 from 'bootstrap-vue-3'
import Multiselect from 'vue-multiselect';
import * as GeneralActions from "@/processing/General";
import * as NationActions from "@/processing/Nation";
import { App, createApp } from 'vue';
import { auto500px } from './util/auto500px';
import { isString } from 'lodash';
import { Args, testSubmitArgs } from './processing/args';

declare const turnList: number[];

setAxiosXMLHttpRequest();

async function submitCommand<T>(isChiefTurn: boolean, turnList: number[], command: string, args: Args): Promise<T> {


    const target = isChiefTurn ? 'j_set_chief_command.php' : 'j_set_general_command.php';

    try {
        const testResult = testSubmitArgs(args);
        if (testResult !== true) {
            throw new TypeError(`Invalied Type ${testResult[0]}, ${testResult[2]} should be ${testResult[1]}`);
        }
        console.log('trySubmit', args);
        const response = await axios({
            url: target,
            responseType: 'json',
            method: 'post',
            data: convertFormData({
                action: command,
                turnList: turnList,
                arg: JSON.stringify(args)
            })
        });
        const data = response.data as InvalidResponse;
        if (!data.result) {
            throw data.reason;
        }

        if (!isChiefTurn) {
            window.location.href = './';
        } else {
            window.location.href = 'b_chiefcenter.php';
        }

        return data as unknown as T;
    }
    catch (e) {
        console.error(e);
        if (isString(e)) {
            alert(e);
        }
        throw e;
    }
}



declare const entryInfo: ['General', keyof typeof GeneralActions] | ['Nation', keyof typeof NationActions];

const app: App<Element> | undefined = (function () {
    //NOTE: route를 쓴다?
    const groupName = entryInfo[0];
    if (groupName == 'General') {
        const moduleName = entryInfo[1];
        if (!(moduleName in GeneralActions)) {
            console.error(`${moduleName}이 ${groupName}에 없음`);
        }
        return createApp(GeneralActions[moduleName]);
    }
    if (groupName == 'Nation') {
        const moduleName = entryInfo[1];
        if (!(moduleName in GeneralActions)) {
            console.error(`${moduleName}이 ${groupName}에 없음`);
        }
        return createApp(NationActions[moduleName]);
    }

    console.error('알수')
    return undefined;
}());

if (app === undefined) {
    console.error(`모듈이 지정되지 않음`, entryInfo);
}
else {

    const div = unwrap(document.querySelector('#container'));
    div.addEventListener('customSubmit', (e: Event) => {
        const {detail} = e as unknown as CustomEvent<Args>;
        void submitCommand(entryInfo[0] == 'Nation', turnList, entryInfo[1], detail);
    }, true);

    app.use(BootstrapVue3).component('v-multiselect', Multiselect).mount('#container');
}


auto500px();