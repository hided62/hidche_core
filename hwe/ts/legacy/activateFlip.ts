import $ from "jquery";

export function activateFlip($obj?: JQuery<HTMLElement>): void {
    let $result: JQuery<HTMLElement>;
    if ($obj === undefined) {
        $result = $('img[data-flip]');
    } else {
        $result = $obj.find('img[data-flip]');
    }

    $result.each(function () {
        activeFlipItem($(this));
    });

}


export function activeFlipItem($img: JQuery<HTMLElement>): void {
    const imageList = [];
    imageList.push($img.attr('src'));
    $.each($img.data('flip').split(','), function (idx, value) {
        value = $.trim(value);
        if (!value) {
            return true;
        }
        imageList.push(value);
    });
    if (imageList.length <= 1) {
        return;
    }
    $img.data('computed_flip_array', imageList);
    $img.data('computed_flip_idx', 0);

    $img.click(function () {
        const arr = $img.data('computed_flip_array');
        let idx = $img.data('computed_flip_idx');
        idx = (idx + 1) % (arr.length);
        $img.attr('src', arr[idx]);
        $img.data('computed_flip_idx', idx);
    });
    $img.css('cursor', 'pointer');
}
