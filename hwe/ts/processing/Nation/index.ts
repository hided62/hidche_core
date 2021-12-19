import { default as che_국기변경 } from "./che_국기변경.vue";
import { default as che_국호변경 } from "./che_국호변경.vue";
import { default as NationProcess } from "./che_급습.vue";
import { default as GeneralAmountProcess } from "./che_몰수.vue";
import { default as GeneralCityProcess } from "./che_발령.vue";

export const commandMap: Record<string, typeof NationProcess> = {
    che_국기변경,
    che_국호변경,
    che_급습: NationProcess,
    che_몰수: GeneralAmountProcess,
    che_포상: GeneralAmountProcess,
    che_발령: GeneralCityProcess,
}