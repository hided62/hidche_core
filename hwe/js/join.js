
jQuery(function($){

    var totalAbil = 150;
    var $leader = $('#leader');
    var $power = $('#power');
    var $intel = $('#intel');
    
    function abilityRand(){
        var leader = Math.random()*65 + 10;
        var power = Math.random()*65 + 10;
        var intel = Math.random()*65 + 10;
        var rate = leader + power + intel;
    
        leader = Math.floor(leader / rate * totalAbil);
        power = Math.floor(power / rate * totalAbil);
        intel = Math.floor(intel / rate * totalAbil);
        
    
        while(leader+power+intel < totalAbil){
            leader+=1;
        }
        
        if(leader > 75 || power > 75 || intel > 75 || leader < 10 || power < 10 || intel < 10){
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
    
        leader = Math.floor(leader / rate * totalAbil);
        power = Math.floor(power / rate * totalAbil);
        intel = Math.floor(intel / rate * totalAbil);
        
        while(leader+power+intel < totalAbil){
            power+=1;
        }
        
        if(intel < 10){
            leader -= 10 - intel;
            intel = 10;
        }
        
        if(leader > 75){
            power += leader - 75;
            leader = 75;
        }
        
        if(power > 75){
            leader += power - 75;
            power = 75;
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
    
        leader = Math.floor(leader / rate * totalAbil);
        power = Math.floor(power / rate * totalAbil);
        intel = Math.floor(intel / rate * totalAbil);
    
        while(leader+power+intel < totalAbil){
            intel+=1;
        }
    
        if(power < 10){
            leader -= 10 - power;
            power = 10;
        }
        
        if(leader > 75){
            intel += leader - 75;
            leader = 75;
        }
        
        if(intel > 75){
            leader += intel - 75;
            intel = 75;
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
    
        leader = Math.floor(leader / rate * totalAbil);
        power = Math.floor(power / rate * totalAbil);
        intel = Math.floor(intel / rate * totalAbil);
    
        while(leader+power+intel < totalAbil){
            intel+=1;
        }
    
        if(leader < 10){
            power -= 10 - leader;
            leader = 10;
        }
        
        if(power > 75){
            intel += power - 75;
            power = 75;
        }
        
        if(intel > 75){
            power += intel - 75;
            intel = 75;
        }
    
        $leader.val(leader);
        $power.val(power);
        $intel.val(intel);
    }
    
    window.abilityRand = abilityRand;
    window.abilityLeadpow = abilityLeadpow;
    window.abilityLeadint = abilityLeadint;
    window.abilityPowint = abilityPowint;
    });