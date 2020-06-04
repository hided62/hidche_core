$(function() {
    var $target = $("#destNationID");
    reloadWorldMap({
        isDetailMap: false,
        clickableAll: true,
        neutralView: true,
        useCachedMap: true,
        selectCallback: function(city) {
            var currVal = $target.val();
            $target.val(city.nationId).trigger("change");
            if ($target.val() === null) {
                $target.val(currVal).trigger("change");
            }
            return false;
        }
    });
});