$(function(){
    var $target = $("#destCityID");
    reloadWorldMap({
        isDetailMap:false,
        clickableAll:true,
        neutralView:true,
        useCachedMap:true,
        selectCallback:function(city){
            var currVal = $target.val();
            $target.val(city.id);
            if($target.val() === null){
                $target.val(currVal);
            }
            return false;
        }
    });
});