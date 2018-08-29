



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
        $editForm.removeClass('viewer').trumbowyg({
            autogrow: true,
            autogrowOnEnter: true,
            lang: 'ko',
            removeformatPasted: true
            
        }).trumbowyg('html', $noticeInput.val());
    }

    function disableEditor(){
        editMode = false;
        $editForm.trumbowyg('destroy');
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
        $noticeInput.val($editForm.trumbowyg('html'));
    });

    $cancelEdit.click(function(e){
        e.preventDefault();
        disableEditor();
    });

}

guiEditorInit($('#noticeForm'), editable);
guiEditorInit($('#scoutMsgForm'), editable);

})