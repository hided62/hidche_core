import { default as che_국기변경 } from "./che_국기변경.vue";
import { default as che_국호변경 } from "./che_국호변경.vue";
import { default as che_물자원조 } from "./che_물자원조.vue";
import { default as che_피장파장 } from "./che_피장파장.vue";

import { default as ProcessNation } from "../ProcessNation.vue";
import { default as ProcessGeneralAmount } from "./che_몰수.vue";
import { default as ProcessGeneralCity } from "./che_발령.vue";

export const commandMap: Record<string, typeof ProcessNation> = {
    che_국기변경,
    che_국호변경,
    che_급습: ProcessNation,
    che_몰수: ProcessGeneralAmount,
    che_물자원조,
    che_발령: ProcessGeneralCity,
    che_불가침파기제의: ProcessNation,
    che_선전포고: ProcessNation,
    che_이호경식: ProcessNation,
    che_종전제의: ProcessNation,
    che_포상: ProcessGeneralAmount,
    che_피장파장,
}

/*
- 항목들
도시 - 백성동원, 수몰, 천도, 초토화, 허보
고유 양식 - 불가침제의

*/