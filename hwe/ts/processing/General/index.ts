import { default as che_건국 } from "./che_건국.vue";
import { default as che_군량매매 } from "./che_군량매매.vue";
import { default as che_등용 } from "./che_등용.vue";
import { default as CityProcess } from "./che_이동.vue";
import { default as che_임관 } from "./che_임관.vue";
import { default as che_징병 } from "./che_징병.vue";

//TODO: 자주 쓰는 녀석들은 Slot으로 변경

export const commandMap: Record<string, typeof CityProcess> = {
    che_강행: CityProcess,
    che_군량매매,
    che_건국,
    che_등용,
    che_이동: CityProcess,
    che_임관,
    che_출병: CityProcess,
    che_징병,
    che_모병: che_징병,
}

/*
- 항목들
장수 - 선양,
장수+ - 장수대상임관
국가+ - 임관
장수/금쌀/분량 - 증여(포상 이식)
금쌀/분량 - 헌납(군량매매 또는 포상 수정)
도시 - 첩보, 계략(화계 등)

고유 양식 - 숙련전환, 장비매매, 징병



*/