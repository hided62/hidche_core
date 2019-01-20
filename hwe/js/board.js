

function submitArticle(){
    var $article = $('#newArticle');
    var $title = $article.find('input.titleInput');
    var $text = $article.find('input.contentInput');
    var title = $.trim($title.val());
    var text = $.trim($text.val());


    if(!text && !title){
        return false;
    }

    $title.val('');
    $text.val('');

    $.post({
        url:'j_add_board_article.php',
        dataType:'json',
        data:{
            title:title,
            text:text
        }
    }).then(function(data){
        if(!data){
            $title.val(title);
            $text.val(text);
            alert()
            return quickReject('글을 올리는데 실패했습니다.');
        }
        if(!data.result){
            $title.val(title);
            $text.val(text);
            return quickReject('글을 올리는데 실패했습니다. : '+data.reason);
        }

        return loadArticles().done(drawArticles);

    }, errUnknown)
    .fail(function(reason){
        alert(reason);
    });

    return false;
}

function submitComment(){
    var $this = $(this);
    var $article = $this.parents('.article').eq(0);
    var articleNo = $article.data('no');
    var $text = $article.find('input.commentText');
    var text = $.trim($text.val());

    if(!text){
        return false;
    }

    $text.val('');

    $.post({
        url:'j_add_board_comment.php',
        dataType:'json',
        data:{
            articleNo:articleNo,
            text:text
        }
    }).then(function(data){
        if(!data){
            $text.val(text);
            alert()
            return quickReject('댓글을 다는데 실패했습니다.');
        }
        if(!data.result){
            $text.val(text);
            return quickReject('댓글을 다는데 실패했습니다. : '+data.reason);
        }

        return loadArticles().done(drawArticles);

    }, errUnknown)
    .fail(function(reason){
        alert(reason);
    });

    return false;
}

function drawArticle(articleObj){
    var $articleFrame = $('#articleTemplate > .articleFrame');
    var $commentFrame = $('#commentTemplate > .comment');

    var $article = $articleFrame.clone();
    $article.addClass('articleObj')
        .data('no', articleObj.no)
        .data('author', articleObj.general_no);

    $article.find('.author').text(articleObj.author);
    $article.find('.title').text(articleObj.title);
    $article.find('.date').text(articleObj.date);
    //$article.find('.text').text(articleObj.text);
    $article.find('.text').html(nl2br(escapeHtml(articleObj.text)));
    //TODO: 바꿀 것

    var $articleComment = $article.find('ul.commentList');
    
    $.each(articleObj.comment, function(commentObj){
        var $comment = $commentFrame.clone();
        $comment.find('.author').text(commentObj.author);
        //$comment.find('.text').text(commentObj.text);
        $comment.find('.text').html(nl2br(escapeHtml(commentObj.text)));
        $comment.find('.date').text(commentObj.date);
        $articleComment.append($comment);
    });

    $('#board').append($article);
}

function drawArticles(articlesObj){
    var deferred = $.Deferred();
    if(!articlesObj){
        return quickReject('받아오는데 실패했습니다.');
    }
    if(!articlesObj.result){
        return quickReject('에러가 발생했습니다. : '+articlesObj.reason);
    }

    $('.articleObj').detach();//첫 버전이니까 일괄 삭제 일괄 로드
    $.each(articlesObj.articles,  drawArticle);
    return true;
}



function loadArticles(){
    return $.post({
        url:'j_get_board_articles.php',
        dataType:'json',
        data:{
            isSecretBoard:isSecretBoard, //첫 버전이니까 전체 다 불러오자
        }
    });
}

$(function(){

$('#submitArticle').click(submitArticle);
$('.submitComment').click(submitComment);

loadArticles()
.then(drawArticles, errUnknown)
.fail(function(reason){
    alert(reason);
});

});