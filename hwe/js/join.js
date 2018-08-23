
jQuery(function($){
    var $leader = $('#leader');
    var $power = $('#power');
    var $intel = $('#intel');
    
    function abilityRand(){
        var leader = Math.random()*65 + 10;
        var power = Math.random()*65 + 10;
        var intel = Math.random()*65 + 10;
        var rate = leader + power + intel;
    
        leader = Math.floor(leader / rate * defaultStatTotal);
        power = Math.floor(power / rate * defaultStatTotal);
        intel = Math.floor(intel / rate * defaultStatTotal);
        
    
        while(leader+power+intel < defaultStatTotal){
            leader+=1;
        }
        
        if(leader > defaultStatMax || power > defaultStatMax || intel > defaultStatMax || leader < defaultStatMin || power < defaultStatMin || intel < defaultStatMin){
            return abilityRand();
        }
    
        $leader.val(leader);
        $power.val(power);
        $intel.val(intel);
    }
    
    
    function abilityLeadpow(){
        var leader = Math.random() * 6;
        var power = Math.random() * 6;
        var intel = Math.random() * 1;
        var rate = leader + power + intel;
    
        leader = Math.floor(leader / rate * defaultStatTotal);
        power = Math.floor(power / rate * defaultStatTotal);
        intel = Math.floor(intel / rate * defaultStatTotal);
        
        while(leader+power+intel < defaultStatTotal){
            power+=1;
        }
        
        if(intel < defaultStatMin){
            leader -= defaultStatMin - intel;
            intel = defaultStatMin;
        }
        
        if(leader > defaultStatMax){
            power += leader - defaultStatMax;
            leader = defaultStatMax;
        }
        
        if(power > defaultStatMax){
            leader += power - defaultStatMax;
            power = defaultStatMax;
        }

        if(leader > defaultStatMax){
            intel += leader - defaultStatMax;
            leader = defaultStatMax;
        }
    
        $leader.val(leader);
        $power.val(power);
        $intel.val(intel);
    }
    
    function abilityLeadint(){
        var leader = Math.random() * 6;
        var power = Math.random() * 1;
        var intel = Math.random() * 6;
        var rate = leader + power + intel;
    
        leader = Math.floor(leader / rate * defaultStatTotal);
        power = Math.floor(power / rate * defaultStatTotal);
        intel = Math.floor(intel / rate * defaultStatTotal);
    
        while(leader+power+intel < defaultStatTotal){
            intel+=1;
        }
    
        if(power < defaultStatMin){
            leader -= defaultStatMin - power;
            power = defaultStatMin;
        }
        
        if(leader > defaultStatMax){
            intel += leader - defaultStatMax;
            leader = defaultStatMax;
        }
        
        if(intel > defaultStatMax){
            leader += intel - defaultStatMax;
            intel = defaultStatMax;
        }

        if(leader > defaultStatMax){
            power += leader - defaultStatMax;
            leader = defaultStatMax;
        }
    
        $leader.val(leader);
        $power.val(power);
        $intel.val(intel);
    }
    
    function abilityPowint(){
        var leader = Math.random() * 1;
        var power = Math.random() * 6;
        var intel = Math.random() * 6;
        var rate = leader + power + intel;
    
        leader = Math.floor(leader / rate * defaultStatTotal);
        power = Math.floor(power / rate * defaultStatTotal);
        intel = Math.floor(intel / rate * defaultStatTotal);
    
        while(leader+power+intel < defaultStatTotal){
            intel+=1;
        }
    
        if(leader < defaultStatMin){
            power -= defaultStatMin - leader;
            leader = defaultStatMin;
        }
        
        if(power > defaultStatMax){
            intel += power - defaultStatMax;
            power = defaultStatMax;
        }
        
        if(intel > defaultStatMax){
            power += intel - defaultStatMax;
            intel = defaultStatMax;
        }

        if(power > defaultStatMax){
            leader += power - defaultStatMax;
            power = defaultStatMax;
        }
    
        $leader.val(leader);
        $power.val(power);
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

    $('#join_form').submit(function(){
        var currentStatTotal = parseInt($leader.val()) + parseInt($power.val()) + parseInt($intel.val());
        if(currentStatTotal < defaultStatTotal){
            if(!confirm('현재 능력치 총합은 {0}으로, {1}보다 낮습니다. 장수 생성을 진행할까요?'.format(currentStatTotal, defaultStatTotal))){
                return false;
            }
        }
        return true;
    });
});