<table width=1000 class='tb_layout bg2' style="margin:auto;margin-top:20px;">
    <colgroup>
        <col style="width:98px;" />
        <col style="width:238px;" />
        <col style="width:98px;" />
        <col style="width:238px;" />
        <col style="width:98px;" />
        <col style="width:238px;" />
    </colgroup>
    <thead>
        <tr>
            <td colspan=8 style="color:<?=$this->newColor($color)?>; background-color:<?=$color?>; text-align:center;"
            >【 <?=$name?> 】</td>
        </tr>
    </thead>
    <tbody style="text-align:center;">
        <tr>
            <td class="bg1">성향</td>
            <td><?=$typeName?></td>
            <td class="bg1">-</td>
            <td>-</td>
            <td class="bg1">일자</td>
            <td><?=$date?></td>
        </tr>
        <tr>
            <td class="bg1">최종 작위</td>
            <td><?=$levelName?></td>
            <td class="bg1">최종 장수 수</td>
            <td><?=count($generals)?>명</td>
            <td class="bg1">기술력</td>
            <td><?=$tech?></td>
        </tr>
        <tr>
            <td class="bg1">최대 영토 수</td>
            <td><?=count($maxCities??[])?></td>
            <td class="bg1">최대 병력 수</td>
            <td><?=$maxCrew??0?>명</td>
            <td class="bg1">최대 국력</td>
            <td><?=$maxPower??0?></td>
        </tr>
        <tr>
            <td valign=top class="bg1"> 최대영토</td>
            <td colspan=5><?=join(', ',$maxCities??[])?></td>
        </tr>
        <tr>
            <td valign=top class="bg1"> 장수명단</td>
            <td colspan=5>
                <?php foreach($generalsFull as $general): ?>
                <?=$general['name']?>,
                <?php endforeach; ?>
            </td>
        </tr>
        <tr></tr>
        <tr>
            <td valign=top class="bg1">국가열전</td>
            <td colspan=5 class='bg0' style="text-align:left;"><?=join('<br>', array_map(function($item){
                return $this->ConvertLog($item);
            }, $history))?></td>
        </tr>
    </tbody>
</table>
