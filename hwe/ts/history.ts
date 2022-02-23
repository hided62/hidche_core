import $ from 'jquery';
import '@/map';
import { joinYearMonth } from './util/joinYearMonth';
import { parseYearMonth } from './util/parseYearMonth';

declare const startYear:number;
declare const startMonth:number;

declare const lastYear: number;
declare const lastMonth: number;

declare const selectYear: number;
declare const selectMonth: number;

$(function ($) {
    let currYear = startYear;
    let currMonth = startMonth;
    const selectDate = joinYearMonth(selectYear, selectMonth);

    const $yearMonth = $('#yearmonth');
    let $elements = $();

    const endDate = joinYearMonth(lastYear, lastMonth) + 1;//연감 마지막 + 1(현재)
    let currDate = joinYearMonth(startYear, startMonth);
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