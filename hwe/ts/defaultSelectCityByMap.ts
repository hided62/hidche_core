import { reloadWorldMap } from "./map";
import { unwrap_any } from "./util/unwrap_any";

export function defaultSelectCityByMap(): void {
    const $target = $("#destCityID");
    console.log('city', $target);
    void reloadWorldMap({
        isDetailMap: false,
        clickableAll: true,
        neutralView: true,
        useCachedMap: true,
        selectCallback: function (city) {
            const currVal = unwrap_any<string>($target.val());
            $target.val(city.id);
            $target.trigger("change");
            if ($target.val() === null) {
                $target.val(currVal).trigger("change").blur();
            }
            return false;
        }
    });
}
