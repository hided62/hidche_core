import { escapeHtml } from "../legacy/escapeHtml";
import linkifyStr from 'linkifyjs/string';
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
    const code = ['with(obj) { var r=[];\n'];
    let cursor = 0;
    const add = function (line: string, js?: boolean) {
        js ? code.push(line.match(reExp) ? line + '\n' : 'r.push(' + line + ');\n') :
            code.push(line != '' ? 'r.push("' + line.replace(/"/g, '\\"') + '");\n' : '');
        return add;
    };
    options.e = escapeHtml;
    options.linkifyStr = linkifyStr;
    for (; ;) {
        const match = re.exec(html);
        if (!match) {
            break;
        }
        add(html.slice(cursor, match.index))(match[1], true);
        cursor = match.index + match[0].length;
    }
    add(html.substr(cursor, html.length - cursor));

    code.push('return r.join(""); }');
    const compiledCode = code.join('').replace(/[\r\t\n]/g, ' ');
    try {
        return new Function('obj', compiledCode).apply(options, [options]);
    } catch (err: unknown) {
        console.error(err, " in \n\nCode:\n", code, "\n");
        throw err;
    }
}
