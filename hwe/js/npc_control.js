jQuery(function($){

function initPriority(priorityKey, currentPriority, availablePriority){
    var $disabledList = $('#{0}Disabled'.format(priorityKey));
    var $enabledList = $('#{0}'.format(priorityKey));

    $disabledList.empty();
    $enabledList.empty();

    var usedKey = {};
    var itemFrame = '<div class="list-group-item" data-value="{0}">{0}</div>';
    $.each(currentPriority, function(key, val){
        var $item = $(itemFrame.format(val));
        usedKey[val] = true;
        $enabledList.append($item);
    })
    
    var $disabled = $(itemFrame.format('&lt;비활성화 항목들&gt;')).addClass('filtered');
    $disabledList.append($disabled);

    $.each(availablePriority, function(key, val){
        if(val in usedKey){
            return true;
        }
        var $item = $(itemFrame.format(val));
        $disabledList.append($item);
    })

    $disabledList.sortable({
        group: priorityKey,
        filter: '.filtered',
        animation: 150
    });

    $enabledList.sortable({
        group: priorityKey,
        filter: '.filtered',
        animation: 150
    });
}

initPriority('generalPriority', currentGeneralActionPriority, availableGeneralActionPriorityItems);
initPriority('nationPriority', currentNationPriority, availableNationPriorityItems);


$.each(currentNationPolicy, function(key, val){
    var $obj = $('#{0}'.format(key));

    if(!$obj.length){
        console.log('{0}가 없대!'.format(key));
        return true;
    }

    var type=$obj.data('type');
    if(!$obj.is('input')){
        console.log('아니라고?');
        return true;
    }

    if(type =='percent'){
        $obj.val(val*100);
    }
    else{
        $obj.val(val);
    }
    
});

});