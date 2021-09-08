import { unwrap } from "./unwrap";

export function autoResizeTextarea(e: InputEvent): void {
    const el = unwrap(e.target) as HTMLInputElement;
    el.style.height = 'auto';
    el.style.height = `${el.scrollHeight + 1}px`;
}