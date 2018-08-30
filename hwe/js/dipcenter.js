



jQuery(function($){

    function guiEditorInit($obj, editable){
    var $submitBtn = $obj.find('.submit');
    var $noticeInput = $obj.find('.input_form');
    var $editForm = $obj.find('.edit_form');
    var $cancelEdit = $obj.find('.cancel_edit');

    var editMode = false;

    function enableEditor(){
        editMode = true;
        $cancelEdit.show();
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
            fontNames: ['맑은 고딕', 'Nanum Gothic', 'Nanum Myeongjo', 'Nanum Pen Script', '굴림', '굴림체', '바탕', '바탕체', '궁서', '궁서체']

        }).summernote('code', $noticeInput.val());
    }

    function disableEditor(){
        editMode = false;
        $editForm.summernote('destroy');
        $cancelEdit.hide();
        $editForm.html($noticeInput.val()).addClass('viewer');
    }

    $cancelEdit.hide();
    $editForm.html($noticeInput.val());
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
        $noticeInput.val($editForm.summernote('code'));
    });

    $cancelEdit.click(function(e){
        e.preventDefault();
        disableEditor();
    });

}

guiEditorInit($('#noticeForm'), editable);
guiEditorInit($('#scoutMsgForm'), editable);

})