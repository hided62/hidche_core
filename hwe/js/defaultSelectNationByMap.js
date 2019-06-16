$(function(){
    var $target = $("#destNationID");
    reloadWorldMap({
        isDetailMap:false,
        clickableAll:true,
        neutralView:true,
        useCachedMap:true,
        selectCallback:function(city){
            $target.val(city.nationId);
            return false;
        }
    });
});