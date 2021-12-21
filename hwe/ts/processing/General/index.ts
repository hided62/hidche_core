import { default as che_건국 } from "./che_건국.vue";
import { default as che_군량매매 } from "./che_군량매매.vue";
import { default as che_등용 } from "./che_등용.vue";
import { default as che_선양 } from "./che_선양.vue";
import { default as che_임관 } from "./che_임관.vue";
import { default as che_장수대상임관 } from "./che_장수대상임관.vue";
import { default as che_징병 } from "./che_징병.vue";
import { default as che_헌납 } from "./che_헌납.vue";

import { default as ProcessCity } from "../ProcessCity.vue";
import { default as ProcessGeneralAmount } from "../ProcessGeneralAmount.vue";


//TODO: 자주 쓰는 녀석들은 Slot으로 변경

export const commandMap: Record<string, typeof ProcessCity> = {
    che_강행: ProcessCity,
    che_군량매매,
    che_건국,
    che_등용,
    che_모병: che_징병,
    che_선동: ProcessCity,
    che_선양,
    che_이동: ProcessCity,
    che_임관,
    che_장수대상임관,
    che_징병,
    che_증여: ProcessGeneralAmount,
    che_첩보: ProcessCity,
    che_출병: ProcessCity,
    che_탈취: ProcessCity,
    che_파괴: ProcessCity,
    che_화계: ProcessCity,
    che_헌납,
}

/*
- 항목들
고유 양식 - 숙련전환, 장비매매
*/