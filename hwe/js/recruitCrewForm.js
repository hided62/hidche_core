window.calc = function(id) {
    var $obj = $('#crewType{0}'.format(id));
    var crew = $obj.find('.form_double').val();
    var baseCost = $obj.data('cost');
    var $cost = $obj.find('.form_cost');

    var cost = crew * baseCost;
    if(window.is모병){
        cost *= 2;
    }
    $cost.val(Math.round(cost));
}

$(function(){

    
    var $postForm = $('#post_form');
    var $formAmount = $('#amount');
    var $formCrewtype = $('#crewType');
    $('.form_double').on('keyup change',function(e){
        var $this = $(this);
        var $parent = $this.parents('.input_form');
        var crewtype = parseInt($parent.data('crewtype'));
        calc(crewtype);
        $formCrewtype.val(crewtype);
        $formAmount.val($this.val());

        if(e.which === 13){
            submitAction();
        }
        return false;
    });

    $('.btn_half').click(function(){
        var $this = $(this);
        var $parent = $this.closest('.input_form');
        var crewtype = parseInt($parent.data('crewtype'));
        var $input = $parent.find('.form_double:eq(0)');

        var fillValue = Math.round(leadership / 2);
        $formCrewtype.val(crewtype);
        $input.val(fillValue).change();
        return false;
    });

    $('.btn_fill').click(function(){
        var $this = $(this);
        var $parent = $this.closest('.input_form');
        var crewtype = parseInt($parent.data('crewtype'));
        var $input = $parent.find('.form_double:eq(0)');

        var fillValue = Math.ceil((leadership*100 - currentCrew)/100);
        if(crewtype != currentCrewType){
            fillValue = leadership;
        }
        $formCrewtype.val(crewtype);
        $input.val(fillValue).change();
        return false;
    });

    $('.btn_full').click(function(){
        var $this = $(this);
        var $parent = $this.closest('.input_form');
        var crewtype = parseInt($parent.data('crewtype'));
        var $input = $parent.find('.form_double:eq(0)');

        var fillValue = fullLeadership + 15;
        $formCrewtype.val(crewtype);
        $input.val(fillValue).change();
        return false;
    });

    $('.submit_btn').click(function(){
        var $this = $(this);
        var $parent = $this.closest('tr').find('.input_form');
        var crewtype = parseInt($parent.data('crewtype'));
        var $input = $parent.find('.form_double');

        $formCrewtype.val(crewtype);
        $formAmount.val($input.val()*100);

        submitAction();
    });

    $('.btn_fill').click();

    $('#show_unavailable_troops').change(function(){
        var show = $('#show_unavailable_troops').is(":checked");
        if(show){
            $('.show_default_false').show();
        }
        else{
            $('.show_default_false').hide();
        }
    });
    $('.show_default_false').hide();
});