/** 
 * <>& 등을 html에서도 그대로 보이도록 escape주는 함수
 * @see https://stackoverflow.com/questions/24816/escaping-html-strings-with-jquery
 * @param {string} string escape하고자 하는 문자열
 * @returns {string}
*/
var escapeHtml = (function (string) {
    var entityMap = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;',
        '`': '&#x60;',
        '=': '&#x3D;'
    };

    return function(string) {
        return String(string).replace(/[&<>"'`=\/]/g, function(s) {
            return entityMap[s];
        });
    }
})();

/**
 * 변수가 정수인지 확인하는 함수
 * @param {*} n 정수인지 확인하기 위한 인자
 * @return {boolean} 정수인지 여부
 */
function isInt(n) {
    return +n === n && !(n % 1);
}

;(function(ns){
    //https://gist.github.com/demouth/3217440
	/**
	 * mb_strwidth
	 * @param String
	 * @return int
	 * @see http://php.net/manual/ja/function.mb-strwidth.php
	 */
	var mb_strwidth = function(str){
		var i=0,l=str.length,c='',length=0;
		for(;i<l;i++){
			c=str.charCodeAt(i);
			if(0x0000<=c&&c<=0x0019){
				length += 0;
			}else if(0x0020<=c&&c<=0x1FFF){
				length += 1;
			}else if(0x2000<=c&&c<=0xFF60){
				length += 2;
			}else if(0xFF61<=c&&c<=0xFF9F){
				length += 1;
			}else if(0xFFA0<=c){
				length += 2;
			}
		}
		return length;
	};
	
	/**
	 * mb_strimwidth
	 * @param String
	 * @param int
	 * @param int
	 * @param String
	 * @return String
	 * @see http://www.php.net/manual/ja/function.mb-strimwidth.php
	 */
	var mb_strimwidth = function(str,start,width,trimmarker){
		if(typeof trimmarker === 'undefined') trimmarker='';
		var trimmakerWidth = mb_strwidth(trimmarker),i=start,l=str.length,trimmedLength=0,trimmedStr='';
		for(;i<l;i++){
			var charCode=str.charCodeAt(i),c=str.charAt(i),charWidth=mb_strwidth(c),next=str.charAt(i+1),nextWidth=mb_strwidth(next);
			trimmedLength += charWidth;
			trimmedStr += c;
			if(trimmedLength+trimmakerWidth+nextWidth>width){
				trimmedStr += trimmarker;
				break;
			}
		}
		return trimmedStr;
	};
	ns.mb_strwidth   = mb_strwidth;
	ns.mb_strimwidth = mb_strimwidth;
})(window);

/**
 * object의 array를 id를 key로 삼는 object로 재 변환
 * @param {Array.{id: Object}} arr id를 가진 object의 array
 * @returns {Object}
 */
function convertDictById(arr) {
    var result = {};
    arr.forEach(function (v, i) {
        result[v.id] = v;
    });
    return result;
}

/**
 * array를 set 형태의 object로 변환
 * @param {Array} arr 값을 가진 array
 * @returns {Object}
 */
function convertSet(arr) {
    var result = {};
    arr.forEach(function (v) {
        result[v] = true;
    });
    return result;
}


/** 
 * {0}, {1}, {2}형태로 포맷해주는 함수
*/
String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) { 
        return (typeof args[number] != 'undefined') ? args[number] : match;
    });
};


/**
 * 게임내에서 지원하는 color type만 선택할 수 있도록 해주는 함수
 * @param {string} color #AAAAAA 또는 AAAAAA 형태로 작성된 RGB hex color string
 * @returns {string}
 */
function convColorValue(color) {
    if (color.charAt(0) == '#') {
        color = color.substr(1);
    }
    color = color.toUpperCase();

    var colorBase = convertSet([
        '000080', '0000FF', '008000', '008080', '00BFFF', '00FF00', '00FFFF', '20B2AA',
        '2E8B57', '483D8B', '6495ED', '7B68EE', '7CFC00', '7FFFD4', '800000', '800080',
        '808000', '87CEEB', 'A0522D', 'A9A9A9', 'AFEEEE', 'BA55D3', 'E0FFFF', 'F5F5DC',
        'FF0000', 'FF00FF', 'FF6347', 'FFA500', 'FFC0CB', 'FFD700', 'FFDAB9', 'FFFF00',
        'FFFFFF'
    ]);

    if (!colorBase.hasOwnProperty(color)) {
        return '000000';
    }

    return color;

}


function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * 단순한 Template 함수.  <%변수명%>으로 template 가능
 * @see  https://github.com/krasimir/absurd/blob/master/lib/processors/html/helpers/TemplateEngine.js
 * @param {string} html 
 * @param {object} options 
 * @returns {string}
 */
var TemplateEngine = function (html, options) {
    var re = /<%(.+?)%>/g,
        reExp = /(^( )?(var|if|for|else|switch|case|break|{|}|;))(.*)?/g,
        code = 'with(obj) { var r=[];\n',
        cursor = 0,
        result,
        match;
    var add = function (line, js) {
        js ? (code += line.match(reExp) ? line + '\n' : 'r.push(' + line + ');\n') :
            (code += line != '' ? 'r.push("' + line.replace(/"/g, '\\"') + '");\n' : '');
        return add;
    }
    options.e = escapeHtml;
    while (match = re.exec(html)) {
        add(html.slice(cursor, match.index))(match[1], true);
        cursor = match.index + match[0].length;
    }
    add(html.substr(cursor, html.length - cursor));
    code = (code + 'return r.join(""); }').replace(/[\r\t\n]/g, ' ');
    try { result = new Function('obj', code).apply(options, [options]); }
    catch (err) { console.error("'" + err.message + "'", " in \n\nCode:\n", code, "\n"); }
    return result;
}

function getIconPath(imgsvr,  picture){
    // ../d_shared/common_path.js 필요
    if(!imgsvr){
        return pathConfig.sharedIcon+'/'+picture;
    }
    else{
        return pathConfig.root+'/d_pic/'+picture;
    }
}

function activeFlip($obj){
    var $result;
    if($obj === undefined){
        $result = $('img[data-flip]');
    }
    else{
        $result = $obj.find('img[data-flip]');
    }

    $result.each(function(){
        activeFlipItem($(this));
    });

}

function activeFlipItem($img){
    var imageList = [];
    imageList.push($img.attr('src'));
    $.each($img.data('flip').split(','), function(idx, value){
        var value = $.trim(value);
        if(!value){
            return true;
        }
        imageList.push(value);
    });
    if(imageList.length <= 1){
        return;
    }
    $img.data('computed_flip_array', imageList);
    $img.data('computed_flip_idx', 0);

    $img.click(function(){
        var arr = $img.data('computed_flip_array');
        var idx = $img.data('computed_flip_idx');
        idx = (idx + 1)%(arr.length);
        $img.attr('src', arr[idx]);
        $img.data('computed_flip_idx', idx);
    });
    $img.css('cursor','pointer');
}

jQuery(function($){
    $('.obj_tooltip').each(function(){
        var $objTooltip = $(this);
        var tooltipClassText = $objTooltip.data('tooltip-class');
        if(!tooltipClassText){
            tooltipClassText = '';
        }
        console.log($objTooltip.data('tooltip-class'));
        var template = '<div class="tooltip {0}" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
            .format(tooltipClassText);

        $objTooltip.tooltip({
            title:function(){
                return $.trim($(this).find('.tooltiptext').html());
            },
            template:template,
            html:true
        });
        
    });

    activeFlip();

    var customCSS = localStorage.getItem('sam_customCSS');
    if(customCSS){
        var $style = $('<style type="text/css"></style>');
        $style.text(customCSS); 
        $style.appendTo($('head'));
    }

    
});
