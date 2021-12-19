import { default as che_건국} from "./che_건국.vue";
import { default as che_군량매매} from "./che_군량매매.vue";
import { default as che_등용} from "./che_등용.vue";
import { default as CityProcess} from "./che_이동.vue";

export const commandMap: Record<string, typeof CityProcess> = {
    che_강행: CityProcess,
    che_군량매매,
    che_건국,
    che_등용,
    che_이동: CityProcess,
    che_출병: CityProcess,
}