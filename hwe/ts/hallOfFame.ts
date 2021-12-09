import $ from 'jquery';
import { stringifyUrl } from 'query-string';
import { initTooltip } from './common_legacy';
import 'bootstrap';

$(function ($) {
    $('#by_scenario').on('change', function (e) {
        e.preventDefault();

        const $this = $(this);
        const scenarioIdx = $this.val();
        const seasonIdx = $(this).find('option:selected').data('season');

        document.location.href = stringifyUrl({
            url: 'a_hallOfFame.php',
            query: {
                scenarioIdx: scenarioIdx, seasonIdx: seasonIdx
            }
        });
    })

    initTooltip();
});