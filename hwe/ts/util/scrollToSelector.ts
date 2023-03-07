export function scrollToSelector(selector: string): void {
    const element = document.querySelector(selector);
    if(!element){
        return;
    }
    element.scrollIntoView({
        behavior: 'auto',
    });
  }