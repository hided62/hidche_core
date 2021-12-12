/* Forked from https://github.com/asiffermann/summernote-image-title */
import $ from 'jquery';
import 'summernote/dist/summernote-bs5.js';

$(function ($) {
    $.extend(true, $.summernote.lang, {
        'en-US': {
            imageFlip: {
                edit: 'Flip image',
                flipLabel: 'Alternative Image'
            }
        },
        'ko-KR': {
            imageFlip: {
                edit: '이미지 전환',
                flipLabel: '대체 이미지( , 로 구분)'
            }
        },
    });
    $.extend($.summernote.options, {
        imageFlip: {
            icon: '<i class="note-icon-pencil"/>',
            tooltip: 'Image Flip'
        }
    });
    $.extend($.summernote.plugins, {
        'imageFlip': function (this: Summernote.Plugin, context: Summernote.Context) {
            // eslint-disable-next-line @typescript-eslint/no-this-alias
            const self = this;

            const ui: Summernote.UI = $.summernote.ui;
            const $note = context.layoutInfo.note;
            const $editor = context.layoutInfo.editor;
            const $editable = context.layoutInfo.editable;
            //const $toolbar = context.layoutInfo.toolbar;

            if (typeof context.options.imageFlip === 'undefined') {
                context.options.imageFlip = {};
            }

            const options = context.options;
            const lang = options.langInfo;

            context.memo('button.imageFlip', function () {
                const button = ui.button({
                    contents: ui.icon(options.icons.pencil),
                    container: false,
                    tooltip: lang.imageFlip.edit,
                    click: function (/*e: JQuery.Event*/) {
                        context.invoke('imageFlip.show');
                    }
                });

                return button.render();
            });

            this.initialize = function () {
                const $container = options.dialogsInBody ? $(document.body) : $editor;

                const body = '<div class="form-group">' +
                    '<label>' + lang.imageFlip.flipLabel + '</label>' +
                    '<input class="note-image-flip-text form-control" type="text" />' +
                    '</div>';

                const footer = '<button href="#" class="btn btn-primary note-image-flip-btn">' + lang.imageFlip.edit + '</button>';

                this.$dialog = ui.dialog({
                    title: lang.imageFlip.edit,
                    body: body,
                    footer: footer
                }).render().appendTo($container);
            };

            this.destroy = function () {
                ui.hideDialog(this.$dialog);
                this.$dialog.remove();
            };

            this.bindEnterKey = function ($input, $btn) {
                $input.on('keypress', function (event) {
                    if (event.keyCode === 13) {
                        $btn.trigger('click');
                    }
                });
            };


            type ImgInfo = {
                imgDom: JQuery<HTMLElement>;
                flip: string;
            }

            this.show = function () {
                const $img = $($editable.data('target'));

                const imgInfo = {
                    imgDom: $img,
                    flip: $img.data('flip'),
                };


                this.showLinkDialog(imgInfo).then(function (imgInfo: ImgInfo) {
                    ui.hideDialog(self.$dialog);
                    const $img = imgInfo.imgDom;

                    if (imgInfo.flip) {
                        $img.attr('data-flip', imgInfo.flip);
                    }
                    else {
                        $img.removeData('flip');
                    }

                    $note.val(context.invoke('code'));
                    $note.change();
                });
            };

            this.showLinkDialog = function (imgInfo: ImgInfo) {
                return $.Deferred(function (deferred) {
                    const $imageFlip = self.$dialog.find('.note-image-flip-text');
                    const $editBtn = self.$dialog.find('.note-image-flip-btn');

                    ui.onDialogShown(self.$dialog, function () {
                        context.triggerEvent('dialog.shown');

                        $editBtn.click(function (event) {
                            event.preventDefault();
                            void deferred.resolve({
                                imgDom: imgInfo.imgDom,
                                flip: $imageFlip.val(),
                            });
                        });

                        $imageFlip.val(imgInfo.flip).trigger('focus');
                        self.bindEnterKey($imageFlip, $editBtn);

                    });

                    ui.onDialogHidden(self.$dialog, function () {
                        $editBtn.off('click');

                        if (deferred.state() === 'pending') {
                            void deferred.reject();
                        }
                    });

                    ui.showDialog(self.$dialog);
                });
            };
        }
    });
})