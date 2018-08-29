(function ($) {
    'use strict';

    $.extend(true, $.trumbowyg, {
        langs: {
            // jshint camelcase:false
            en: {
                fontsize: 'Font size',
                fontsizes: {
                    'custom': 'Custom'
                }
            },
            ko: {
                fontsize: '글자 크기',
                fontsizes: {
                    'custom': '사용자 정의'
                }
            },
        }
    });
    // jshint camelcase:true

    // Add dropdown with font sizes
    $.extend(true, $.trumbowyg, {
        plugins: {
            fontsize: {
                init: function (trumbowyg) {
                    trumbowyg.addBtnDef('fontsize', {
                        dropdown: buildDropdown(trumbowyg)
                    });
                }
            }
        }
    });

    function buildDropdown(trumbowyg) {
        var dropdown = [];
        var sizes = [
            '8px',
            '9px',
            '10px',
            '11px',
            '12px',
            '14px',
            '16px',
            '18px',
            '20px',
            '24px',
            '28px',
            '32px',
            '40px',
            '46px',
            '52px',
            '60px'
        ];

        $.each(sizes, function (index, size) {
            trumbowyg.addBtnDef('fontsize_' + size, {
                text: '<span style="font-size: ' + size + ';">' + size + '</span>',
                hasIcon: false,
                fn: function () {
                    trumbowyg.saveRange();
                    var text = trumbowyg.range.startContainer.parentElement;
                    var selectedText = trumbowyg.getRangeText();
                    if ($(text).html() === selectedText) {
                        $(text).css('font-size', size);
                    } else {
                        trumbowyg.range.deleteContents();
                        var html = '<span style="font-size: ' + size + ';">' + selectedText + '</span>';
                        var node = $(html)[0];
                        trumbowyg.range.insertNode(node);
                    }
                    trumbowyg.saveRange();
                    trumbowyg.syncCode();
                    trumbowyg.$c.trigger('tbwchange');
                    return true;
                }
            });
            dropdown.push('fontsize_' + size);
        });

        var freeSizeButtonName = 'fontsize_custom',
            freeSizeBtnDef = {
                fn: function () {
                    trumbowyg.openModalInsert('Custom Font Size',
                        {
                            size: {
                                label: 'Font Size',
                                value: '48px'
                            }
                        },
                        function (values) {
                            var text = trumbowyg.range.startContainer.parentElement;
                            var selectedText = trumbowyg.getRangeText();
                            if ($(text).html() === selectedText) {
                                $(text).css('font-size', values.size);
                            } else {
                                trumbowyg.range.deleteContents();
                                var html = '<span style="font-size: ' + values.size + ';">' + selectedText + '</span>';
                                var node = $(html)[0];
                                trumbowyg.range.insertNode(node);
                            }
                            trumbowyg.saveRange();
                            trumbowyg.syncCode();
                            trumbowyg.$c.trigger('tbwchange');
                            return true;
                        }
                    );
                },
                text: '<span style="font-size: medium;">' + trumbowyg.lang.fontsizes.custom + '</span>',
                hasIcon: false
            };
        trumbowyg.addBtnDef(freeSizeButtonName, freeSizeBtnDef);
        dropdown.push(freeSizeButtonName);

        return dropdown;
    }
})(jQuery);
