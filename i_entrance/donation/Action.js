function EntranceDonation_Import() {
}

function EntranceDonation_Init() {
    $("#EntranceDonation_0001").click(EntranceDonation_Back);
    $("#EntranceDonation_000200").click(function() { EntranceDonation_Calculate(); });
    $("#EntranceDonation_000308").click(function() { EntranceDonation_Donate(); });
}

function EntranceDonation_Update() {
    EntranceDonation_UpdateDonation();
}

function EntranceDonation_Back() {
    $("#EntranceDonation_00").hide();

    $("#Entrance_00").show();
    Entrance_Update();
}

function EntranceDonation_UpdateDonation() {
    Popup_Wait(function() {
        GetJSON(
            HOME+I+ENTRANCE+W+DONATION+W+GET, {
            },
            function(response, textStatus) {
                if(response.result == "SUCCESS") {
                    $("#EntranceDonation_0005").html(response.donations);
                    $("#EntranceDonation_000301").val(response.date);
                    Popup_WaitHide();
                } else {
                    Popup_WaitShow("처리 실패!");
                }
            }
        )
    });
}

function EntranceDonation_Calculate() {
    Popup_Wait(function() {
        PostJSON(
            HOME+I+ENTRANCE+W+DONATION+W+"calculate"+POST, {
            },
            function(response, textStatus) {
                if(response.result == "SUCCESS") {
                    $("#EntranceDonation_000301").val("");
                    $("#EntranceDonation_000302").val("");
                    $("#EntranceDonation_000303").val("");
                    $("#EntranceDonation_000304").val("");
                    $("#EntranceDonation_000305").val("");
                    $("#EntranceDonation_000306").val("");
                    Popup_WaitHide();
                    EntranceDonation_UpdateDonation();
                } else {
                    Popup_WaitShow("처리 실패!");
                }
            }
        )
    });
}

function EntranceDonation_Donate() {
    var date = $("#EntranceDonation_000301").val();
    var id = $("#EntranceDonation_000302").val();
    var name = $("#EntranceDonation_000303").val();
    var rname = $("#EntranceDonation_000304").val();
    var subname = $("#EntranceDonation_000305").val();
    var amount = $("#EntranceDonation_000306").val();

    Popup_Wait(function() {
        PostJSON(
            HOME+I+ENTRANCE+W+DONATION+W+POST, {
                date: date,
                id: id,
                name: name,
                rname: rname,
                subname: subname,
                amount: amount
            },
            function(response, textStatus) {
                if(response.result == "SUCCESS") {
                    $("#EntranceDonation_000301").val("");
                    $("#EntranceDonation_000302").val("");
                    $("#EntranceDonation_000303").val("");
                    $("#EntranceDonation_000304").val("");
                    $("#EntranceDonation_000305").val("");
                    $("#EntranceDonation_000306").val("");
                    Popup_WaitHide();
                    EntranceDonation_UpdateDonation();
                } else {
                    Popup_WaitShow("처리 실패!");
                }
            }
        )
    });
}
