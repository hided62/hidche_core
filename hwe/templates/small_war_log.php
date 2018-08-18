<div class="small_war_log">
    
    <span class="me">
        <span class="name_plate">
            <span class="crew_type"><?=$me['crewtype']?></span>
            <span class="name_plate_cover"
                >【<span class="name"><?=$me['name']?></span>】
            </span>
        </span>

        <span class="crew_plate"
            ><span class="remain_crew"><?=$me['remain_crew']?></span
            ><span class="killed_plate">(<span class="killed_crew"><?=$me['killed_crew']?></span>)</span
        ></span>
    </span>

    <span class="war_type war_type_<?=$war_type?>"><?=$war_type_str?></span>

    <span class="you">
        <span class="crew_plate"
            ><span class="remain_crew"><?=$you['remain_crew']?></span
            ><span class="killed_plate">(<span class="killed_crew"><?=$you['killed_crew']?></span>)</span
        ></span>

        <span class="name_plate">
            <span class="crew_type"><?=$you['crewtype']?></span>
            <span class="name_plate_cover"
                >【<span class="name"><?=$you['name']?></span>】
            </span>
        </span>

        
    </span>
</div>