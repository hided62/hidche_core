/* Forked from https://github.com/asiffermann/summernote-image-title */

(function (factory) {
  /* Global define */
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define(['jquery'], factory);
  } else if (typeof module === 'object' && module.exports) {
    // Node/CommonJS
    module.exports = factory(require('jquery'));
  } else {
    // Browser globals
    factory(window.jQuery);
  }
}(function ($) {
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
      'imageFlip': function (context) {
          var self = this;

          var ui = $.summernote.ui;
          var $note = context.layoutInfo.note;
          var $editor = context.layoutInfo.editor;
          var $editable = context.layoutInfo.editable;
          var $toolbar  = context.layoutInfo.toolbar;

          if (typeof context.options.imageFlip === 'undefined') {
              context.options.imageFlip = {};
          }

          var options = context.options;
          var lang = options.langInfo;

          context.memo('button.imageFlip', function () {
              var button = ui.button({
                  contents: ui.icon(options.icons.pencil),
                  container: false,
                  tooltip: lang.imageFlip.edit,
                  click: function (e) {
                      context.invoke('imageFlip.show');
                  }
              });

              return button.render();
          });

          this.initialize = function () {
              var $container = options.dialogsInBody ? $(document.body) : $editor;

              var body = '<div class="form-group">' +
                           '<label>' + lang.imageFlip.flipLabel + '</label>' +
                           '<input class="note-image-flip-text form-control" type="text" />' +
                         '</div>';

              var footer = '<button href="#" class="btn btn-primary note-image-flip-btn">' + lang.imageFlip.edit + '</button>';

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

          this.show = function () {
              var $img = $($editable.data('target'));

              var imgInfo = {
                  imgDom: $img,
                  flip: $img.data('flip'),
              };
              this.showLinkDialog(imgInfo).then(function (imgInfo) {
                  ui.hideDialog(self.$dialog);
                  var $img = imgInfo.imgDom;

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

          this.showLinkDialog = function (imgInfo) {
              return $.Deferred(function (deferred) {
                  var $imageFlip = self.$dialog.find('.note-image-flip-text');
                  var $editBtn = self.$dialog.find('.note-image-flip-btn');

                  ui.onDialogShown(self.$dialog, function () {
                      context.triggerEvent('dialog.shown');

                      $editBtn.click(function (event) {
                          event.preventDefault();
                          deferred.resolve({
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
                          deferred.reject();
                      }
                  });

                  ui.showDialog(self.$dialog);
              });
          };
      }
  });
}));