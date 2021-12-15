export function insertCustomCSS(key = 'sam_customCSS'){
    const customCSS = localStorage.getItem(key);
    if (customCSS) {
        const css = document.createElement('style');
        css.innerHTML = customCSS;
        console.log(css);
        document.getElementsByTagName('head')[0].appendChild(css);
    }
}
