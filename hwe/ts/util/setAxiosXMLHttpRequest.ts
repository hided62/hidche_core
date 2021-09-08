import axios from "axios";

export function setAxiosXMLHttpRequest(): void{
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    //TODO: X-Requested-With 믿지 말자.
}
