import { default as che_국기변경 } from "./che_국기변경.vue";
import { default as che_국호변경 } from "./che_국호변경.vue";
import { default as che_물자원조 } from "./che_물자원조.vue";

import { default as NationProcess } from "./che_선전포고.vue";
import { default as GeneralAmountProcess } from "./che_몰수.vue";
import { default as GeneralCityProcess } from "./che_발령.vue";

export const commandMap: Record<string, typeof NationProcess> = {
    che_국기변경,
    che_국호변경,
    che_급습: NationProcess,
    che_몰수: GeneralAmountProcess,
    che_물자원조,
    che_선전포고: NationProcess,
    che_포상: GeneralAmountProcess,
    che_발령: GeneralCityProcess,
}

/*
- 항목들
국가/금쌀/분량 - 물자원조
도시 - 백성동원, 수몰, 천도, 초토화,
국가 - 불가침파기제의, 선전포고, 이호경식, 종전제의, 피장파장, 허보
고유 양식 - 불가침제의

*/