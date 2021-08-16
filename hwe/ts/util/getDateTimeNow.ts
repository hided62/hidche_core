import {DateTime} from 'luxon';

export const DATE_TIME_FORMAT = 'yyyy-MM-dd HH:mm:ss';
export const DATE_TIME_FORMAT_WITH_FRACTION = 'yyyy-MM-dd HH:mm:ss.SSS';

export function getDateTimeNow(withFraction = false): string{
    if(withFraction){
        return DateTime.now().toFormat(DATE_TIME_FORMAT_WITH_FRACTION);
    }
    else{
        return DateTime.now().toFormat(DATE_TIME_FORMAT);
    }
    
}