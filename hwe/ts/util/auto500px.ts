import { htmlReady } from "@util/htmlReady";
import { unwrap } from "@util/unwrap";
import { keyScreenMode, ScreenModeType } from "@/defs";

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
        _viewPortMeta.content = 'width=500';
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

        const screenMode = (localStorage.getItem(keyScreenMode) as ScreenModeType)??'auto';
        if(screenMode == '500px'){
            viewportMeta.content = 'width=500';
            return;
        }

        if(screenMode == '1000px'){
            viewportMeta.content = 'width=1000';
            return;
        }

        if (deviceWidth < 500) {
            viewportMeta.content = 'width=500';
            return;
        }

        if (innerHeight < selectorHeight) {
            const maybeNextWidth = deviceWidth / innerHeight * selectorHeight;
            if (maybeNextWidth >= 700) {
                viewportMeta.content = 'width=1000';
            }
            else {
                viewportMeta.content = `height=${Math.ceil(selectorHeight)}`;
            }
            return;
        }
        else if(deviceWidth >= 700){
            viewportMeta.content = 'width=1000';
        }
        else{
            viewportMeta.content = 'width=device-width, initial-scale=1';
        }
    }

    htmlReady(() => {
        init();
        adjustViewportWidth();
        window.addEventListener('scroll', adjustViewportWidth, true);
        window.addEventListener('orientationchange', adjustViewportWidth, true);
    });
}