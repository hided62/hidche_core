import { default as CityProcess} from "./che_이동.vue";

export const commandMap: Record<string, ()=>typeof CityProcess> = {
    che_강행: ()=>CityProcess,
    che_이동: ()=>CityProcess,
    che_출병: ()=>CityProcess,
}