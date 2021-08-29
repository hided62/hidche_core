import $ from 'jquery';
import './map';

declare const startYear:number;
declare const startMonth:number;

declare const lastYear: number;
declare const lastMonth: number;

declare const selectYear: number;
declare const selectMonth: number;

$(function ($) {
    let currYear = startYear;
    let currMonth = startMonth;

    const $yearMonth = $('#yearmonth');
    let $elements = $();

    const endDate = lastYear * 12 + lastMonth - 1;
    let currDate = startYear * 12 + startMonth - 1;
    while (currDate <= endDate) {

        const target = currYear * 100 + currMonth;
        let sel = '';
        if (currYear == selectYear && currMonth == selectMonth) {
            sel = 'selected="selected"';
        }
        const option = $(`<option value="${target}" ${sel} >${currYear}년 ${currMonth}월</option>`);
        $elements = $elements.add(option);

        currMonth += 1;
        if (currMonth > 12) {
            currYear += 1;
            currMonth -= 12;
        }
        currDate += 1;
    }
    $yearMonth.empty();
    $yearMonth.append($elements);
});