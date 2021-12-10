import $ from 'jquery';
import { activateFlip, initTooltip } from '@/common_legacy';
import '@/msg.ts';
import '@/map.ts';

import '@scss/main.scss';
import { unwrap } from '@util/unwrap';
import { htmlReady } from '@util/htmlReady';

htmlReady(() => {
    $('.refreshPage').click(function () {
        document.location.reload();
        return false;
    });

    $('.open-window').on('click', function (e) {
        e.preventDefault();
        let target = $(e.target as HTMLAnchorElement);
        while (target.attr('href') === undefined) {
            target = target.parent('a');
            if (target.length == 0) {
                return;
            }
        }
        const href = target.attr('href');
        window.open(href);
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
