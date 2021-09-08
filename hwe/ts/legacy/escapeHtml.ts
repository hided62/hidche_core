/**
 * <>& 등을 html에서도 그대로 보이도록 escape주는 함수
 * @see https://stackoverflow.com/questions/24816/escaping-html-strings-with-jquery
 */
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
export function escapeHtml(string: string): string{
    return String(string).replace(/[&<>"'`=/]/g, function (s: string) {
        return entityMap[s];
    });
}