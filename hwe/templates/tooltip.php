<span class="obj_tooltip <?=(isset($copyable_info)&&$copyable_info)?'tooltip_copyable_info':''?>" data-bs-toggle="tooltip" data-bs-placement="top"
    ><span <?=isset($style)?"style=\"$style\"":''?>><?=$text??''?></span
    ><span class="tooltiptext"
        ><span class="hidden_but_copyable">[</span><?=$info??''?><span class="hidden_but_copyable">]</span
    ></span
></span>