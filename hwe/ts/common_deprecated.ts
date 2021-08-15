import { activeFlip, escapeHtml, isInt, mb_strwidth, mb_strimwidth, convertDictById, convertSet, hexToRgb, isBrightColor, convColorValue, numberWithCommas, linkifyStrWithOpt, TemplateEngine, getIconPath, combineObject, combineArray, activeFlipItem, errUnknown, errUnknownToast, quickReject, nl2br, getNpcColor, initTooltip } from "./common_legacy";
import jQuery from "jquery";



jQuery(function ($) {
    initTooltip();
    activeFlip();

    const customCSS = localStorage.getItem('sam_customCSS');
    if (customCSS) {
        const $style = $('<style type="text/css"></style>');
        $style.text(customCSS);
        $style.appendTo($('head'));
    }
});

declare global {
    interface Window {
        $: typeof jQuery;
        jQuery: typeof jQuery;
        /** @deprecated Module 사용할 것 */
        escapeHtml: typeof escapeHtml;
        /** @deprecated Module 사용할 것 */
        isInt: typeof isInt;
        /** @deprecated Module 사용할 것 */
        mb_strwidth: typeof mb_strwidth;
        /** @deprecated Module 사용할 것 */
        mb_strimwidth: typeof mb_strimwidth;
        /** @deprecated Module 사용할 것 */
        convertDictById: typeof convertDictById;
        /** @deprecated Module 사용할 것 */
        convertSet: typeof convertSet;
        /** @deprecated Module 사용할 것 */
        hexToRgb: typeof hexToRgb;
        /** @deprecated Module 사용할 것 */
        isBrightColor: typeof isBrightColor;
        /** @deprecated Module 사용할 것 */
        convColorValue: typeof convColorValue;
        /** @deprecated Module 사용할 것 */
        numberWithCommas: typeof numberWithCommas;
        /** @deprecated Module 사용할 것 */
        linkifyStrWithOpt: typeof linkifyStrWithOpt;
        /** @deprecated Module 사용할 것 */
        TemplateEngine: typeof TemplateEngine;
        /** @deprecated Module 사용할 것 */
        getIconPath: typeof getIconPath;
        /** @deprecated Module 사용할 것 */
        activeFlip: typeof activeFlip;
        /** @deprecated Module 사용할 것 */
        combineObject: typeof combineObject;
        /** @deprecated Module 사용할 것 */
        combineArray: typeof combineArray;
        /** @deprecated Module 사용할 것 */
        activeFlipItem: typeof activeFlipItem;
        /** @deprecated Module 사용할 것 */
        errUnknown: typeof errUnknown;
        /** @deprecated Module 사용할 것 */
        errUnknownToast: typeof errUnknownToast;
        /** @deprecated Module 사용할 것 */
        quickReject: typeof quickReject;
        /** @deprecated Module 사용할 것 */
        nl2br: typeof nl2br;
        /** @deprecated Module 사용할 것 */
        getNpcColor: typeof getNpcColor;
        /** @deprecated Module 사용할 것 */
        initTooltip: typeof initTooltip;
    }
}

window.$ = jQuery;
window.jQuery = jQuery;

window.escapeHtml = escapeHtml;
window.isInt = isInt;
window.mb_strwidth = mb_strwidth;
window.mb_strimwidth = mb_strimwidth;
window.convertDictById = convertDictById;
window.convertSet = convertSet;
window.hexToRgb = hexToRgb;
window.isBrightColor = isBrightColor;
window.convColorValue = convColorValue;
window.numberWithCommas = numberWithCommas;
window.linkifyStrWithOpt = linkifyStrWithOpt;
window.TemplateEngine = TemplateEngine;
window.getIconPath = getIconPath;
window.activeFlip = activeFlip;
window.combineObject = combineObject;
window.combineArray = combineArray;
window.activeFlipItem = activeFlipItem;
window.errUnknown = errUnknown;
window.errUnknownToast = errUnknownToast;
window.quickReject = quickReject;
window.nl2br = nl2br;
window.getNpcColor = getNpcColor;
window.initTooltip = initTooltip;