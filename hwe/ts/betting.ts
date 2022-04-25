import $ from 'jquery';
import { unwrap_any } from '@util/unwrap_any';
import { SammoAPI } from './SammoAPI';

declare const staticValues: {
    bettingID: number;
}

$(function ($) {
    $('.submitBtn').on('click', async function (e) {
        e.preventDefault();

        const $this = $(this);
        const target = parseInt($this.data('target'));
        const amount = parseInt(unwrap_any<string>($(`#target_${target}`).val()));

        try {
            await SammoAPI.Betting.Bet({
                bettingID: staticValues.bettingID,
                bettingType: [target],
                amount: amount,
            });
        } catch (e) {
            console.error(e);
            alert(`베팅을 실패했습니다: ${e}`);
            location.reload();
            return;
        }

        location.reload();
        return;
    });
});