jQuery(function($){
    $('.submitBtn').click(function(){
        var $this = $(this);
        var target = parseInt($this.data('target'));
        var amount = $('#target_{0}'.format(target)).val();

        $.post({
            url:'j_betting.php',
            dataType:'json',
            data:{
                target:target,
                amount:amount
            }
        }).then(function(data){
            if(!data){
                return quickReject('베팅을 실패했습니다.');
            }
            if(!data.result){
                return quickReject('베팅을 실패했습니다. : '+data.reason);
            }
            
            location.reload();
    
        }, errUnknown)
        .fail(function(reason){
            alert(reason);
            location.reload();
        });
        return false;
    });
});