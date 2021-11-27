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

$(function ($) {
    $('#refreshPage').click(function () {
        document.location.reload();
        return false;
    });

    $('.open-window').on('click', function(e){
        e.preventDefault();
        let target = $(e.target as HTMLAnchorElement);
        while(target.attr('href') === undefined){
            target = target.parent('a');
            if(target.length == 0){
                return;
            }
        }
        const href = target.attr('href');
        window.open(href);
    });

    activateFlip();
    initTooltip();
});
