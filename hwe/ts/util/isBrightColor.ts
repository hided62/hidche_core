import { unwrap } from "@util/unwrap";
import { hexToRgb } from "@util/hexToRgb";

export function isBrightColor(color: string): boolean {
    const cv = unwrap(hexToRgb(color));
    if ((cv.r * 0.299 + cv.g * 0.587 + cv.b * 0.114) > 140) {
        return true;
    } else {
        return false;
    }
}
