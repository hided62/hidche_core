import axios from "axios";
import { RuntimeError, unwrap } from "./util";

declare global {
    interface Window {
        userList: Record<number, JQuery<HTMLElement>>;
    }
}

export function launchTroopPlugin($: JQueryStatic): void {

    let userList: Record<number, JQuery<HTMLElement>> = {};
    const basicPath = (() => {
        const path = document.location.pathname;
        return path.substring(0, path.lastIndexOf('/'));
    })();

    const $userFrame: JQuery<HTMLElement> = $("<div id='on_mover' style='position:absolute;'>" +
        "<table class='tb_layout bg0' style='width:100%;'><thead>" +
        "<tr>" +
        "<td width=98 class='bg1 center'>이 름</td>" +
        "<td width=98 class='bg1 center'>통무지</td>" +
        "<td width=53 class='bg1 center'>자 금</td>" +
        "<td width=53 class='bg1 center'>군 량</td>" +
        "<td width=48 class='bg1 center'>도시</td>" +
        "<td width=28 class='bg1 center'>守</td>" +
        "<td width=58 class='bg1 center'>병 종</td>" +
        "<td width=63 class='bg1 center'>병 사</td>" +
        "<td width=38 class='bg1 center'>훈련</td>" +
        "<td width=38 class='bg1 center'>사기</td>" +
        "<td width=213 class='bg1 center'>명 령</td>" +
        "<td width=38 class='bg1 center'>삭턴</td>" +
        "<td width=48 class='bg1 center'>턴</td>" +
        "</tr>" +
        "</thead></thead><tbody class='content'></tbody></table></div>");
    $userFrame.find('thead td');
    $userFrame.css('width', '960px').css('margin', '0').css('padding', '0').css('left', '50%').css('margin-left', '-480px');
    $userFrame.hide();


    const runAnalysis = async function () {
        userList = {};
        const $content = $('#on_mover .content');
        $content.html('');
        const response = await axios.get(`${basicPath}/b_genList.php`, {responseType: 'text'});
        const rawData = response.data;

        try {
            const $html = $(rawData); 

            const tmpUsers: JQuery<HTMLElement> = (() => {
                let tmpUsers = undefined;
                $html.each(function () {
                    const $this = $(this);
                    if ($this.attr('id') == "general_list") {
                        tmpUsers = $(this);
                        return false;
                    }
                });
                if (tmpUsers === undefined) {
                    throw new RuntimeError();
                }
                return tmpUsers;
            })()


            tmpUsers.find("tbody > tr").each(function () {
                const $this = $(this);
                const $부대 = $this.children('.i_troop');

                const 부대 = $.trim($부대.text());

                if (부대 == '-') {
                    //부대 안탔음!
                    return;
                }
                $부대.remove();

                const generalID = parseInt($this.data('general-id'));
                userList[generalID] = $this;
                $this.hide();
                $content.append($this);
            });

            $('.troopUser').hover(function () {
                const $this = $(this);
                const parent = $this.closest('tr');
                const generalID = parseInt($this.data('general-id'));
                console.log(generalID);
                const top = unwrap(parent.offset()).top + unwrap(parent.outerHeight());
                $userFrame.css('top', top);
                userList[generalID].show();
                $userFrame.show();
            }, function () {
                const $this = $(this);
                const generalID = parseInt($this.data('general-id'));
                userList[generalID].hide();
                $userFrame.hide();
            });
        }
        catch (err) {
            console.log(err);
        }


    };

    const $frame = $('table:eq(0) td:eq(0)');
    $frame.find('br:last').remove();

    const $btn = $('<input type="button" value="암행부 연동">');
    $btn.click(async function () {
        await runAnalysis();
    });

    $frame.append($btn);


    window.userList = userList;

    $('body').append($userFrame);

}