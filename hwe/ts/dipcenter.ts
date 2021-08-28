import $ from 'jquery';
import Popper from 'popper.js';
(window as unknown as { Popper: unknown }).Popper = Popper;//XXX: 왜 popper를 이렇게 불러야 하는가?
import 'bootstrap';
import 'summernote/dist/summernote-bs4';
import 'summernote/dist/summernote-bs4.css';
import './summernote-image-flip';
import { activateFlip } from './common_legacy';
import { setAxiosXMLHttpRequest } from './util/setAxiosXMLHttpRequest';

declare const editable: boolean;

declare const storedData: {
    nationMsg: string,
    scoutMsg: string,
}

$(function ($) {
    setAxiosXMLHttpRequest();
    function guiEditorInit($obj: JQuery<HTMLElement>, editable: boolean) {
        const $submitBtn = $obj.find('.submit');
        const $noticeInput = $obj.find('.input_form');
        const globalVariableName: 'nationMsg' | 'scoutMsg' = $noticeInput.data('global');
        const $editForm = $obj.find('.edit_form');
        const $cancelEdit = $obj.find('.cancel_edit');

        let editMode = false;

        function enableEditor() {
            editMode = true;
            $cancelEdit.show();

            let inputText = storedData[globalVariableName];
            if (!inputText || inputText == '<p></p>') {
                inputText = '<p><br></p>';
            }

            $editForm.removeClass('viewer').summernote({
                minHeight: 200,
                maxHeight: undefined,
                focus: true,
                lang: 'ko-KR',
                toolbar: [
                    // [groupName, [list of button]]
                    ['misc', ['undo', 'redo']],
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname', 'fontsize']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['color', ['color']],
                    ['insert', ['picture', 'link', 'video', 'table', 'hr']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['misc', ['codeview']]
                ],
                popover: {
                    image: [
                        ['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
                        ['float', ['floatLeft', 'floatRight', 'floatNone']],
                        ['remove', ['removeMedia']],
                        ['custom', ['imageFlip']],
                    ],
                },
                fontNames: ['맑은 고딕', 'Nanum Gothic', 'Nanum Myeongjo', 'Nanum Pen Script', '굴림', '굴림체', '바탕', '바탕체', '궁서', '궁서체'],
                fontSizes: ['8', '9', '10', '11', '12', '14', '16', '20', '24', '28', '32', '36', '40', '46', '52', '60'],
                callbacks: {
                    onImageUpload: function (files) {
                        $editForm.summernote('saveRange');
                        if (files.length == 0) {
                            alert('업로드된 파일이 없습니다.');
                            return false;
                        }

                        const formData = new FormData();
                        formData.append('img', files[0]);
                        $editForm.summernote("pasteHtml", '');

                        $.ajax({
                            type: 'post',
                            url: 'j_image_upload.php',
                            dataType: 'json',
                            contentType: false,
                            processData: false,
                            data: formData
                        }).then(function (result) {
                            if (!result.result) {
                                alert(result.reason);
                                return;
                            }

                            console.log($editForm.summernote('code'));
                            if ($editForm.summernote('isEmpty')) {

                                console.log('haha?');
                                const $img = $('<img>');
                                $img.attr('src', result.path);
                                $editForm.summernote('code', $img.html());
                                return;
                            }

                            $editForm.summernote("insertImage", result.path, result.path);

                        }, function () {
                            alert('알 수 없는 이유로 아이콘 업로드를 실패했습니다.');
                        });
                    }
                }
            }).summernote('code', inputText);
        }

        function disableEditor() {
            editMode = false;
            $editForm.summernote('destroy');
            $cancelEdit.hide();
            $editForm.html(storedData[globalVariableName]).addClass('viewer');
            activateFlip($editForm);
        }

        $cancelEdit.hide();
        $editForm.html(storedData[globalVariableName]);
        activateFlip($editForm);
        if (editable) {
            $submitBtn.prop('disabled', false);
        }
        else {
            $submitBtn.prop('disabled', true);
        }

        $submitBtn.click(function (e) {
            if (!editMode) {
                e.preventDefault();
                enableEditor();
                return false;
            }
            const text = $editForm.summernote('code');
            storedData[globalVariableName] = text;
            $noticeInput.val(text);
        });

        $cancelEdit.click(function (e) {
            e.preventDefault();
            disableEditor();
        });

    }

    guiEditorInit($('#noticeForm'), editable);
    guiEditorInit($('#scoutMsgForm'), editable);
    activateFlip();

})