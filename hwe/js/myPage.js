jQuery(function($){


    var initCustomCSSForm = function(){
        var lastTimeOut = null;
        var $obj = $('#custom_css');
        var key = 'sam_customCSS';

        var text = localStorage.getItem(key);
        if(text){
            $obj.val(text);
            console.log(text);
        }

        if($obj.on('change keyup paste', function(){
            var newText = $obj.val();
            if(text == newText){
                return;
            }
            if(lastTimeOut){
                clearTimeout(lastTimeOut);
            }
            $obj.css('background-color', '#222222');
            lastTimeOut = setTimeout(function(){
                text = $obj.val();
                localStorage.setItem(key, text);
                $obj.css('background-color', 'black');
            }, 500);
        }));

    }

    initCustomCSSForm();
});