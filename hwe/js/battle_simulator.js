jQuery(function($){


    var $generalForm = $('.form_sample .general_detail');
    var $defenderHeaderForm = $('.form_sample .card-header');
    var $defenderColumn = $('.defender-column');

    var defenderNoList = {};

    var $attackerCard = $('.attacker_form');

    var initBasicEvent = function(){

        $('.form_injury').change(function(){
            var $this = $(this);
            var $general = getGeneralDetail($this);
            var $helptext = $general.find('.injury_helptext');

            var injury = parseInt($this.val());
            //FIXME: PHP 코드와 항상 일치하도록 변경
            var text = '건강';
            var color = 'white';
            if(injury > 60){
                text = '위독';
                color = 'red';
            }
            else if(injury > 40){
                text = '심각';
                color = 'magenta';
            }
            else if(injury > 20){
                text = '중상';
                color = 'orange';
            }
            else if(injury > 0){
                text = '경상';
                color = 'yellow';
            }
            $helptext.html(text).css('color', color);
        });

        $('.export_general').click(function(){
            var $btn = $(this);
            var $general = getGeneralDetail($btn);
            
            var values = exportGeneralInfo($general);
            console.log(values);
        });
        $('.delete-defender').click(function(){
            var $card = getGeneralFrame($(this));
            deleteDefender($card);
        });
        $('.copy-defender').click(function(){
            var $card = getGeneralFrame($(this));
            copyDefender($card);
        });

        $('.add-defender').click(function(){
            addDefender();
        });

        $('.btn-general-load').click(function(){
            var $file = $(this).prev();
            $file[0].click();
        })

        $('.form_load_general_file').on('change', function(e){
            e.preventDefault();
            var $this = $(this);
            var $card = getGeneralFrame($this);

            var files = e.target.files;

            importGeneralInfoByFile(files, $card);
            return false;
        });

        $('.btn-general-save').click(function(){
            var $this = $(this);
            var $general = getGeneralDetail($this);
            var generalData = exportGeneralInfo($general);

            var filename = "general_{0}.json".format(generalData.name);
            var saveData = JSON.stringify({
                objType:'general',
                data:generalData
            }, null, 4);

            download(saveData, filename, 'application/json');
        });

        var $generals = $('.general_detail');
        $generals.bind('dragover dragleave', function(e) {
            e.stopPropagation()
            e.preventDefault()
        })
        $generals.bind('drop', function(e){
            e.preventDefault();
            var $this = $(this);
            var $card = getGeneralFrame($this);

            var files = e.originalEvent.dataTransfer.files;

            importGeneralInfoByFile(files, $card);
            return false;
        });
    }

    var importGeneralInfo = function($general, data){
        var setVal = function(query, val){
            $general.find(query).val(val).change();
        }

        setVal('.form_general_name', data.name);

        setVal('.form_general_level', data.level);
        setVal('.form_exp_level', data.explevel);
        setVal('.form_injury', data.injury);

        setVal('.form_leadership', data.leader);
        setVal('.form_general_horse', data.horse);
        setVal('.form_power', data.power);
        setVal('.form_general_weap', data.weap);
        setVal('.form_intel', data.intel);
        setVal('.form_general_book', data.book);
        setVal('.form_general_item', data.item);

        setVal('.form_injury', data.injury);

        setVal('.form_rice', data.rice);

        setVal('.form_general_character', data.personal);
        setVal('.form_general_special_war', data.special2);

        setVal('.form_crew', data.crew);
        setVal('.form_crewtype', data.crewtype);
        setVal('.form_atmos', data.atmos);
        setVal('.form_train', data.train);

        setVal('.form_dex0', data.dex0);
        setVal('.form_dex10', data.dex10);
        setVal('.form_dex20', data.dex20);
        setVal('.form_dex30', data.dex30);
        setVal('.form_dex40', data.dex40);
        setVal('.form_defend_mode', data.mode);

        if(!setGeneralNo($general, data.no)){
            setGeneralNo($general, generateNewGeneralNo());
        }
    }

    var exportGeneralInfo = function($general){
        var getInt = function(query){
            return parseInt($general.find(query).val());
        }

        var getVal = function(query){
            return $general.find(query).val();
        }

        return {
            no:getGeneralNo($general),
            name:getVal('.form_general_name'),
            level:getInt('.form_general_level'),
            explevel:getInt('.form_exp_level'),

            leader:getInt('.form_leadership'),
            horse:getInt('.form_general_horse'),
            power:getInt('.form_power'),
            weap:getInt('.form_general_weap'),
            intel:getInt('.form_intel'),
            book:getInt('.form_general_book'),
            item:getInt('.form_general_item'),

            injury:getInt('.form_injury'),

            rice:getInt('.form_rice'),

            personal:getInt('.form_general_character'),
            special2:getInt('.form_general_special_war'),

            crew:getInt('.form_crew'),
            crewtype:getInt('.form_crewtype'),
            atmos:getInt('.form_atmos'),
            train:getInt('.form_train'),
            
            dex0:getInt('.form_dex0'),
            dex10:getInt('.form_dex10'),
            dex20:getInt('.form_dex20'),
            dex30:getInt('.form_dex30'),
            dex40:getInt('.form_dex40'),
            mode:getInt('.form_defend_mode'),
        };
    }

    var importGeneralInfoByFile = function(files, $card){
        if($card === undefined){
            $card = addDefender();
        }

        if(files === null){
            alert('파일 에러!');
            return false;
        }

        if(files.length < 1){
            alert("파일 에러!");
            return false;
        }


        var file = files[0];
        if(file.size > 1024*1024){
            alert('파일이 너무 큽니다!');
            return false;
        }
        if(file.type === ''){
            alert('폴더를 업로드할 수 없습니다!');
            return false;
        }
        
        var reader = new FileReader();
        reader.onload = function(){
            var generalData = {};
            try {
                generalData = JSON.parse(reader.result);
            } catch(e) {
                alert('올바르지 않은 파일 형식입니다');
                return false;
            }

            if(!('objType' in generalData)){
                alert('파일 형식을 확인할 수 없습니다');
                return false;
            }

            if(generalData.objType != 'general'){
                alert('장수 데이터가 아닙니다');
                return false;
            }

            $card.find('.form_load_general_file').val('');

            importGeneralInfo($card, generalData.data);
            return true;
        };

        try {

            reader.readAsText(file);
        }
        catch(e) {
            alert('파일을 읽는데 실패했습니다.');
            return false;
        }

        return true;
    }

    var exportGeneralInfoForDB = function($general, idx){
        var retVal = exportGeneralInfo($general);

        var dbVal = {
            turntime:'2018-08-26 12:00',
            leader2:0,
            power2:0,
            intel2:0,
            
            gold:10000,

            dedication:0,
            warnum:10,
            killnum:4,
            deathnum:4,

            killcrew:20000,
            deathcrew:20000,
            recwar:'SUCCESS',
            experience:Math.pow(retVal.explevel, 2),
        };

        if(idx <= 0){
            dbVal['name'] = '출병자';
            dbVal['nation'] = 1;
        }
        else{
            dbVal['name'] = '수비자{0}'.format(idx);
            dbVal['nation'] = 2;
        }

        return $.merge(retVal, defaultVal);
    }

    var getGeneralFrame = function($btn){
        var $card = $btn.closest('.general_form');
        return $card;
    }

    var getGeneralDetail = function($btn){
        var $card = getGeneralFrame($btn);
        var $general = $card.find('.general_detail');
        return $general;
    }

    var getGeneralNo = function($btn){
        return parseInt(getGeneralFrame($btn).data('general_no'));
    }

    var setGeneralNo = function($btn, value){
        if(value == 1){
            //1번은 무조건 공격자임
            return false; 
        }
        if(value in defenderNoList){
            return false;
        }
        var $card = getGeneralFrame($btn);
        $card.data('general_no', value);
        defenderNoList[value] = $card;
        return true;
    }

    var generateNewGeneralNo = function(){
        while(true){
            var newGeneralNo = parseInt(Math.random()*(1<<24))+2;
            if(newGeneralNo in defenderNoList){
                continue;
            }
            return newGeneralNo;
        }
    }

    var deleteGeneralNo = function($btn){
        var $card = getGeneralFrame($btn);
        $card.removeData('general_no');
        var generalNo = getGeneralNo($card);
        delete defenderNoList[generalNo];
    }

    var addDefender = function($cardAfter){
        var $newCard = $('<div class="card mb-2 defender_form general_form"></div>');

        if($cardAfter === undefined){
            $defenderColumn.append($newCard);
        }
        else{
            $cardAfter.after($newCard);
        }
        
        $newCard.append($defenderHeaderForm.clone(true,true));
        $newCard.append($generalForm.clone(true,true));

        $newGeneral = getGeneralDetail($newCard);
        setGeneralNo($newCard, generateNewGeneralNo());

        return $newCard;
    }

    var deleteDefender = function($card){
        deleteGeneralNo($card);
        $card.detach();
    }

    var copyDefender = function($card){
        var $general = getGeneralDetail($card);

        var generalData = exportGeneralInfo($general);
        var $newObj = addDefender($card);
        importGeneralInfo(getGeneralDetail($newObj), generalData);
    }

    initBasicEvent();
    $attackerCard.append($generalForm.clone(true,true));
    addDefender();
    
});