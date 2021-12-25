import '@scss/processing.scss';

import { unwrap } from "@util/unwrap";
import BootstrapVue3 from 'bootstrap-vue-3'
import Multiselect from 'vue-multiselect';
import { commandMap as GeneralActions } from "@/processing/General";
import { commandMap as NationActions } from '@/processing/Nation';
import { App, createApp } from 'vue';
import { auto500px } from './util/auto500px';
import { isString } from 'lodash';
import { Args, testSubmitArgs } from './processing/args';
import { sammoAPI, ValidResponse } from './util/sammoAPI';

declare const turnList: number[];

async function submitCommand<T extends ValidResponse>(isChiefTurn: boolean, turnList: number[], action: string, arg: Args): Promise<T> {
    const target = isChiefTurn ? 'Command/ReserveCommand' : 'NationCommand/ReserveCommand';

    try {
        const testResult = testSubmitArgs(arg);
        if (testResult !== true) {
            throw new TypeError(`Invalied Type ${testResult[0]}, ${testResult[2]} should be ${testResult[1]}`);
        }
        console.log('trySubmit', arg);
        const response = await sammoAPI(target, {
                action,
                turnList,
                arg,
        });

        if (!isChiefTurn) {
            window.location.href = './';
        } else {
            window.location.href = 'v_chiefCenter.php';
        }

        return response as T;
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
            return undefined;
        }
        return createApp(GeneralActions[moduleName]);
    }
    if (groupName == 'Nation') {
        const moduleName = entryInfo[1];
        if (!(moduleName in NationActions)) {
            console.error(`${moduleName}이 ${groupName}에 없음`);
            return undefined;
        }
        return createApp(NationActions[moduleName]);
    }

    console.error('알수')
    return undefined;
}());

if (app === undefined) {
    const div = document.createElement('div');
    div.innerHTML = `모듈의 view가 없습니다. ${JSON.stringify(entryInfo)}`;
    document.body.appendChild(div);
    console.error(`모듈이 지정되지 않음`, entryInfo);
}
else {
    const div = unwrap(document.querySelector('#container'));
    div.addEventListener('customSubmit', (e: Event) => {
        const { detail } = e as unknown as CustomEvent<Args>;
        void submitCommand(entryInfo[0] == 'Nation', turnList, entryInfo[1], detail);
    }, true);

    app.use(BootstrapVue3).component('v-multiselect', Multiselect).mount('#container');
}


auto500px();