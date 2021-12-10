import { unwrap_any } from '@util/unwrap_any';


declare const leadership: number;
declare const fullLeadership: number;
declare const currentCrewType: number;
declare const currentCrew: number;
//declare const currentGold: number;
declare const is모병 = false;
function calc(id: number) {
    const $obj = $(`#crewType${id}`);
    const crew = parseInt(unwrap_any<string>($obj.find('.form_double').val()));
    const baseCost = $obj.data('cost');
    const $cost = $obj.find('.form_cost');

    let cost = crew * baseCost;
    if (is모병) {
        cost *= 2;
    }
    $cost.val(Math.round(cost));
}

export function recruitCrewForm(): void {
    const $formAmount = $('#amount');
    const $formCrewtype = $('#crewType');
    $('.form_double').on('keyup change', function (e) {
        const $this = $(this);
        const $parent = $this.parents('.input_form');
        const crewtype = parseInt($parent.data('crewtype'));
        calc(crewtype);
        $formCrewtype.val(crewtype);
        $formAmount.val(parseFloat(unwrap_any<string>($this.val())) * 100);

        if (e.which === 13) {
            void window.submitAction();
        }
        return false;
    });

    $('.btn_half').on('click', function () {
        const $this = $(this);
        const $parent = $this.closest('.input_form');
        const crewtype = parseInt($parent.data('crewtype'));
        const $input = $parent.find('.form_double:eq(0)');

        const fillValue = Math.round(leadership / 2);
        $formCrewtype.val(crewtype);
        $input.val(fillValue).change();
        return false;
    });

    $('.btn_fill').on('click', function () {
        const $this = $(this);
        const $parent = $this.closest('.input_form');
        const crewtype = parseInt($parent.data('crewtype'));
        const $input = $parent.find('.form_double:eq(0)');

        let fillValue = Math.ceil((leadership * 100 - currentCrew) / 100);
        if (crewtype != currentCrewType) {
            fillValue = leadership;
        }
        $formCrewtype.val(crewtype);
        $input.val(fillValue).change();
        return false;
    });

    $('.btn_full').on('click', function () {
        const $this = $(this);
        const $parent = $this.closest('.input_form');
        const crewtype = parseInt($parent.data('crewtype'));
        const $input = $parent.find('.form_double:eq(0)');

        const fillValue = fullLeadership + 15;
        $formCrewtype.val(crewtype);
        $input.val(fillValue).change();
        return false;
    });

    $('.submit_btn').on('click', function () {
        const $this = $(this);
        const $parent = $this.closest('tr').find('.input_form');
        const crewtype = parseInt($parent.data('crewtype'));
        const $input = $parent.find('.form_double');

        $formCrewtype.val(crewtype);
        $formAmount.val(parseFloat(unwrap_any<string>($input.val())) * 100);

        void window.submitAction();
    });

    $('.btn_fill').click();

    $('#show_unavailable_troops').change(function () {
        const show = $('#show_unavailable_troops').is(":checked");
        if (show) {
            $('.show_default_false').show();
        }
        else {
            $('.show_default_false').hide();
        }
    });
    $('.show_default_false').hide();
}