import '@scss/processing.scss';

import { unwrap } from "@util/unwrap";
import BootstrapVue3 from 'bootstrap-vue-3'
import Multiselect from 'vue-multiselect';
import { commandMap as GeneralActions } from "@/processing/General";
import { commandMap as NationActions } from '@/processing/Nation';
import { type App, createApp } from 'vue';
import { auto500px } from './util/auto500px';
import { isString } from 'lodash';
import { type Args, testSubmitArgs } from './processing/args';
import { SammoAPI } from './SammoAPI';
import { StoredActionsHelper } from './util/StoredActionsHelper';
import type { ReserveCommandResponse } from './defs/API/Command';
import { htmlReady } from './util/htmlReady';
import { insertCustomCSS } from './util/customCSS';

declare const staticValues: {
    serverNick: string,
    turnList: number[],
    mapName: string,
    unitSet: string,
};

const { turnList } = staticValues;

async function submitCommand<T extends ReserveCommandResponse>(isChiefTurn: boolean, turnList: number[], action: string, arg: Args): Promise<T> {
    const targetAPI = isChiefTurn ? SammoAPI.NationCommand.ReserveCommand : SammoAPI.Command.ReserveCommand;

    try {
        const testResult = testSubmitArgs(arg);
        if (testResult !== true) {
            throw new TypeError(`Invalied Type ${testResult[0]}, ${testResult[2]} should be ${testResult[1]}`);
        }
        console.log('trySubmit', arg);
        const responseP = targetAPI({
                action,
                turnList,
                arg,
        }) as Promise<T>;

        const storedActionsHelper = new StoredActionsHelper(staticValues.serverNick, isChiefTurn?'nation':'general', staticValues.mapName, staticValues.unitSet);

        const response = await responseP;
        storedActionsHelper.pushRecentActions({
            action,
            brief: response.brief,
            arg: (arg??{}),
        })

        if (!isChiefTurn) {
            window.location.href = './';
        } else {
            window.location.href = 'v_chiefCenter.php';
        }

        return response;
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
    //NOTE: route??? ???????
    const groupName = entryInfo[0];
    if (groupName == 'General') {
        const moduleName = entryInfo[1];
        if (!(moduleName in GeneralActions)) {
            console.error(`${moduleName}??? ${groupName}??? ??????`);
            return undefined;
        }
        return createApp(GeneralActions[moduleName]);
    }
    if (groupName == 'Nation') {
        const moduleName = entryInfo[1];
        if (!(moduleName in NationActions)) {
            console.error(`${moduleName}??? ${groupName}??? ??????`);
            return undefined;
        }
        return createApp(NationActions[moduleName]);
    }

    console.error('??????')
    return undefined;
}());

if (app === undefined) {
    const div = document.createElement('div');
    div.innerHTML = `????????? view??? ????????????. ${JSON.stringify(entryInfo)}`;
    document.body.appendChild(div);
    console.error(`????????? ???????????? ??????`, entryInfo);
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

htmlReady(() => {
    insertCustomCSS();
  });