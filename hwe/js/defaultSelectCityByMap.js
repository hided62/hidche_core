$(function(){
    var $target = $("#destCityID");
    reloadWorldMap({
        isDetailMap:false,
        clickableAll:true,
        neutralView:true,
        useCachedMap:true,
        selectCallback:function(city){
            $target.val(city.id);
            return false;
        }
    });
});