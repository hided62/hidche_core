import $ from 'jquery';
import Popper from 'popper.js';
exportWindow(Popper, 'Popper');//XXX: 왜 popper를 이렇게 불러야 하는가?
import 'bootstrap';
import { activateFlip, initTooltip } from './common_legacy';
import './msg.ts';
import './map.ts';
import { exportWindow } from './util/exportWindow';

import { scrollHardTo } from './util/scrollHardTo';

exportWindow(scrollHardTo, 'scrollHardTo');
exportWindow($, '$');

import '../scss/main.scss';
import { unwrap } from './util/unwrap';
import { auto500px } from './util/auto500px';
import { htmlReady } from './util/htmlReady';

$(function ($) {
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
});

(() => {

    let finInit = false;
    const objects: {
        target: HTMLElement,
        button: HTMLAnchorElement,
    }[] = [];

    let nationMsgBox!: HTMLElement;
    let nationMsg!: HTMLElement;
    let nationMsgHeight: number | undefined = undefined;

    function init() {
        const buttons = document.querySelectorAll<HTMLAnchorElement>('#float-tabs a.btn');
        if (!buttons) {
            return false;
        }
        finInit = true;

        for (const button of buttons) {
            const targetQuery = button.href.split('#');
            if (!targetQuery || targetQuery.length < 2) {
                continue;
            }
            const target = document.getElementById(targetQuery[1]);
            if (!target) {
                continue;
            }
            objects.push({ target, button });
        }

        nationMsgBox = unwrap(document.getElementById('nation-msg-box'));
        nationMsg = unwrap(document.getElementById('nation-msg'));
    }

    function onScroll() {
        if (!finInit && !init()) return;

        for (const { button } of objects) {
            button.classList.remove('active');
        }

        const screenHeight = window.innerHeight
        for (const { target, button } of objects) {
            const { top, bottom, height } = target.getBoundingClientRect();

            if (top >= 0 && bottom <= screenHeight) {
                //valid
            }
            else if (top <= 0 && bottom >= screenHeight) {
                //valid
            }
            else if (top < 0) {
                if (bottom / height < 0.8) {
                    continue;
                }
            }
            else if (bottom > screenHeight) {
                if ((screenHeight - top) / height < 0.8) {
                    continue;
                }
            }

            button.classList.add('active');
        }

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

auto500px();