function ClearContent(sel) {
    $(sel).html("");
}

function Open(url) {
    window.open(url);
}

function Replace(url) {
    //location.replace(url);
    console.log('this',url);
}

function ReplaceFrame(url) {
    //window.top.location.replace(url);
    console.log('top',url);
}

function ImportStyle(href) {
    var CSS   = document.createElement('link');
    CSS.rel   = 'stylesheet';
    CSS.type  = 'text/css';
    CSS.media = 'screen';
    CSS.href  = href;
    document.getElementsByTagName('head')[0].appendChild(CSS);
}

function ImportView(sel, url) {
    var html = $.ajax({
        url: url,
        async: false
    });
    $(sel).append(html.responseText);
}

function ImportAction(sel, url) {
    var tag = "<script type=\"text/javascript\" src=\"" + url + "\"></script>";
    $(sel).append(tag);
}

function ImportAction(url) {
// 비동기라서 동기화된 함수로 변경
// 추후 cache: true, 로 변경
//    $.getScript(url, function() { eval(initFunc); });
    $.ajax({
        type: 'GET',
        url: url,
        cache: true,
        async: false,
        dataType: 'script'
    });
}

function GetJSON(url, data, callback) {
    $.ajax({
        type: 'GET',
        url: url,
        cache: true,
        async: true,
        data: data,
        success: callback,
        dataType: 'json',
        error: Error
    });
}

function GetJSONSync(url, data, callback) {
    $.ajax({
        type: 'GET',
        url: url,
        cache: true,
        async: false,
        data: data,
        success: callback,
        dataType: 'json',
        error: Error
    });
}

function PostJSON(url, data, callback) {
    $.ajax({
        type: 'POST',
        url: url,
        cache: true,
        async: true,
        data: data,
        success: callback,
        dataType: 'json',
        error: Error
    });
}

function PostJSONSync(url, data, callback) {
    $.ajax({
        type: 'POST',
        url: url,
        cache: true,
        async: false,
        data: data,
        success: callback,
        dataType: 'json',
        error: Error
    });
}

function Error(xhr, textStatus, errorThrown) {
//    alert(xhr.status);
    alert(xhr.responseText);
//    alert(textStatus);
//    alert(errorThrown);
/*
    if(xhr.status == 404) {
        alert("처리 프로그램이 없습니다!");
        alert(xhr.responseText);
    } else if(xhr.status == 500) {
        alert("처리 프로그램이 오류입니다!");
        alert(xhr.responseText);
    }
*/
}

function IsNumber(input) {
    var check = /(^\d+$)/;
    return check.test(input);
}

function Second(time, amount) {
    var h = parseInt(time.substr(0, 2), 10);
    var m = parseInt(time.substr(3, 2), 10);
    var s = parseInt(time.substr(6, 2), 10);
    s += amount;

    if(amount > 0) {
        if(s > 60) { m += Math.floor(s/60); s = s%60; }
        if(m > 60) { h += Math.floor(m/60); m = m%60; }
        if(h > 24) { h = h%24; }
    } else {
        if(s < 0) { m += Math.floor(s/60); s = 60+s%60; }
        if(m < 0) { h += Math.floor(m/60); m = 60+m%60; }
        if(h < 0) { h = 24+h%24; }
    }

    if(h < 10) h = "0"+h;
    if(m < 10) m = "0"+m;
    if(s < 10) s = "0"+s;

    var newTime = h+":"+m+":"+s;
    return newTime;
}

function ExitButton(obj) {
    $(obj).each(function() {
        $(this).css("background", "transparent url(../e_image/button/exit0x26x25.png) no-repeat");
        $(this).mouseover(function () { $(this).css("background", "transparent url(../e_image/button/exit1x26x25.png) no-repeat" ); });
        $(this).mouseout(function () { $(this).css("background", "transparent url(../e_image/button/exit0x26x25.png) no-repeat"); });
        $(this).mousedown(function () { $(this).css("background", "transparent url(../e_image/button/exit2x26x25.png) no-repeat"); });
        $(this).mouseup(function () { $(this).css("background", "transparent url(../e_image/button/exit1x26x25.png) no-repeat"); });
    });
}

function Button(obj, w) {
    $(obj).each(function() {
        $(this).css("background", "transparent url(../e_image/button/button0x"+w+"x20.png) no-repeat");
        $(this).mouseover(function () { $(this).css("background", "transparent url(../e_image/button/button1x"+w+"x20.png) no-repeat" ); });
        $(this).mouseout(function () { $(this).css("background", "transparent url(../e_image/button/button0x"+w+"x20.png) no-repeat"); });
        $(this).mousedown(function () { $(this).css("background", "transparent url(../e_image/button/button2x"+w+"x20.png) no-repeat"); });
        $(this).mouseup(function () { $(this).css("background", "transparent url(../e_image/button/button1x"+w+"x20.png) no-repeat"); });
    });
}

function Disable(obj, w) {
    $(obj).each(function() {
        $(this).css("background", "transparent url(../e_image/button/button3x"+w+"x20.png) no-repeat");
        $(this).css("color", "#CCCCCC");
        $(this).css("font-style", "italic");
        $(this).css("cursor", "default");
        $(this).attr("disabled", "true");
    });
}

function Enable(obj, w) {
    $(obj).each(function() {
        $(this).css("background", "transparent url(../e_image/button/button0x"+w+"x20.png) no-repeat");
        $(this).css("color", "#FFFFFF");
        $(this).css("font-style", "normal");
        $(this).css("cursor", "pointer");
        $(this).attr("disabled", "");
    });
}

function Drag(obj) {
    var _ox = $(obj).offset().left;
    var _oy = $(obj).offset().top;

    $(obj).attr("_x", 0);
    $(obj).attr("_y", 0);
    $(obj).attr("_ox", _ox);
    $(obj).attr("_oy", _oy);

    $(obj).bind("dragstart", function(event) {
        $(this).attr("_x", $(this).scrollLeft());
        $(this).attr("_y", $(this).scrollTop());
    });

    $(obj).bind("dragend", function(event) {
        $(this).attr("_x", $(this).scrollLeft());
        $(this).attr("_y", $(this).scrollTop());
    });

    $(obj).bind("drag", function(event) {
        var _x = parseInt($(this).attr("_x"));
        var _y = parseInt($(this).attr("_y"));
        var _ox = parseInt($(this).attr("_ox"));
        var _oy = parseInt($(this).attr("_oy"));

        $(this).scrollLeft(_x + _ox - event.offsetX);
        $(this).scrollTop(_y + _oy - event.offsetY);
    });
}

function ScrollTo(obj, x, y) {
    var w = $(obj).width();
    var h = $(obj).height();

    $(obj).scrollLeft(x - w/2);
    $(obj).scrollTop(y - h/2);
}

function CheckIE6PNG() {
    if($.browser.msie == true && $.browser.version == "6.0") {
        DD_belatedPNG.fix(".png");
    }
}
