

function submitArticle(){
    var $article = $('#newArticle');
    var $title = $article.find('.titleInput');
    var $text = $article.find('.contentInput');
    var title = $.trim($title.val());
    var text = $.trim($text.val());


    if(!text && !title){
        return false;
    }

    $title.val('');
    $text.val('');

    $.post({
        url:'j_board_article_add.php',
        dataType:'json',
        data:{
            isSecret:isSecretBoard,
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
    
    var $article = $this.closest('.articleObj').eq(0);
    var articleNo = $article.data('no');
    var $text = $article.find('input.commentText');
    var text = $.trim($text.val());

    if(!text){
        return false;
    }

    $text.val('');

    $.post({
        url:'j_board_comment_add.php',
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

function drawArticle(idx, articleObj){
    var $articleFrame = $('#articleTemplate > .articleFrame');
    var $commentFrame = $($('#commentTemplate').prop('content'));

    var $article = $articleFrame.clone();
    $article.addClass('articleObj')
        .data('no', articleObj.no)
        .data('author', articleObj.general_no);

    
    $article.find('.articleNo').text(articleObj.no);
    $article.find('.authorName').text(articleObj.author);
    $article.find('.articleTitle').text(articleObj.title);
    $article.find('.date').text(articleObj.date);
    if(articleObj.author_icon){
        $article.find('.authorIcon').attr('src', articleObj.author_icon);
    }
    //$article.find('.text').text(articleObj.text);
    $article.find('.text').html(nl2br(escapeHtml(articleObj.text)));
    //TODO: 바꿀 것

    var $articleComment = $article.find('.commentList');
    
    $.each(articleObj.comment, function(_, commentObj){
        var $comment = $commentFrame.clone();
        $comment.find('.author').text(commentObj.author);
        //$comment.find('.text').text(commentObj.text);
        $comment.find('.text').html(nl2br(escapeHtml(commentObj.text)));
        $comment.find('.date').text(commentObj.date);
        $articleComment.append($comment);
    });

    $article.find('.submitComment').click(submitComment);
    $article.find('.commentText').on('keypress', function (e) {
        if(e.which === 13){
            $article.find('.submitComment').click();
        }
    });
    $article.find('.inputCommentHeader').click(function(){
        $article.find('.commentText').focus();
    })

    var $board = $('#board');

    $board.prepend($article);
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
        url:'j_board_get_articles.php',
        dataType:'json',
        data:{
            isSecret:isSecretBoard, //첫 버전이니까 전체 다 불러오자
        }
    });
}

function resizeTextarea($obj){
    $obj.height(1).height($obj.prop('scrollHeight')+12 );
}

$(function(){
    $('textarea.autosize').on('keydown keyup', function(){
        resizeTextarea($(this));
    })

$('#submitArticle').click(submitArticle);

loadArticles()
.then(drawArticles, errUnknown)
.fail(function(reason){
    alert(reason);
});

});