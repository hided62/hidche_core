import { automata초성All } from "./automata초성";
import { filter초성withAlphabet } from "./filter초성withAlphabet";

export function convertSearch초성(text: string): string[]{
    const [filteredTextH, filteredTextA] = filter초성withAlphabet(text.replace(/\s+/g, ""));
    const [filteredTextHL1, filteredTextHL2] = automata초성All(filteredTextH);

    return [text, filteredTextA, filteredTextH, filteredTextHL1, filteredTextHL2];
}