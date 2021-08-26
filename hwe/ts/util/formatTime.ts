import { format, formatISO9075 } from 'date-fns';
//const DATE_TIME_FORMAT = 'yyyy-MM-dd HH:mm:ss';
const DATE_TIME_FORMAT_WITH_FRACTION = 'yyyy-MM-dd HH:mm:ss.SSS';

export function formatTime(time: Date, withFraction?:boolean): string;
export function formatTime(time: Date, format:string): string;

export function formatTime(time: Date, withFractionOrFormat:string|boolean = false): string {
    if (typeof withFractionOrFormat === "string") {
        return format(time, withFractionOrFormat);
    }
    else if(withFractionOrFormat){
        return format(time, DATE_TIME_FORMAT_WITH_FRACTION);
    }
    else {
        return formatISO9075(time);
    }
}