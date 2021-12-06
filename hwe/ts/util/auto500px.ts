import { htmlReady } from "./htmlReady";
import { unwrap } from "./unwrap";

export function auto500px(targetHeight = 700): void {
    let deviceWidth = -1;
    let viewportMeta!: HTMLMetaElement;


    function init() {
        let _viewPortMeta = document.querySelector<HTMLMetaElement>("meta[name=viewport]");
        if (_viewPortMeta) {
            viewportMeta = _viewPortMeta;
            return;
        }

        const htmlTag = unwrap(document.querySelector("head"));
        _viewPortMeta = document.createElement("meta");
        _viewPortMeta.name = 'viewport';
        _viewPortMeta.content = 'width=device-width, initial-scale=0.72, maximum-scale=5.0, minimum-scale=0.72';
        htmlTag.appendChild(_viewPortMeta);
        viewportMeta = _viewPortMeta;
    }

    function adjustViewportWidth() {
        if (deviceWidth == window.screen.availWidth) {
            return;
        }
        deviceWidth = window.screen.availWidth;
        const innerHeight = window.innerHeight;
        const selectorHeight = targetHeight;

        if (deviceWidth < 500) {
            viewportMeta.content = 'width=device-width, initial-scale=0.72, maximum-scale=5.0, minimum-scale=0.72';
            return;
        }

        if (innerHeight < selectorHeight) {
            const maybeNextWidth = deviceWidth / innerHeight * selectorHeight;
            if (maybeNextWidth >= 750) {
                viewportMeta.content = 'width=1000, initial-scale=1';
            }
            else {
                viewportMeta.content = `height=${Math.ceil(selectorHeight)}, initial-scale=1`;
            }
            return;
        }
    }

    htmlReady(() => {
        init();
        adjustViewportWidth();
        window.addEventListener('scroll', adjustViewportWidth, true);
        window.addEventListener('orientationchange', adjustViewportWidth, true);
    });
}