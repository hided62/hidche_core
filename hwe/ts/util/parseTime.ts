import {parseISO} from 'date-fns';

export function parseTime(dateString: string): Date{
    return parseISO(dateString);
}