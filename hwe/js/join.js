
jQuery(function($){
    var $leadership = $('#leadership');
    var $strength = $('#strength');
    var $intel = $('#intel');
    
    function abilityRand(){
        var leadership = Math.random()*65 + 10;
        var strength = Math.random()*65 + 10;
        var intel = Math.random()*65 + 10;
        var rate = leadership + strength + intel;
    
        leadership = Math.floor(leadership / rate * defaultStatTotal);
        strength = Math.floor(strength / rate * defaultStatTotal);
        intel = Math.floor(intel / rate * defaultStatTotal);
        
    
        while(leadership+strength+intel < defaultStatTotal){
            leadership+=1;
        }
        
        if(leadership > defaultStatMax || strength > defaultStatMax || intel > defaultStatMax || leadership < defaultStatMin || strength < defaultStatMin || intel < defaultStatMin){
            return abilityRand();
        }
    
        $leadership.val(leadership);
        $strength.val(strength);
        $intel.val(intel);
    }
    
    
    function abilityLeadpow(){
        var leadership = Math.random() * 6;
        var strength = Math.random() * 6;
        var intel = Math.random() * 1;
        var rate = leadership + strength + intel;
    
        leadership = Math.floor(leadership / rate * defaultStatTotal);
        strength = Math.floor(strength / rate * defaultStatTotal);
        intel = Math.floor(intel / rate * defaultStatTotal);
        
        while(leadership+strength+intel < defaultStatTotal){
            strength+=1;
        }
        
        if(intel < defaultStatMin){
            leadership -= defaultStatMin - intel;
            intel = defaultStatMin;
        }
        
        if(leadership > defaultStatMax){
            strength += leadership - defaultStatMax;
            leadership = defaultStatMax;
        }
        
        if(strength > defaultStatMax){
            leadership += strength - defaultStatMax;
            strength = defaultStatMax;
        }

        if(leadership > defaultStatMax){
            intel += leadership - defaultStatMax;
            leadership = defaultStatMax;
        }
    
        $leadership.val(leadership);
        $strength.val(strength);
        $intel.val(intel);
    }
    
    function abilityLeadint(){
        var leadership = Math.random() * 6;
        var strength = Math.random() * 1;
        var intel = Math.random() * 6;
        var rate = leadership + strength + intel;
    
        leadership = Math.floor(leadership / rate * defaultStatTotal);
        strength = Math.floor(strength / rate * defaultStatTotal);
        intel = Math.floor(intel / rate * defaultStatTotal);
    
        while(leadership+strength+intel < defaultStatTotal){
            intel+=1;
        }
    
        if(strength < defaultStatMin){
            leadership -= defaultStatMin - strength;
            strength = defaultStatMin;
        }
        
        if(leadership > defaultStatMax){
            intel += leadership - defaultStatMax;
            leadership = defaultStatMax;
        }
        
        if(intel > defaultStatMax){
            leadership += intel - defaultStatMax;
            intel = defaultStatMax;
        }

        if(leadership > defaultStatMax){
            strength += leadership - defaultStatMax;
            leadership = defaultStatMax;
        }
    
        $leadership.val(leadership);
        $strength.val(strength);
        $intel.val(intel);
    }
    
    function abilityPowint(){
        var leadership = Math.random() * 1;
        var strength = Math.random() * 6;
        var intel = Math.random() * 6;
        var rate = leadership + strength + intel;
    
        leadership = Math.floor(leadership / rate * defaultStatTotal);
        strength = Math.floor(strength / rate * defaultStatTotal);
        intel = Math.floor(intel / rate * defaultStatTotal);
    
        while(leadership+strength+intel < defaultStatTotal){
            intel+=1;
        }
    
        if(leadership < defaultStatMin){
            strength -= defaultStatMin - leadership;
            leadership = defaultStatMin;
        }
        
        if(strength > defaultStatMax){
            intel += strength - defaultStatMax;
            strength = defaultStatMax;
        }
        
        if(intel > defaultStatMax){
            strength += intel - defaultStatMax;
            intel = defaultStatMax;
        }

        if(strength > defaultStatMax){
            leadership += strength - defaultStatMax;
            strength = defaultStatMax;
        }
    
        $leadership.val(leadership);
        $strength.val(strength);
        $intel.val(intel);
    }
    
    window.abilityRand = abilityRand;
    window.abilityLeadpow = abilityLeadpow;
    window.abilityLeadint = abilityLeadint;
    window.abilityPowint = abilityPowint;

    var $charInfoText = $('#charInfoText');
    var $selChar = $('#selChar');
    $selChar.change(function(){
        var $this = $(this);
        var char = $this.val();
        if(char in charInfoText){
            $charInfoText.html(charInfoText[char]);
        }
        else{
            $charInfoText.html('');
        }
    });

    var $generalName = $('#generalName');
    if($generalName.on('change keyup paste', function(){
        var generalName = $generalName.val();
        var len = mb_strwidth(generalName);
        if(len == 0 || len > 18){
            $generalName.css('color','red');
        }
        else{
            $generalName.css('color','white');
        }
    }));

    $('#join_form').submit(function(){
        var generalName = $generalName.val();
        if(mb_strwidth(generalName) > 18){
            alert('장수 이름이 너무 깁니다!');
            return false;
        }
        var currentStatTotal = parseInt($leadership.val()) + parseInt($strength.val()) + parseInt($intel.val());
        if(currentStatTotal < defaultStatTotal){
            if(!confirm('현재 능력치 총합은 {0}으로, {1}보다 낮습니다. 장수 생성을 진행할까요?'.format(currentStatTotal, defaultStatTotal))){
                return false;
            }
        }
        return true;
    });

    var randomGenType = Math.floor(Math.random()*7);
    if(randomGenType < 3){
        abilityLeadpow();
    }
    else if(randomGenType < 6){
        abilityLeadint();
    }
    else{
        abilityPowint();
    }

});