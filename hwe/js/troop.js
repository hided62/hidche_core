jQuery(function($){
    //btnJoinTroop, btnLeaveTroop, btnKickTroop, btnCreateTroop, btnChangeTroopName
    $('#btnLeaveTroop').click(function(e){
        if(!confirm("정말 부대를 탈퇴하시겠습니까?")){
            return false;
        }
        $.post({
            url:'j_troop.php',
            dataType:'json',
            data:{
                action:'부대탈퇴'
            }
        }).then(function(data){
            console.log(data);
            if(!data.result){
                alert(data.reason);
                location.reload();
            }
    
            location.reload();
    
        }, errUnknown);
        return false;
    });

    $('#btnCreateTroop').click(function(e){
        $.post({
            url:'j_troop.php',
            dataType:'json',
            data:{
                action:'부대창설',
                name:$('#nameplate').val()
            }
        }).then(function(data){
            console.log(data);
            if(!data.result){
                alert(data.reason);
                location.reload();
            }
    
            location.reload();
    
        }, errUnknown);
        return false;
    });

    $('#btnChangeTroopName').click(function(e){
        $.post({
            url:'j_troop.php',
            dataType:'json',
            data:{
                action:'부대변경',
                name:$('#nameplate').val()
            }
        }).then(function(data){
            console.log(data);
            if(!data.result){
                alert(data.reason);
                location.reload();
            }
    
            location.reload();
    
        }, errUnknown);
        return false;
    });

    $('#btnKickTroop').click(function(e){
        $.post({
            url:'j_troop.php',
            dataType:'json',
            data:{
                action:'부대추방',
                gen:$('#genNo').val()
            }
        }).then(function(data){
            console.log(data);
            if(!data.result){
                alert(data.reason);
                location.reload();
            }
    
            location.reload();
    
        }, errUnknown);
        return false;
    });

    $('#btnJoinTroop').click(function(e){
        $.post({
            url:'j_troop.php',
            dataType:'json',
            data:{
                action:'부대가입',
                troop:$('.troopId:checked').val()
            }
        }).then(function(data){
            console.log(data);
            if(!data.result){
                alert(data.reason);
                location.reload();
            }
    
            location.reload();
    
        }, errUnknown);
        return false;
    });

    
});