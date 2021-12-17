import { reloadWorldMap } from "@/map";

declare let vueReactive_destNationID: number|undefined;

export function defaultSelectNationByMap(): void{
    const $target = $("#destNationID");
    console.log('nation', $target);
    void reloadWorldMap({
        isDetailMap: false,
        clickableAll: true,
        neutralView: true,
        useCachedMap: true,
        selectCallback: function (city) {
            if(typeof vueReactive_destNationID === 'undefined'){
                console.error('아직 초기화 되지 않음');
                return false;
            }
            vueReactive_destNationID = city.nationID;
            return false;
        }
    });
}