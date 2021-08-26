import { activeFlip, isBrightColor, getIconPath, errUnknown, errUnknownToast, quickReject, initTooltip } from "./common_legacy";
import { mb_strwidth } from "./util/mb_strwidth";
import { TemplateEngine } from "./util/TemplateEngine";
import { escapeHtml } from "./legacy/escapeHtml";
import { nl2br } from "./util/nl2br";
import jQuery from "jquery";
window.jQuery = jQuery;
window.$ = jQuery;

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
        mb_strwidth: typeof mb_strwidth;
        /** @deprecated Module 사용할 것 */
        isBrightColor: typeof isBrightColor;
        /** @deprecated Module 사용할 것 */
        TemplateEngine: typeof TemplateEngine;
        /** @deprecated Module 사용할 것 */
        getIconPath: typeof getIconPath;
        /** @deprecated Module 사용할 것 */
        activeFlip: typeof activeFlip;
        /** @deprecated Module 사용할 것 */
        errUnknown: typeof errUnknown;
        /** @deprecated Module 사용할 것 */
        errUnknownToast: typeof errUnknownToast;
        /** @deprecated Module 사용할 것 */
        quickReject: typeof quickReject;
        /** @deprecated Module 사용할 것 */
        nl2br: typeof nl2br;
        /** @deprecated Module 사용할 것 */
        initTooltip: typeof initTooltip;
    }
}

window.escapeHtml = escapeHtml;
window.mb_strwidth = mb_strwidth;
window.isBrightColor = isBrightColor;
window.TemplateEngine = TemplateEngine;
window.getIconPath = getIconPath;
window.activeFlip = activeFlip;
window.errUnknown = errUnknown;
window.errUnknownToast = errUnknownToast;
window.quickReject = quickReject;
window.nl2br = nl2br;
window.initTooltip = initTooltip;