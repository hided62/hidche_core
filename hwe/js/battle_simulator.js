jQuery(function($){


    var $generalForm = $('.form_sample .general_detail');
    var $defenderHeaderForm = $('.form_sample .card-header');
    var $defenderColumn = $('.defender-column');

    var initBasicEvent = function(){
        $('.export_general').click(function(){
            var $this = $(this);
            var $card = $this.closest('.general_form');
            var $general = $card.find('.general_detail');
            
            var values = extractGeneralInfo($general);
            console.log(values);
        });
        //$defenderHeaderForm.find('')
    }

    var loadGeneralInfo = function($general, data){

    }

    var extractGeneralInfoForDB = function($general, idx){
        var retVal = extractGeneralInfo($general);

        var dbVal = {
            turntime:'2018-08-26 12:00',
            leader2:0,
            power2:0,
            intel2:0,
            
            dedication:0,
            warnum:10,
            killnum:4,
            deathnum:4,

            killcrew:20000,
            deathcrew:20000,
            recwar:'SUCCESS',
            experience:extInt('.form_exp_level')*extInt('.form_exp_level'),
        };

        if(idx <= 0){
            dbVal['name'] = '출병자';
            dbVal['no'] = 1;
            dbVal['nation'] = 1;
        }
        else{
            dbVal['name'] = '수비자{0}'.format(idx);
            dbVal['no'] = idx+1;
            dbVal['nation'] = 2;
        }

        return $.merge(retVal, defaultVal);
    }

    var extractGeneralInfo = function($general){
        var extInt = function(query){
            return parseInt($general.find(query).val());
        }

        return {
            level:extInt('.form_general_level'),
            explevel:extInt('.form_exp_level'),
            injury:extInt('.form_injury'),

            leader:extInt('.form_leadership'),
            horse:extInt('.form_general_horse'),
            power:extInt('.form_power'),
            weap:extInt('.form_general_weap'),
            intel:extInt('.form_intel'),
            book:extInt('.form_general_book'),
            item:extInt('.form_general_item'),

            gold:extInt('.form_gold'),
            rice:extInt('.form_rice'),

            personal:extInt('.form_general_character'),
            special2:extInt('.form_general_special_war'),

            crew:extInt('.form_crew'),
            crewtype:extInt('.form_crewtype'),
            atmos:extInt('.form_atmos'),
            train:extInt('.form_train'),
            
            dex0:extInt('.form_dex0'),
            dex10:extInt('.form_dex10'),
            dex20:extInt('.form_dex20'),
            dex30:extInt('.form_dex30'),
            dex40:extInt('.form_dex40'),
            mode:extInt('.form_defend_mode'),
        };
    }

    initBasicEvent();

    $('.defender_form').append($defenderHeaderForm.clone(true, true));
    $('.general_form').append($generalForm.clone(true, true));

    var addDefender = function(){
        var $newObj = $('<div class="card mb-2 defender_form general_form"></div>');
        $defenderColumn.append($newObj);
        $newObj.append($defenderHeaderForm.clone(true,true));
        $newObj.append($generalForm.clone(true,true));
    }

    var deleteDefender = function($this){
        var $card = $this.closest('.general_form');
        $card.detach();
    }

    $('.add-defender').click(addDefender);
    $('.delete-defender').click(function(){deleteDefender($(this));});
});