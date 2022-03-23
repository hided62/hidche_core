import $ from 'jquery';
import '@/map';
import { joinYearMonth } from './util/joinYearMonth';
import { parseYearMonth } from './util/parseYearMonth';

declare const staticValues: {
    startYear:number;
    startMonth:number;

    lastYear: number;
    lastMonth: number;

    selectYear: number;
    selectMonth: number;
}



$(function ($) {
    let currYear = staticValues.startYear;
    let currMonth = staticValues.startMonth;
    const selectDate = joinYearMonth(staticValues.selectYear, staticValues.selectMonth);

    const $yearMonth = $('#yearmonth');
    let $elements = $();

    const endDate = joinYearMonth(staticValues.lastYear, staticValues.lastMonth) + 1;//연감 마지막 + 1(현재)
    let currDate = joinYearMonth(currYear, currMonth);
    while (currDate <= endDate) {
        let sel = '';
        if (currDate == selectDate) {
            sel = 'selected="selected"';
        }

        const more = currDate == endDate? ' (현재)':'';
        const option = $(`<option value="${currDate}" ${sel} >${currYear}년 ${currMonth}월${more}</option>`);
        $elements = $elements.add(option);

        currDate += 1;
        [currYear, currMonth] = parseYearMonth(currDate);
    }
    $yearMonth.empty();
    $yearMonth.append($elements);
});