import { unwrap } from "./unwrap";

//https://stackoverflow.com/questions/36280818/how-to-convert-file-to-base64-in-javascript
export function getBase64FromFileObject(file: File): Promise<string> {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => {
            let encoded = unwrap(reader.result).toString().replace(/^data:(.*,)?/, '');
            if ((encoded.length % 4) > 0) {
                encoded += '='.repeat(4 - (encoded.length % 4));
            }
            resolve(encoded);
        };
        reader.onerror = error => reject(error);
    });
}