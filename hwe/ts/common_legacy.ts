import { unwrap } from "./util";

declare const jQuery: JQueryStatic;


/** 
 * <>& 등을 html에서도 그대로 보이도록 escape주는 함수
 * @see https://stackoverflow.com/questions/24816/escaping-html-strings-with-jquery
 */
export const escapeHtml = (() => {
    const entityMap: { [v: string]: string } = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;',
        '`': '&#x60;',
        '=': '&#x3D;'
    };

    return function (string: string) {
        return String(string).replace(/[&<>"'`=/]/g, function (s: string) {
            return entityMap[s];
        });
    }
})();

/**
 * 변수가 정수인지 확인하는 함수
 * @param {*} n 정수인지 확인하기 위한 인자
 * @return {boolean} 정수인지 여부
 */
export function isInt(n: unknown): n is number {
    const v = n as number;
    return +v === v && !(v % 1);
}


//https://gist.github.com/demouth/3217440
/**
 * mb_strwidth
 * @see http://php.net/manual/function.mb-strwidth.php
 */
export function mb_strwidth(str: string): number {
    const l = str.length;
    let length = 0;
    for (let i = 0; i < l; i++) {
        const c = str.charCodeAt(i);
        if (0x0000 <= c && c <= 0x0019) {
            length += 0;
        } else if (0x0020 <= c && c <= 0x1FFF) {
            length += 1;
        } else if (0x2000 <= c && c <= 0xFF60) {
            length += 2;
        } else if (0xFF61 <= c && c <= 0xFF9F) {
            length += 1;
        } else if (0xFFA0 <= c) {
            length += 2;
        }
    }
    return length;
}


/**
 * mb_strimwidth
 * @param String
 * @param int
 * @param int
 * @param String
 * @return String
 * @see http://www.php.net/manual/function.mb-strimwidth.php
 */
export function mb_strimwidth(str: string, start: number, width: number, trimmarker = ''): string {
    const trimmakerWidth = mb_strwidth(trimmarker);
    const l = str.length;
    let trimmedLength = 0;
    const trimmedStr: string[] = [];
    for (let i = start; i < l; i++) {
        const c = str.charAt(i);
        const charWidth = mb_strwidth(c);
        const next = str.charAt(i + 1);
        const nextWidth = mb_strwidth(next);

        trimmedLength += charWidth;
        trimmedStr.push(c);
        if (trimmedLength + trimmakerWidth + nextWidth > width) {
            trimmedStr.push(trimmarker);
            break;
        }
    }
    return trimmedStr.join('');
}

/**
 * object의 array를 id를 key로 삼는 object로 재 변환
 */
export function convertDictById<K extends string | number, T extends { id: K }>(arr: ArrayLike<T>): Record<K, T> {
    const result: Record<string | number, T> = {};
    for (const v of Object.values(arr)) {
        result[v.id] = v;
    }
    return result;
}

/**
 * array를 set 형태의 object로 변환
 */
export function convertSet<K extends string | number>(arr: ArrayLike<K>): Record<K, true> {
    const result: Record<string | number, true> = {};
    for (const v of Object.values(arr)) {
        result[v] = true;
    }
    return result;
}


/** 
 * {0}, {1}, {2}형태로 포맷해주는 함수
 */

declare global {
    interface String {
        format(...args: string[]): string;
    }
}
String.prototype.format = function (...args: string[]) {
    return this.replace(/{(\d+)}/g, function (match, number) {
        return (typeof args[number] != 'undefined') ? args[number] : match;
    });
};


export function hexToRgb(hex: string): { r: number, g: number, b: number } | null {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

export function isBrightColor(color: string): boolean {
    const cv = unwrap(hexToRgb(color));
    if ((cv.r * 0.299 + cv.g * 0.587 + cv.b * 0.114) > 140) {
        return true;
    } else {
        return false;
    }
}

/**
 * 게임내에서 지원하는 color type만 선택할 수 있도록 해주는 함수
 * @param {string} color #AAAAAA 또는 AAAAAA 형태로 작성된 RGB hex color string
 * @returns {string}
 */
export function convColorValue(color: string): string {
    if (color.charAt(0) == '#') {
        color = color.substr(1);
    }
    color = color.toUpperCase();

    const colorBase = new Set([
        '000080', '0000FF', '008000', '008080', '00BFFF', '00FF00', '00FFFF', '20B2AA',
        '2E8B57', '483D8B', '6495ED', '7B68EE', '7CFC00', '7FFFD4', '800000', '800080',
        '808000', '87CEEB', 'A0522D', 'A9A9A9', 'AFEEEE', 'BA55D3', 'E0FFFF', 'F5F5DC',
        'FF0000', 'FF00FF', 'FF6347', 'FFA500', 'FFC0CB', 'FFD700', 'FFDAB9', 'FFFF00',
        'FFFFFF'
    ]);

    if (!colorBase.has(color)) {
        return '000000';
    }

    return color;
}


export function numberWithCommas(x: number): string {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

//linkify가 불러와 있어야함
declare global {
    interface Window {
        linkifyStr: (v: string, k?: Record<string, string | number>) => string;
    }
}
export function linkifyStrWithOpt(text: string): string {
    return window.linkifyStr(text, {});
}

/**
 * 단순한 Template 함수.  <%변수명%>으로 template 가능
 * @see  https://github.com/krasimir/absurd/blob/master/lib/processors/html/helpers/TemplateEngine.js
 * @param {string} html 
 * @param {object} options 
 * @returns {string}
 */
export function TemplateEngine(html: string, options: Record<string | number, unknown> = {}): string {
    const re = /<%(.+?)%>/g;
    const reExp = /(^( )?(var|if|for|else|switch|case|break|{|}|;))(.*)?/g;
    let cursor = 0;
    const add = function (line: string, js?: boolean) {
        js ? (code += line.match(reExp) ? line + '\n' : 'r.push(' + line + ');\n') :
            (code += line != '' ? 'r.push("' + line.replace(/"/g, '\\"') + '");\n' : '');
        return add;
    }
    options.e = escapeHtml;
    options.linkifyStr = linkifyStrWithOpt;
    for (; ;) {
        const match = re.exec(html);
        if (!match) {
            break;
        }
        add(html.slice(cursor, match.index))(match[1], true);
        cursor = match.index + match[0].length;
    }
    add(html.substr(cursor, html.length - cursor));

    let code = 'with(obj) { var r=[];\n';
    code = (code + 'return r.join(""); }').replace(/[\r\t\n]/g, ' ');
    try {
        return new Function('obj', code).apply(options, [options]);
    } catch (err) {
        console.error("'" + err.message + "'", " in \n\nCode:\n", code, "\n");
        throw err;
    }
}

export function getIconPath(imgsvr: boolean | 1 | 0, picture: string): string {
    // ../d_shared/common_path.js 필요
    if (!imgsvr) {
        return window.pathConfig.sharedIcon + '/' + picture;
    } else {
        return window.pathConfig.root + '/d_pic/' + picture;
    }
}

export function activeFlip($obj?: JQuery<HTMLElement>): void {
    let $result: JQuery<HTMLElement>;
    if ($obj === undefined) {
        $result = $('img[data-flip]');
    } else {
        $result = $obj.find('img[data-flip]');
    }

    $result.each(function () {
        activeFlipItem($(this));
    });

}

export function combineObject<K extends string, V>(item: V[], columnList: K[]): Record<K, V> {
    const newItem: Record<string, V> = {};
    for (const columnIdx in columnList) {
        const columnName = columnList[columnIdx];
        newItem[columnName] = item[columnIdx];
    }
    return newItem;
}

export function combineArray<K extends string, V>(array: V[][], columnList: K[]): Record<K, V>[] {
    const result: Record<K, V>[] = [];
    for (const key of array.keys()) {
        const item = array[key];
        result[key] = combineObject(item, columnList);
    }
    return result;
}

export function activeFlipItem($img: JQuery<HTMLElement>): void {
    const imageList = [];
    imageList.push($img.attr('src'));
    $.each($img.data('flip').split(','), function (idx, value) {
        value = $.trim(value);
        if (!value) {
            return true;
        }
        imageList.push(value);
    });
    if (imageList.length <= 1) {
        return;
    }
    $img.data('computed_flip_array', imageList);
    $img.data('computed_flip_idx', 0);

    $img.click(function () {
        const arr = $img.data('computed_flip_array');
        let idx = $img.data('computed_flip_idx');
        idx = (idx + 1) % (arr.length);
        $img.attr('src', arr[idx]);
        $img.data('computed_flip_idx', idx);
    });
    $img.css('cursor', 'pointer');
}



export function errUnknown(): void {
    alert('작업을 실패했습니다.');
}



export function errUnknownToast(): void {
    jQuery.toast({
        title: '에러!',
        content: '작업을 실패했습니다.',
        type: 'danger',
        delay: 5000
    });
}

export function quickReject<T>(errMsg: string): JQuery.Promise<T> {
    if (errMsg === undefined) {
        errMsg = '작업을 실패했습니다.';
    }
    const deferred = $.Deferred();
    void deferred.reject(errMsg);
    return deferred.promise();
}

export function nl2br(text: string): string {
    return text.replace(/\n/g, "<br>");
}
/*
function br2nl (text) {   
    return text.replace(/<\s*\/?br\s*[\/]?>/gi, '\n');
}
*/

export function getNpcColor(npcType: number): 'cyan' | 'skyblue' | null {
    if (npcType >= 2) {
        return 'cyan';
    }
    if (npcType == 1) {
        return 'skyblue';
    }
    return null;
}

export function initTooltip($obj?: JQuery<HTMLElement>): void {
    if ($obj === undefined) {
        $obj = $('.obj_tooltip');
    } else if (!$obj.hasClass('obj_tooltip')) {
        $obj = $obj.find('.obj_tooltip');
    }
    console.log($obj);

    $obj.each(function () {
        const $target = $(this);

        if ($target.data('installHandler')) {
            return;
        }
        $target.data('installHandler', true);

        $target.mouseover(function () {
            const $objTooltip = $(this);
            if ($objTooltip.data('setObjTooltip')) {
                return;
            }

            let tooltipClassText = $objTooltip.data('tooltip-class');
            if (!tooltipClassText) {
                tooltipClassText = '';
            }
            const template = '<div class="tooltip {0}" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                .format(tooltipClassText);

            $objTooltip.tooltip({
                title: function () {
                    return $.trim($(this).find('.tooltiptext').html());
                },
                template: template,
                html: true
            }).tooltip('show');

            $objTooltip.data('setObjTooltip', true);
        });
    });
}