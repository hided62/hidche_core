jQuery(function($){


var currYear = startYear;
var currMonth = startMonth;

var $yearMonth = $('#yearmonth');
var $elements = $();

var endDate = lastYear * 12 + lastMonth - 1;
var currDate = startYear * 12 + startMonth - 1;
while(currDate <= endDate){

    var target = currYear*100 + currMonth;
    var sel = '';
    if(currYear == selectYear && currMonth == selectMonth){
        sel = 'selected="selected"';
    }
    var option = $('<option value="{0}" {1} >{2}년 {3}월</option>'.format(target, sel, currYear, currMonth));
    $elements = $elements.add(option);

    currMonth += 1;
    if(currMonth > 12){
        currYear += 1;
        currMonth -= 12;
    }
    currDate += 1;
}
$yearMonth.empty();
$yearMonth.append($elements);
});