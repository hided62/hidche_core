import $ from 'jquery';
import Popper from 'popper.js';
(window as unknown as {Popper:unknown}).Popper = Popper;//XXX: 왜 popper를 이렇게 불러야 하는가?
import 'bootstrap';
import { reloadWorldMap } from './map';

declare global{
    interface Window{
        fitIframe:()=>void;
    }
}

$(function($){
    void reloadWorldMap({
        targetJson: "j_map_recent.php",
        reqType: 'get',
        dynamicMapTheme: true,
        callback: function(data, rawObject) {
            const historyRaw = rawObject as unknown as {
                history: string
            };
            $('.card-body').html(historyRaw.history);

            if(window.parent !== window){
                setTimeout(()=>{
                    window.parent.fitIframe();
                }, 1);

            }
        }
    });
});