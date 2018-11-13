



jQuery(function($){

    function guiEditorInit($obj, editable){
    var $submitBtn = $obj.find('.submit');
    var $noticeInput = $obj.find('.input_form');
    var globalVariableName = $noticeInput.data('global');
    var $editForm = $obj.find('.edit_form');
    var $cancelEdit = $obj.find('.cancel_edit');

    var editMode = false;

    function enableEditor(){
        editMode = true;
        $cancelEdit.show();

        var inputText = window[globalVariableName];
        if(!inputText || inputText == '<p></p>'){
            inputText = '<p><br></p>';
        }

        $editForm.removeClass('viewer').summernote({
            minHeight:200,
            maxHeight:null,
            focus:true,
            lang:'ko-KR',
            toolbar: [
                // [groupName, [list of button]]
                ['do', ['undo', 'redo']],
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font2', ['fontname', 'fontsize']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['color', ['color']],
                ['in', ['picture', 'link', 'video', 'table', 'hr']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height', 'codeview']]
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
                onImageUpload: function(files) {
                    $editForm.summernote('saveRange');
                    if(files.length == 0){
                        alert('업로드된 파일이 없습니다.');
                        return false;
                    }

                    var formData = new FormData();
                    formData.append('img', files[0]);
                    $editForm.summernote("pasteHtml", '');

                    $.ajax({
                        type:'post',
                        url:'j_image_upload.php',
                        dataType:'json',
                        contentType: false,
                        processData:false,
                        data:formData
                    }).then(function(result){
                        if(!result.result){
                            alert(result.reason);
                            return;
                        }

                        console.log($editForm.summernote('code'));
                        if($editForm.summernote('isEmpty')){
                            
                            console.log('haha?');
                            var $img = $('<img>');
                            $img.attr('src', result.path);
                            $editForm.summernote('code', $img);
                            return;
                        }
                        
                        $editForm.summernote("insertImage", result.path, result.path);
                        
                    },function(){
                        alert('알 수 없는 이유로 아이콘 업로드를 실패했습니다.');
                    });
                }
            }
        }).summernote('code', inputText);
    }

    function disableEditor(){
        editMode = false;
        $editForm.summernote('destroy');
        $cancelEdit.hide();
        $editForm.html(window[globalVariableName]).addClass('viewer');
        activeFlip($editForm);
    }

    $cancelEdit.hide();
    $editForm.html(window[globalVariableName]);
    activeFlip($editForm);
    if(editable){
        $submitBtn.prop('disabled', false);
    }
    else{
        $submitBtn.prop('disabled', true);
    }

    $submitBtn.click(function(e){
        if(!editMode){
            e.preventDefault();
            enableEditor();
            return false;
        }
        var text = $editForm.summernote('code');
        window[globalVariableName] = text;
        $noticeInput.val(text);
    });

    $cancelEdit.click(function(e){
        e.preventDefault();
        disableEditor();
    });

}

guiEditorInit($('#noticeForm'), editable);
guiEditorInit($('#scoutMsgForm'), editable);

})