import { exportWindow } from "@util/exportWindow";
import { activateFlip, errUnknown, initTooltip } from "@/common_legacy";
import { isBrightColor } from "@util/isBrightColor";
import { getIconPath } from "@util/getIconPath";
import { mb_strwidth } from "@util/mb_strwidth";
import { TemplateEngine } from "@util/TemplateEngine";
import { escapeHtml } from "@/legacy/escapeHtml";
import { nl2br } from "@util/nl2br";
import jQuery from "jquery";
import 'bootstrap';

import "@scss/common_legacy.scss";

exportWindow(jQuery, '$');
exportWindow(jQuery, 'jQuery');

jQuery(function ($) {
    initTooltip();
    activateFlip();

    const customCSS = localStorage.getItem('sam_customCSS');
    if (customCSS) {
        const $style = $('<style type="text/css"></style>');
        $style.text(customCSS);
        $style.appendTo($('head'));
    }
});

/**
 * {0}, {1}, {2}형태로 포맷해주는 함수
 */
exportWindow(function(this:string, ...args:(string|number)[]){
    return this.replace(/{(\d+)}/g, function (match, number) {
        return (typeof args[number] != 'undefined') ? args[number].toString() : match;
    });
}, 'format', String.prototype);
exportWindow(escapeHtml, 'escapeHtml');
exportWindow(mb_strwidth, 'mb_strwidth');
exportWindow(isBrightColor, 'isBrightColor');
exportWindow(TemplateEngine, 'TemplateEngine');
exportWindow(getIconPath, 'getIconPath');
exportWindow(activateFlip, 'activateFlip');
exportWindow(errUnknown, 'errUnknown');
exportWindow(nl2br, 'nl2br');
exportWindow(initTooltip, 'initTooltip');