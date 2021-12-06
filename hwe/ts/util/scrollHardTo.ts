export function scrollHardTo(elementId: string): void {
    const element = document.getElementById(elementId);
    if(!element){
        return;
    }
    element.scrollIntoView({
        behavior: 'auto',
    });
  }