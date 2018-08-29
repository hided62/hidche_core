



jQuery(function($){

jQuery.trumbowyg.langs.ko.fontFamily = '글꼴';

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
            removeformatPasted: true,
            imageWidthModalEdit: true,
            minimalLinks: true,
            btns: [
                ['viewHTML'],
                ['undo', 'redo'], // Only supported in Blink browsers
                ['formatting'],
                ['fontfamily', 'fontsize'],
                ['foreColor', 'backColor'],
                ['emoji'],
                ['strong', 'em', 'del'],
                ['link'],
                ['insertImage'],
                ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                ['unorderedList', 'orderedList'],
                ['horizontalRule'],
                ['removeformat'],
                ['fullscreen']
            ],
            plugins: {
                fontfamily : {
                    fontList:[
                        {name:'맑은 고딕', family:"'맑은 고딕'"},
                        {name:'나눔 고딕', family:"'Nanum Gothic', sans-serif"},
                        {name:'나눔 명조', family:"'Nanum Myeongjo', serif"},
                        {name:'나눔손글씨 펜체', family:"'Nanum Pen Script', cursive"},
                        {name:'굴림', family:"'굴림'"},
                        {name:'굴림체', family:"'굴림체'"},
                        {name:'바탕', family:"'바탕'"},
                        {name:'바탕체', family:"'바탕체'"},
                        {name:'궁서', family:"'궁서'"},
                        {name:'궁서체', family:"'궁서체'"},
                    ]
                }
            },
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