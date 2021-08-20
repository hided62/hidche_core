import { reloadWorldMap } from "./map";
import { unwrap_any } from "./util/unwrap_any";
/*import $ from 'jquery';
import 'select2';*///TODO: processing

export function loadPlugin(): void{
    const $target = $("#destNationID");
    void reloadWorldMap({
        isDetailMap: false,
        clickableAll: true,
        neutralView: true,
        useCachedMap: true,
        selectCallback: (city)=>{
            const currVal = unwrap_any<string>($target.val());
            if(!city.nationID){
                return false;
            }
            $target.val(city.nationID).trigger("change");
            if ($target.val() === null) {
                $target.val(currVal).trigger("change");
            }
            return false;
        }
    });
}

$(function() {
    loadPlugin();
});