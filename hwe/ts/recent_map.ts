import { reloadWorldMap } from '@/map';
import { htmlReady } from './util/htmlReady';
import { unwrap } from './util/unwrap';

declare global {
    interface Window {
        fitIframe: () => void;
    }
}
htmlReady(function () {
    console.log('haha');
    void reloadWorldMap({
        targetJson: "j_map_recent.php",
        reqType: 'get',
        dynamicMapTheme: true,
        callback: function (data, rawObject) {
            const historyRaw = rawObject as unknown as {
                history: string
            };
            unwrap(document.querySelector('.card-body')).innerHTML = historyRaw.history;

            if (window.parent !== window) {
                setTimeout(() => {
                    window.parent.fitIframe();
                }, 1);

            }
        }
    });
})