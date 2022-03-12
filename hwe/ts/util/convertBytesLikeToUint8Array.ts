import { BytesLike } from "./BytesLike";

export function convertBytesLikeToUint8Array(data: BytesLike, encodeUTF8 = true): Uint8Array {
    if (data instanceof Uint8Array) {
        return data;
    }
    if (data instanceof ArrayBuffer) {
        return new Uint8Array(data);
    }
    if (typeof (data) === 'string') {
        if(encodeUTF8){
            return (new TextEncoder()).encode(data);
        }
        return new Uint8Array(data.split('').map(s=>s.codePointAt(0) as number));
    }
    return new Uint8Array(data.buffer);
}