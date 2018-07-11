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

jQuery(function($){
    $('.obj_tooltip').tooltip({
        title:function(){
            return $.trim($(this).find('.tooltiptext').html());
        },
        html:true
    });
});
