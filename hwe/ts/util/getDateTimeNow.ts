import { formatTime } from '@util/formatTime';
export function getDateTimeNow(withFraction = false): string {
    return formatTime(new Date(), withFraction);
}