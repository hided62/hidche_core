type JQueryBootstrapToast = {
    container?: JQuery<HTMLElement>,
    position?: 'top-right' | 'top-left' | 'top-center' | 'bottom-right' | 'bottom-left' | 'bottom-center',
    title?: string,
    subtitle?: string,
    content?: string,
    type?: 'info'|'success'|'warning'|'danger'|'error',
    delay?: number,
    img?: string,
    pause_on_hover?: boolean,
}

interface JQueryStatic {
    toast(opt: JQueryBootstrapToast): void;
}