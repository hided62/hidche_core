import $ from 'jquery';
import Popper from 'popper.js';
exportWindow(Popper, 'Popper');//XXX: 왜 popper를 이렇게 불러야 하는가?
import 'bootstrap';
import { activateFlip, initTooltip } from './common_legacy';
import './msg.ts';
import './map.ts';
import { exportWindow } from './util/exportWindow';


exportWindow($, '$');

import '../scss/main.scss';
import { unwrap } from './util/unwrap';
import { number } from 'vue-types';

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

function ready(fn: () => unknown) {
    if (document.readyState != 'loading') {
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

(() => {

    let finInit = false;
    const objects: {
        target: HTMLElement,
        button: HTMLAnchorElement,
    }[] = [];

    let nationMsgBox!: HTMLElement;
    let nationMsg!: HTMLElement;
    let nationMsgHeight : number|undefined = undefined;

    let commandSelector!: HTMLElement;
    let deviceWidth = -1;
    let viewportMeta! : HTMLMetaElement;

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

        commandSelector = unwrap(document.getElementById('reservedCommandList'));
        viewportMeta = unwrap(document.querySelector<HTMLMetaElement>("meta[name=viewport]"));
    }

    function adjustViewportWidth() {
        if(deviceWidth == window.screen.availWidth){
            return;
        }
        deviceWidth = window.screen.availWidth;
        const innerHeight = window.innerHeight;
        const selectorHeight = commandSelector.offsetHeight*1.1;

        if(deviceWidth < 500){
            viewportMeta.content = 'width=500, initial-scale=1';
            console.log(`2`);
            return;
        }

        if(innerHeight < selectorHeight){
            const maybeNextWidth = deviceWidth / innerHeight * selectorHeight;
            if(maybeNextWidth >= 750){
                viewportMeta.content = 'width=1000, initial-scale=1';
            }
            else{
                viewportMeta.content = `height=${Math.ceil(selectorHeight)}, initial-scale=1`;
            }
            return;
        }
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

        if(nationMsgBox.offsetWidth < nationMsg.offsetWidth){
            if(nationMsgHeight === undefined){
                nationMsgHeight = nationMsgBox.offsetHeight;
                nationMsgBox.style.height = `${nationMsgHeight / 2}px`;
            }
        }
        else{
            if(nationMsgBox.style.height){
                nationMsgHeight = undefined;
                nationMsgBox.style.height = '';
            }
        }

        adjustViewportWidth();
    }

    ready(() => {
        init();
        onScroll();
        window.addEventListener('scroll', onScroll, true);
        window.addEventListener('orientationchange', onScroll, true);
    });
})();