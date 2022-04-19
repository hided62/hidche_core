import { watch, ref } from "vue";

export const hideMapCityName = ref<boolean>(localStorage.getItem('sam.hideMapCityName') == 'yes');
watch(hideMapCityName, (value) => {
    localStorage.setItem('sam.hideMapCityName', value ? 'yes' : 'no');
});

export const toggleSingleTap = ref<boolean>(localStorage.getItem('sam.toggleSingleTap') == 'yes');
watch(toggleSingleTap, (value) => {
    localStorage.setItem('sam.toggleSingleTap', value ? 'yes' : 'no');
});