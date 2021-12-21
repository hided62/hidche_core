import { default as che_건국 } from "./che_건국.vue";
import { default as che_군량매매 } from "./che_군량매매.vue";
import { default as che_등용 } from "./che_등용.vue";
import { default as che_선양 } from "./che_선양.vue";

import { default as che_임관 } from "./che_임관.vue";
import { default as che_장수대상임관 } from "./che_장수대상임관.vue";
import { default as che_징병 } from "./che_징병.vue";

import { default as CityProcess } from "./CityProcess.vue";


//TODO: 자주 쓰는 녀석들은 Slot으로 변경

export const commandMap: Record<string, typeof CityProcess> = {
    che_강행: CityProcess,
    che_군량매매,
    che_건국,
    che_등용,
    che_모병: che_징병,
    che_선동: CityProcess,
    che_선양,
    che_이동: CityProcess,
    che_임관,
    che_장수대상임관,
    che_징병,
    che_첩보: CityProcess,
    che_출병: CityProcess,
    che_탈취: CityProcess,
    che_파괴: CityProcess,
    che_화계: CityProcess,
}

/*
- 항목들
장수/금쌀/분량 - 증여(포상 이식)
금쌀/분량 - 헌납(군량매매 또는 포상 수정)
도시 - 첩보, 계략(화계 등)

고유 양식 - 숙련전환, 장비매매



*/