<table style='width:100%;' class='tb_layout bg2'>
    <tr><td colspan=8 style='text-align:center;height:20px;color:<?=$nationTextColor?>;background-color:<?=$nationColor?>;font-weight:bold;font-size:13px;'>【 <?=$region?> | <?=$levelText?> 】 <?=$name?></td></tr>
    <tr><td colspan=8 style='text-align:center;height:20px;color:<?=$nationTextColor?>;background-color:<?=$nationColor?>'><b><?=$nationName?"지배 국가 【 {$nationName} 】":'공 백 지'?></b></td>
    </tr>
    <tr>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>주민</b></td>
        <td height=7 colspan=3><?=$this->bar($pop/$pop_max*100)?></td>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>민심</b></td>
        <td height=7><?=$this->bar($trust)?></td>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>태수</b></td>
        <td rowspan=2 style='text-align:center;'><?=$officerName[4]?></td>
    </tr>
    <tr>
        <td colspan=3 style='text-align:center;'><?=$pop?>/<?=$pop_max?></td>
        <td style='text-align:center;'><?=round($trust, 1)?></td>
    </tr>
    <tr>
        <td width=50  rowspan=2 style='text-align:center;' class='bg1'><b>농업</b></td>
        <td width=100 height=7><?=$this->bar($agri/$agri_max*100)?></td>
        <td width=50  rowspan=2 style='text-align:center;' class='bg1'><b>상업</b></td>
        <td width=100 height=7><?=$this->bar($comm/$comm_max*100)?></td>
        <td width=50  rowspan=2 style='text-align:center;' class='bg1'><b>치안</b></td>
        <td width=100 height=7><?=$this->bar($secu/$secu_max*100)?></td>
        <td width=50  rowspan=2 style='text-align:center;' class='bg1'><b>군사</b></td>
        <td rowspan=2 style='text-align:center;'><?=$officerName[3]?></td>
    </tr>
    <tr>
        <td style='text-align:center;'><?=$agri?>/<?=$agri_max?></td>
        <td style='text-align:center;'><?=$comm?>/<?=$comm_max?></td>
        <td style='text-align:center;'><?=$secu?>/<?=$secu_max?></td>
    </tr>
    <tr>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>수비</b></td>
        <td height=7><?=$this->bar($def/$def_max*100)?></td>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>성벽</b></td>
        <td height=7><?=$this->bar($wall/$wall_max*100)?></td>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>시세</b></td>
        <td height=7><?=$this->bar(($trade-95)*10)?></td>
        <td rowspan=2 style='text-align:center;' class='bg1'><b>종사</b></td>
        <td rowspan=2 style='text-align:center;'><?=$officerName[2]?></td>
    </tr>
    <tr>
        <td style='text-align:center;'><?=$def?>/<?=$def_max?></td>
        <td style='text-align:center;'><?=$wall?>/<?=$wall_max?></td>
        <td style='text-align:center;'><?=$trade?"{$trade}%":'상인없음'?></td>
    </tr>
</table>