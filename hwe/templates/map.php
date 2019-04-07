<div class="world_map map_theme_<?=$mapTheme?> draw_required">
    <div class="map_title obj_tooltip" data-toggle="tooltip" data-placement="top" data-tooltip-class="map_title_tooltiptext">
        <span class="map_title_text ">
        </span>
        <span class="tooltiptext"></span>
    </div>
    <div class="map_body">
        <div class="map_bglayer1"></div>
        <div class="map_bglayer2"></div>
        <div class="map_bgroad"></div>
        <div class="map_button_stack">
        <button type="button" class="btn btn-primary map_toggle_cityname btn-xs" data-toggle="button" aria-pressed="false" autocomplete="off">
            도시명 표기
        </button><br>
        <button type="button" class="btn btn-secondary map_toggle_single_tap btn-xs" data-toggle="button" aria-pressed="false" autocomplete="off">
            두번 탭 해 도시 이동
        </button>
        </div>
        <div class="city_tooltip">
            <div class="city_name">

            </div>
            <div class="nation_name">

            </div>
        </div>
    </div>
</div>