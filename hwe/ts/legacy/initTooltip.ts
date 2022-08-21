import Tooltip from "bootstrap/js/dist/tooltip";
//HACK: 이유는 잘 모르겠지만 bootstrap-vue3에서 bootstrap 호출하는 것과 충돌하여 우회 중
import $ from "jquery";
import { trim } from "lodash-es";

export function initTooltip($obj?: JQuery<HTMLElement>): void {
    if ($obj === undefined) {
        $obj = $('.obj_tooltip');
    } else if (!$obj.hasClass('obj_tooltip')) {
        $obj = $obj.find('.obj_tooltip');
    }

    $obj.each(function () {
        const $target = $(this);

        if ($target.data('installHandler')) {
            return;
        }
        $target.data('installHandler', true);

        $target.on('mouseover', function () {
            const $objTooltip = $(this);
            if ($objTooltip.data('setObjTooltip')) {
                return;
            }

            let tooltipClassText = $objTooltip.data('tooltip-class');
            if (!tooltipClassText) {
                tooltipClassText = '';
            }
            const template = `<div class="tooltip ${tooltipClassText}" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>`;

            const oTooltip = new Tooltip(this, {
                title: function () {
                    return trim(this.querySelector('.tooltiptext')?.innerHTML);
                },
                template: template,
                html: true
            });
            oTooltip.show();

            $objTooltip.data('setObjTooltip', true);
        });
    });

}
