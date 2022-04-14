import "@scss/hallOfFame.scss";

import $ from 'jquery';
import { stringifyUrl } from 'query-string';
import { initTooltip } from "@/legacy/initTooltip";
import { auto500px } from './util/auto500px';
import { htmlReady } from './util/htmlReady';
import { insertCustomCSS } from './util/customCSS';

auto500px();

htmlReady(() => {
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
    insertCustomCSS();
});