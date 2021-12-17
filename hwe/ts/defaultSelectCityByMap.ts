import { reloadWorldMap } from "@/map";

declare let vueReactive_destCityID: number|undefined;

export function defaultSelectCityByMap(): void {
    void reloadWorldMap({
        isDetailMap: false,
        clickableAll: true,
        neutralView: true,
        useCachedMap: true,
        selectCallback: function (city) {
            if(vueReactive_destCityID === undefined){
                console.error('아직 초기화 되지 않음');
                return false;
            }
            vueReactive_destCityID = city.id;
            return false;
        }
    });
}
