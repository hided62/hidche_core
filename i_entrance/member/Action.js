function EntranceMember_Import() {
}

function EntranceMember_Init() {
    $("#EntranceMember_0001").click(EntranceMember_Back);
    $("#EntranceMember_000201").click(EntranceMember_Order);

    $("#btn_allow_join").click(function() { EntranceMember_Post(8); });
    $("#btn_deny_join").click(function() { EntranceMember_Post(9); });
    $("#btn_allow_login").click(function() { EntranceMember_Post(10); });
    $("#btn_deny_login").click(function() { EntranceMember_Post(11); });
    $("#btn_process_withdraw").click(function() { EntranceMember_Post(12); });
    $("#btn_process_scrub_olduser").click(function() { EntranceMember_Post(13); });
    $("#EntranceMember_000607").click(function() { EntranceMember_Post(14); });

    $("#EntranceMember_000301").click(function() { EntranceMember_Post(0); });
    $("#EntranceMember_000302").click(function() { EntranceMember_Post(1); });
    $("#EntranceMember_000303").click(function() { EntranceMember_Post(2); });
    $("#EntranceMember_000304").click(function() { EntranceMember_Post(3); });
    $("#EntranceMember_000305").click(function() { EntranceMember_Post(4); });
    $("#EntranceMember_000306").click(function() { EntranceMember_Post(5); });
    $("#EntranceMember_000307").click(function() { EntranceMember_Post(6); });
    $("#EntranceMember_000308").click(function() { EntranceMember_Post(7); });

}

function EntranceMember_Update() {
    EntranceMember_UpdateMember(0);
}

function EntranceMember_Back() {
    $("#EntranceMember_00").hide();

    $("#Entrance_00").show();
    Entrance_Update();
}

function EntranceMember_Order() {
    EntranceMember_UpdateMember($("#EntranceMember_000200").val());
}

function EntranceMember_UpdateMember(select) {
    Popup_Wait(function() {
        GetJSON(
            "../../i_entrance/member/Get.php", {
                select: select
            },
            function(response, textStatus) {
                if(response.result == "SUCCESS") {
                    $("#EntranceMember_000000").text(response.count);
                    $("#EntranceMember_000300").html(response.lists);
                    $("#EntranceMember_0005").html(response.members);
                    $("#EntranceMember_000600").html(response.state);
                    Popup_WaitHide();
                } else {
                    Popup_WaitShow("처리 실패!");
                }
            }
        )
    });
}

function EntranceMember_Post(select) {
    var no = $("#EntranceMember_000300").val();
    var sel = $("#EntranceMember_000200").val();

    Popup_Confirm('정말 실행하시겠습니까?', function() {
        Popup_Wait(function() {
            PostJSON(
                "../../i_entrance/member/Post.php", {
                    select: select,
                    no: no
                },
                function(response, textStatus) {
                    if(response.result == "SUCCESS") {
                        Popup_WaitHide();
                        EntranceMember_UpdateMember(sel);
                    } else {
                        Popup_WaitShow("처리 실패!");
                    }
                }
            )
        })
    });
}
