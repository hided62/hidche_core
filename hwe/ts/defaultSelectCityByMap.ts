import { reloadWorldMap } from "./map";
import { unwrap_any } from "./util/unwrap_any";
/*import $ from 'jquery';
import 'select2';*///TODO: processing

$(function() {
    const $target = $("#destCityID");
    console.log('target', $target);
    void reloadWorldMap({
        isDetailMap: false,
        clickableAll: true,
        neutralView: true,
        useCachedMap: true,
        selectCallback: function(city) {
            const currVal = unwrap_any<string>($target.val());
            $target.val(city.id).trigger("change");
            if ($target.val() === null) {
                $target.val(currVal).trigger("change");
            }
            return false;
        }
    });
});