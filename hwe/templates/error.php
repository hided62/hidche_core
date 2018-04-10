<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>에러</title>

    <link type="text/css" rel="stylesheet" href='../d_shared/common.css'>
    <link rel='stylesheet' href='../css/config.css' type='text/css'>
</head>
<body>

<div class="bg0 legacy_layout" 
    style="width:1000px;color:white;position:absolute;left:50%;top:50%;transform:translateX(-50%) translateY(-50%) ;">
    <h2 class="with_border bg1" style="color:orange;text-align:center;">서 버 에 러</h2>
    <main class="with_border" >
    두가지 중 한가지 이유일 수 있습니다.<br><br>
            1. 현재 서버가 처리중입니다. 몇초 후 아래 버튼을 눌러주세요.<br><br>
            2. 오랫동안 이 메세지가 뜰 경우는 서버에 에러가 발생하여 잠시 중단된 상태입니다.<br>
            &nbsp;&nbsp;&nbsp;운영자가 처리할 때까지 기다려주세요
    </main>
    <div class="with_border">
        <button style='width:200px;height:2em;font-size:1.2em;' onclick="location.replace('./')">몇 초 뒤 눌러주세요</button>
    </div>
    <div class="with_border">
        <?=$this->message?>
    </div>  
    <div class="with_border">
        <pre>
        <?php debug_print_backtrace(); ?>
</pre>
    </div>  
</div>
</body>
</html>

