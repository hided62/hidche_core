import jQuery from 'jquery';
import { activateFlip } from "@/legacy/activateFlip";
import { unwrap } from '@util/unwrap';
import { htmlReady } from '@util/htmlReady';
import { initTooltip } from './initTooltip';
import { exportWindow } from '@/util/exportWindow';

import '@/msg.ts';
import '@/map.ts';

exportWindow(jQuery, '$');


htmlReady(() => {
    document.querySelector('.refreshPage')?.addEventListener('click', function () {
        document.location.reload();
        return false;
    });

    document.querySelector('.open-window')?.addEventListener('click', function (e) {
        e.preventDefault();
        let target: HTMLElement | null = e.target as HTMLElement;
        while (target !== null) {
            target = target.parentElement;
            if (target === null) {
                return;
            }
            if (target.tagName != 'a') {
                continue;
            }
            if ((target as HTMLAnchorElement).href !== undefined) {
                break;
            }
        }

        if (!target) {
            return;
        }

        window.open((target as HTMLAnchorElement).href);
    });

    activateFlip();
    initTooltip();

    const customCSS = localStorage.getItem('sam_customCSS');
    if (customCSS) {
        const styleEl = document.createElement('style');
        styleEl.innerHTML = customCSS;
        document.head.appendChild(styleEl);
    }
});

(() => {

    let finInit = false;

    let nationMsgBox!: HTMLElement;
    let nationMsg!: HTMLElement;
    let nationMsgHeight: number | undefined = undefined;

    function init() {
        if (finInit) {
            return false;
        }
        const _nationMsgBox = document.getElementById('nation-msg-box');
        if (!_nationMsgBox) {
            return false;
        }
        finInit = true;

        nationMsgBox = _nationMsgBox;
        nationMsg = unwrap(document.getElementById('nation-msg'));
    }

    function onScroll() {
        if (!finInit && !init()) return;

        if (nationMsgBox.offsetWidth < nationMsg.offsetWidth) {
            if (nationMsgHeight === undefined) {
                nationMsgHeight = nationMsgBox.offsetHeight;
                nationMsgBox.style.height = `${nationMsgHeight / 2}px`;
            }
        }
        else {
            if (nationMsgBox.style.height) {
                nationMsgHeight = undefined;
                nationMsgBox.style.height = '';
            }
        }
    }

    htmlReady(() => {
        init();
        onScroll();
        window.addEventListener('scroll', onScroll, true);
        window.addEventListener('orientationchange', onScroll, true);
    });
})();
