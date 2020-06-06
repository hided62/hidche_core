jQuery(function($) {


    var initCustomCSSForm = function() {
        var lastTimeOut = null;
        var $obj = $('#custom_css');
        var key = 'sam_customCSS';

        var text = localStorage.getItem(key);
        if (text) {
            $obj.val(text);
            console.log(text);
        }

        if ($obj.on('change keyup paste', function() {
                var newText = $obj.val();
                if (text == newText) {
                    return;
                }
                if (lastTimeOut) {
                    clearTimeout(lastTimeOut);
                }
                $obj.css('background-color', '#222222');
                lastTimeOut = setTimeout(function() {
                    text = $obj.val();
                    localStorage.setItem(key, text);
                    $obj.css('background-color', 'black');
                }, 500);
            }));

    }

    $('.load_old_log').click(function() {
        var $thisBtn = $(this);
        var logType = $thisBtn.data('log_type');
        var $last = $('.log_{0}:last'.format(logType));
        var reqTo = null;
        if ($last.length) {
            reqTo = $last.data('seq');
        }

        $.post({
                url: 'j_general_log_old.php',
                dataType: 'json',
                data: {
                    to: reqTo,
                    type: logType
                }
            }).then(function(data) {
                if (!data) {
                    return quickReject('로그를 받아오지 못했습니다.');
                }
                if (!data.result) {
                    return quickReject('로그를 받아오지 못했습니다. : ' + data.reason);
                }

                var keys = Object.keys(data.log);
                if (keys.length > 1 && keys[0] < keys[1]) {
                    keys.reverse();
                }

                if (keys == 0) {
                    $thisBtn.hide();
                    return;
                }

                var html = [];
                $.each(keys, function(_, key) {
                    if ($('#log_{0}_{1}'.format(logType, key)).length) {
                        return true;
                    }
                    var item = data.log[key];
                    html.push("<div class='log_{0}' id='log_{0}_{1}' data-seq='{1}'>{2}</div>".format(logType, key, item));
                });

                $('#{0}Plate'.format(logType)).append(html);
            }, errUnknown)
            .fail(function(reason) {
                alert(reason);
                location.reload();
            });
    })

    initCustomCSSForm();


    $('#die_immediately').click(function() {
        if (!confirm('정말로 삭제하시겠습니까?')) {
            return false;
        }
        $.post({
                url: 'j_die_immediately.php',
                dataType: 'json',
            }).then(function(data) {
                if (!data) {
                    return quickReject('실패했습니다.');
                }
                if (!data.result) {
                    return quickReject(data.reason);
                }

                location.replace('..');

            }, errUnknown)
            .fail(function(reason) {
                alert(reason);
                location.reload();
            });
        return false;
        return false;
    });

    $('#vacation').click(function() {
        if (!confirm('휴가 기능을 신청할까요?')) {
            return false;
        }
        $.post({
                url: 'j_vacation.php',
                dataType: 'json',
            }).then(function(data) {
                if (!data) {
                    return quickReject('실패했습니다.');
                }
                if (!data.result) {
                    return quickReject(data.reason);
                }

                location.reload();

            }, errUnknown)
            .fail(function(reason) {
                alert(reason);
                location.reload();
            });
        return false;
        return false;
    });

    $('#set_my_setting').click(function() {
        $.post({
                url: 'j_set_my_setting.php',
                dataType: 'json',
                data: {
                    tnmt: $('.tnmt:checked').val(),
                    defence_train: $('#defence_train').val(),
                    use_treatment: $('#use_treatment').val(),
                    use_auto_nation_turn: $('#use_auto_nation_turn').val(),
                }
            }).then(function(data) {
                if (!data) {
                    return quickReject('실패했습니다.');
                }
                if (!data.result) {
                    return quickReject(data.reason);
                }

                location.reload();

            }, errUnknown)
            .fail(function(reason) {
                alert(reason);
                location.reload();
            });
        return false;
        return false;
    });
});