
import axios from 'axios';
import $ from 'jquery';
import { trim } from 'lodash';
import { escapeHtml, nl2br } from './common_legacy';
import { InvalidResponse } from './defs';
import { convertFormData } from './util/convertFormData';
import { setAxiosXMLHttpRequest } from './util/setAxiosXMLHttpRequest';
import { unwrap_any } from './util/unwrap_any';

declare const isSecretBoard: boolean;

type BoardResponse = {
    result: true,
    articles: Record<number, BoardArticle>,
}

type BoardArticle = {
    no: number,
    nation_no: number,
    is_secret?: boolean,
    date: string,
    general_no: number,
    author: string,
    author_icon: string,
    title: string,
    text: string,
    comment: BoardComment[],
}

type BoardComment = {
    no: number,
    nation_no: number,
    is_secret?: boolean,
    date: string,
    document_no: number,
    general_no: number,
    author: string,
    text: string,
}

async function submitArticle(e: JQuery.Event) {
    e.preventDefault();

    const $article = $('#newArticle');
    const $title = $article.find('.titleInput');
    const $text = $article.find('.contentInput');
    const title = trim(unwrap_any<string>($title.val()));
    const text = trim(unwrap_any<string>($text.val()));


    if (!text && !title) {
        return false;
    }

    let result: InvalidResponse;

    try {
        const response = await axios({
            url: 'j_board_article_add.php',
            method: 'post',
            responseType: 'json',
            data: convertFormData({
                isSecret: isSecretBoard,
                title: title,
                text: text
            })
        });
        result = response.data;
    }
    catch(e){
        console.error(e);
        alert(`실패했습니다. :${e}`);
        return;
    }

    if(!result.result){
        alert(`글을 올리는데 실패했습니다. ${result.reason}`);
        return;
    }

    $title.val('');
    $text.val('');

    void loadArticles();
}

async function submitComment(this: HTMLElement, e: JQuery.Event) {
    e.preventDefault();

    const $this = $(this);

    const $article = $this.closest('.articleObj').eq(0);
    const articleNo = $article.data('no');
    const $text = $article.find('input.commentText');
    const text = trim(unwrap_any<string>($text.val()));

    if (!text) {
        return;
    }


    let result: InvalidResponse;


    try {
        const response = await axios({
            url: 'j_board_comment_add.php',
            method: 'post',
            responseType: 'json',
            data: convertFormData({
                articleNo: articleNo,
                text: text
            })
        });
        result = response.data;
    }
    catch(e){
        console.error(e);
        alert(`실패했습니다. :${e}`);
        return;
    }

    if(!result.result){
        alert(`댓글을 다는데 실패했습니다. ${result.reason}`);
        return;
    }

    $text.val('');

    void loadArticles();

    return false;
}

function drawArticle(articleObj: BoardArticle) {
    const $articleFrame = $('#articleTemplate > .articleFrame');
    const $commentFrame = $($('#commentTemplate').prop('content'));

    const $article = $articleFrame.clone();
    $article.addClass('articleObj')
        .data('no', articleObj.no)
        .data('author', articleObj.general_no);


    $article.find('.articleNo').text(articleObj.no);
    $article.find('.authorName').text(articleObj.author);
    $article.find('.articleTitle').text(articleObj.title);
    $article.find('.date').text(articleObj.date);
    if (articleObj.author_icon) {
        $article.find('.authorIcon').attr('src', articleObj.author_icon);
    }
    //$article.find('.text').text(articleObj.text);
    $article.find('.text').html(nl2br(escapeHtml(articleObj.text)));
    //TODO: 바꿀 것

    const $articleComment = $article.find('.commentList');

    for (const commentObj of articleObj.comment) {
        const $comment = $commentFrame.clone();
        $comment.find('.author').text(commentObj.author);
        //$comment.find('.text').text(commentObj.text);
        $comment.find('.text').html(nl2br(escapeHtml(commentObj.text)));
        $comment.find('.date').text(commentObj.date);
        $articleComment.append($comment);
    }

    $article.find('.submitComment').on('click', submitComment);
    $article.find('.commentText').on('keypress', function (e) {
        if (e.which === 13) {
            $article.find('.submitComment').trigger('click');
        }
    });
    $article.find('.inputCommentHeader').click(function () {
        $article.find('.commentText').trigger('focus');
    })

    const $board = $('#board');

    $board.prepend($article);
}

function drawArticles(articlesObj: BoardResponse | InvalidResponse) {
    if (!articlesObj.result) {
        alert(`에러: ${articlesObj.reason}`);
        return;
    }

    $('.articleObj').detach();//첫 버전이니까 일괄 삭제 일괄 로드
    for (const article of Object.values(articlesObj.articles)) {
        drawArticle(article);
    }
}



async function loadArticles() {
    try {
        const response = await axios({
            url: 'j_board_get_articles.php',
            responseType: 'json',
            method: 'post',
            data: convertFormData({
                isSecret: isSecretBoard
            })
        });
        drawArticles(response.data);
    }
    catch (e) {
        console.error(e);
        alert(`에러: ${e}`);
        return;
    }
    return $.post({
        url: 'j_board_get_articles.php',
        dataType: 'json',
        data: {
            isSecret: isSecretBoard, //첫 버전이니까 전체 다 불러오자
        }
    });
}

function resizeTextarea($obj: JQuery<HTMLElement>) {
    $obj.height(1).height($obj.prop('scrollHeight') + 12);
}

$(function () {
    setAxiosXMLHttpRequest();

    $('textarea.autosize').on('keydown keyup', function () {
        resizeTextarea($(this));
    })

    $('#submitArticle').on('click', submitArticle);

    void loadArticles();

});