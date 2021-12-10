import axios from "axios";
import { isArray } from "lodash";
import { InvalidResponse } from '@/defs';

type ValidResponse = {
    result: true
}

export async function sammoAPI<ResultType extends ValidResponse>(path: string | string[], args?: Record<string, unknown>): Promise<ResultType> {
    if (isArray(path)) {
        path = path.join('/');
    }

    const response = await axios({
        url: "api.php",
        method: "post",
        responseType: "json",
        data: {
            path,
            args,
        },
    });
    const result: InvalidResponse | ResultType = response.data;
    if (!result.result) {
        throw result.reason;
    }
    return result;
}