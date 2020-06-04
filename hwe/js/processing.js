function reserveTurn(turnList, command, arg) {
    var target;
    if (isChiefTurn) {
        target = 'j_set_chief_command.php';
    } else {
        target = 'j_set_general_command.php';
    }
    $.post({
        url: target,
        dataType: 'json',
        data: {
            action: command,
            turnList: turnList,
            arg: JSON.stringify(arg)
        }
    }).then(function(data) {
        if (!data.result) {
            alert(data.reason);
            return;
        }

        if (!isChiefTurn) {
            window.location.href = './';
        } else {
            window.location.href = 'b_chiefcenter.php';
        }

    }, errUnknown);
}

jQuery(function($) {

    window.submitAction = function() {

        //checkCommandArg 참고
        var availableArgumentList = {
            'string': [
                'nationName', 'optionText', 'itemType', 'nationType', 'itemCode',
            ],
            'int': [
                'crewType', 'destGeneralID', 'destCityID', 'destNationID',
                'amount', 'colorType',
                'year', 'month',
                'srcArmType', 'destArmType', //숙련전환 전용
            ],
            'boolean': [
                'isGold', 'buyRice',
            ],
            'integerArray': [
                'destNationIDList', 'destGeneralIDList', 'amountList'
            ]
        }

        var handlerList = {
            'string': function($obj) {
                return $.trim($obj.eq(0).val());
            },
            'int': function($obj) {
                return parseInt($obj.eq(0).val());
            },
            'boolean': function($obj) {
                switch ($obj.eq(0).val().toLowerCase()) {
                    case "true":
                    case "yes":
                    case "1":
                        return true;
                    case "false":
                    case "no":
                    case "0":
                        return false;
                    default:
                        throw new Error("Boolean.parse: Cannot convert string to boolean.");
                }
            },
            'integerArray': function($obj) {
                return $obj.map(function() {
                    return parseInt($(this).val());
                });
            }
        }

        var argument = {};
        for (var typeName in availableArgumentList) {
            availableArgumentList[typeName].forEach(function(argName) {
                var $obj = $('#' + argName);
                if ($obj.length == 0) {
                    $obj = $('.' + argName);
                    if ($obj.length == 0) {
                        return;
                    }
                }

                argument[argName] = handlerList[typeName]($obj);
            });
        }

        console.log(argument);
        reserveTurn(turnList, command, argument);
    };

    $('#commonSubmit').click(submitAction);

    var $colorType = $('#colorType');
    if ($colorType.length) {
        $colorType.select2({
            theme: 'bootstrap4',
            placeholder: "색상을 선택해 주세요.",
            language: "ko",
            containerCss: {
                display: "inline-block !important",
                color: 'white !important',
            },
            templateSelection: function(item) {
                if (item.disabled) {
                    return item.text;
                }
                var bgcolor = item.element.dataset.color;
                var fgcolor = item.element.dataset.fontColor;
                return $("<span><span style='background-color:{0};color:{1};'>　</span>&nbsp;{2}</span>".format(
                    bgcolor, fgcolor, item.text
                ));
            },
            templateResult: function(item) {
                if (item.disabled) {
                    return item.text;
                }
                var bgcolor = item.element.dataset.color;
                var fgcolor = item.element.dataset.fontColor;
                return $("<div style='padding: 0.75rem 0.375rem; background-color:{0};color:{1};'>{2}</div>".format(
                    bgcolor, fgcolor, item.text
                ));
            },
            containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
            dropdownCssClass: 'no-padding simple-select2-align-center bg-secondary text-secondary',
        });
    }

    var $nationType = $('#nationType');
    if ($nationType.length) {
        $nationType.select2({
            theme: 'bootstrap4',
            language: "ko",
            containerCss: {
                display: "inline-block !important",
                color: 'white !important',
            },
            containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
            dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        });
    }

    var $destCityID = $('#destCityID');
    if ($destCityID.length) {
        $destCityID.select2({
            theme: 'bootstrap4',
            placeholder: "도시를 선택해 주세요.",
            language: "ko",
            containerCss: {
                display: "inline-block !important",
                color: 'white !important',
            },
            containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
            dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        });
    }

    var $destNationID = $('#destNationID');
    if ($destNationID.length) {
        $destNationID.select2({
            theme: 'bootstrap4',
            placeholder: "국가를 선택해 주세요.",
            language: "ko",
            containerCss: {
                display: "inline-block !important",
                color: 'white !important',
            },
            containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
            dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        });
    }

    var $destGeneralID = $('#destGeneralID');
    if ($destGeneralID.length) {
        $destGeneralID.select2({
            theme: 'bootstrap4',
            placeholder: "장수를 선택해 주세요.",
            language: "ko",
            containerCss: {
                display: "inline-block !important",
                color: 'white !important',
            },
            containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
            dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        });
    }

    var $isGold = $('#isGold');
    if ($isGold.length) {
        $isGold.select2({
            theme: 'bootstrap4',
            placeholder: "분량을 지정해 주세요.",
            language: "ko",
            containerCss: {
                display: "inline-block !important",
                color: 'white !important',
            },
            minimumResultsForSearch: -1,
            containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
            dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        });
    }

    var $amount = $('#amount:not([type=hidden])');
    if ($amount.length) {
        $amount.select2({
            theme: 'bootstrap4',
            placeholder: "분량을 지정해 주세요.",
            allowClear: false,
            language: "ko",
            containerCss: {
                display: "inline-block !important",
                color: 'white !important',
            },
            tags: true,
            sorter: function(items) {
                items.sort(function(lhs, rhs) {
                    return parseInt(lhs.id) - parseInt(rhs.id);
                })
                return items;
            },
            containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
            dropdownCssClass: 'select2-only-number simple-select2-align-center bg-secondary text-secondary',
        })
    }

    $(document).on('keypress', '.select2-only-number .select2-search__field', function() {
        $(this).val($(this).val().replace(/[^\d].+/, ""));
        if ((event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
    });

});