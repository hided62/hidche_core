import { unwrap } from "@util/unwrap";

export function autoResizeTextarea(e: Event): void {
    const el = unwrap(e.target) as HTMLInputElement;
    el.style.height = 'auto';
    el.style.height = `${el.scrollHeight + 1}px`;
}