import "../scss/inheritPoint.scss";

import { sum } from "lodash";
import { unwrap } from "./util";
declare global {
    interface Window { 
        formStart: ()=>void;
        items: {[name: string]:number};
        helpText: {[name: string]:string};
    }
}

export {}

function formStart() {

    const dSum = unwrap(document.querySelector('#inherit_sum_value')) as HTMLInputElement ;
    const dOld = unwrap(document.querySelector('#inherit_previous_value')) as HTMLInputElement;
    const dNew = unwrap(document.querySelector('#inherit_new_value')) as HTMLInputElement;

    const sumPoint = Math.floor(sum(Object.values(window.items)));
    const oldPoint = Math.floor(window.items['previous']);
    const sumNewPoint = sumPoint - oldPoint;

    dSum.value = sumPoint.toLocaleString();
    dOld.value = oldPoint.toLocaleString();
    dNew.value = sumNewPoint.toLocaleString();

    for(const [key, val] of Object.entries(window.items)){
        const dItem = unwrap(document.querySelector(`#inherit_${key}_value`)) as HTMLInputElement ;
        dItem.value = Math.floor(val).toLocaleString();
    }

    for(const [key, text] of Object.entries(window.helpText)){
        const dText = unwrap(document.querySelector(`#inherit_${key} small.form-text`)) as HTMLElement;
        dText.innerHTML = text;
    }
}

formStart();