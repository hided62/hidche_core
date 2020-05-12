jQuery(function ($) {

    function initPriority(priorityKey, currentPriority, availablePriority) {
        var $disabledList = $('#{0}Disabled'.format(priorityKey));
        var $enabledList = $('#{0}'.format(priorityKey));

        $disabledList.empty();
        $enabledList.empty();

        var usedKey = {};
        var itemFrame = '<div class="list-group-item" data-value="{0}">{0}</div>';
        $.each(currentPriority, function (key, val) {
            var $item = $(itemFrame.format(val));
            usedKey[val] = true;
            $enabledList.append($item);
        })

        var $disabled = $(itemFrame.format('&lt;비활성화 항목들&gt;')).addClass('filtered');
        $disabledList.append($disabled);

        $.each(availablePriority, function (key, val) {
            if (val in usedKey) {
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



    function initNationPolicy() {
        $.each(currentNationPolicy, function (key, val) {
            var $obj = $('#{0}'.format(key));

            if (!$obj.length) {
                console.log('{0}가 없대!'.format(key));
                return true;
            }

            var type = $obj.data('type');
            if (!$obj.is('input')) {
                console.log('아니라고?');
                return true;
            }

            if (type == 'percent') {
                $obj.val(val * 100);
            }
            else if (type == 'json') {
                $obj.val(JSON.stringify(val));
            }
            else {
                $obj.val(val);
            }
        });
    }


    initPriority('generalPriority', currentGeneralActionPriority, availableGeneralActionPriorityItems);
    initPriority('nationPriority', currentNationPriority, availableNationPriorityItems);
    initNationPolicy();


    $('.reset_btn').click(function () {
        var $this = $(this);

        if (!confirm('서버 초기 설정으로 되돌릴까요?')) {
            return false;
        }

        var type = $this.parents('.control_bar').data('type');
        if (type === 'nationPolicy') {
            window.currentNationPolicy = window.defaultNationPolicy;
            initNationPolicy();
        }
        else if (type === 'generalPriority') {
            window.currentGeneralActionPriority = window.defaultGeneralActionPriority;
            initPriority('generalPriority', currentGeneralActionPriority, availableGeneralActionPriorityItems);
        }
        else if (type === 'nationPriority') {
            window.currentNationPriority = window.defaultNationPriority;
            initPriority('nationPriority', currentNationPriority, availableNationPriorityItems);
        }

        $.toast({
            title: '초기화 완료',
            content: '서버 초기값을 적용했습니다. 설정 버튼을 누르면 반영됩니다.',
            type: 'info',
            delay: 5000
        });
        return false;
    });

    $('.revert_btn').click(function () {
        var $this = $(this);

        if (!confirm('이전 설정으로 되돌릴까요?')) {
            return false;
        }

        var type = $this.parents('.control_bar').data('type');
        if (type === 'nationPolicy') {
            initNationPolicy();
        }

        if (type === 'generalPriority') {
            initPriority('generalPriority', currentGeneralActionPriority, availableGeneralActionPriorityItems);
        }

        if (type === 'nationPriority') {
            initPriority('nationPriority', currentNationPriority, availableNationPriorityItems);
        }

        $.toast({
            title: '되돌리기 완료',
            content: '이전 설정으로 되돌렸습니다.',
            type: 'info',
            delay: 5000
        });
    });

    var collectPriority = function (priorityKey) {
        var $list = $('#{0}'.format(priorityKey));
        var result = [];
        $list.find('.list-group-item').each(function(){
            var $item = $(this);
            result.push($item.data('value'));
        });
        return result;
    }

    var collectNationPolicy = function () {
        var src = window.currentNationPolicy;
        var result = {};
        $.each(src, function (key, val) {
            var $item = $('#{0}'.format(key));
            var val = $item.val();
            var dataType = $item.data('type');
            if (dataType === 'percent') {
                val /= 100;
            }
            else if (dataType === 'json') {
                val = JSON.parse(val);
            }
            else if (dataType === 'integer') {
                val = parseInt(val);
            }
            result[key] = val;
        });
        return result;
    }
    window.collectNationPolicy = collectNationPolicy;

    $('.submit_btn').click(function () {
        var $this = $(this);
        if (!confirm('저장할까요?')) {
            return false;
        }

        var type = $this.parents('.control_bar').data('type');
        var data;
        if (type === 'nationPolicy') {
            data = collectNationPolicy();
        }
        else if (type === 'nationPriority') {
            data = collectPriority(type);
        }
        else if (type === 'generalPriority') {
            data = collectPriority(type);
        }
        else {
            alert('올바르지 않은 type : {0}'.type);
            return false;
        }
        console.log(data);

        $.post({
            url: 'j_set_npc_control.php',
            dataType: 'json',
            data: {
                type: type,
                data: JSON.stringify(data)
            }
        }).then(function (data) {
            if (!data) {
                return quickReject('설정하지 못했습니다.');
            }
            if (!data.result) {
                return quickReject('설정하지 못했습니다. : ' + data.reason);
            }
            $.toast({
                title: '적용 완료',
                content: 'NPC 정책이 반영되었습니다.',
                type: 'success',
                delay: 5000
            });
        }, errUnknownToast)
            .fail(function (reason) {
                $.toast({
                    title: '에러!',
                    content: reason,
                    type: 'danger',
                    delay: 5000
                });
            });
    })

});