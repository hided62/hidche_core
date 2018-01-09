var Popup_callback = function() {};
var Popup_waitTimer;

function Popup_Import() {
}

function Popup_Init() {
    $("#Popup_0102").click(Popup_AlertOK);

    $("#Popup_0202").click(Popup_ConfirmYes);
    $("#Popup_0203").click(Popup_ConfirmNo);

    $("#Popup_0303").click(Popup_WaitHide);
}

function Popup_Update() {
}

function Popup_Alert(msg, callback) {
    $("#Popup_0101").text(msg);

    Popup_callback = callback;

    Popup_AlertShow();

    // 익스플로러 버그로 인해 딜레이 줌
    setTimeout(function() { $("#Popup_0102").focus(); }, 10);
}

function Popup_AlertOK() {
    if(Popup_callback) {
        Popup_callback();
        Popup_callback = "";
    }

    Popup_AlertHide();
}

function Popup_AlertShow() {
    $("#Popup_00").show();
    $("#Popup_01").show();
}

function Popup_AlertHide() {
    $("#Popup_00").hide();
    $("#Popup_01").hide();
}

function Popup_Confirm(msg, callback) {
    $("#Popup_0201").text(msg);

    Popup_callback = callback;

    Popup_ConfirmShow();

    // 익스플로러 버그로 인해 딜레이 줌
    setTimeout(function() { $("#Popup_0203").focus(); }, 10);
}

function Popup_ConfirmYes() {
    Popup_ConfirmHide();

    if(Popup_callback) {
        Popup_callback();
        Popup_callback = "";
    }
}

function Popup_ConfirmNo() {
    Popup_callback = "";

    Popup_ConfirmHide();
}

function Popup_ConfirmShow() {
    $("#Popup_00").show();
    $("#Popup_02").show();
}

function Popup_ConfirmHide() {
    $("#Popup_00").hide();
    $("#Popup_02").hide();
}

function Popup_Wait(callback) {
    $("#Popup_0301").text("처리중입니다.");
    Popup_waitTimer = setInterval(Popup_WaitInvalidate, 1000);

    $("#Popup_00").show();
    $("#Popup_03").show();

    callback();
}

function Popup_WaitShow(msg, callback) {
    clearTimeout(Popup_waitTimer);

    Popup_callback = callback;

    $("#Popup_0301").text(msg);
    $("#Popup_0303").show();

    // 익스플로러 버그로 인해 딜레이 줌
    setTimeout(function() { $("#Popup_0303").focus(); }, 10);
}

function Popup_WaitHide() {
    clearTimeout(Popup_waitTimer);

    if(Popup_callback) {
        Popup_callback();
        Popup_callback = "";
    }

    $("#Popup_0303").hide();
    $("#Popup_00").hide();
    $("#Popup_03").hide();
}

function Popup_WaitInvalidate() {
    var waitMsg = $("#Popup_0302").text();

    if(waitMsg.length >= 5) {
        waitMsg = "";
    }

    $("#Popup_0302").text(waitMsg+".");
}
