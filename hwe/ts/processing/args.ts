import { isArray, isBoolean, isInteger, isString } from "lodash";


const stringArgs = [
    'nationName', 'optionText', 'itemType', 'nationType', 'itemCode', 'commandType',
] as const;
const intArgs = [
    'crewType', 'destGeneralID', 'destCityID', 'destNationID',
    'amount', 'colorType',
    'year', 'month',
    'srcArmType', 'destArmType', //숙련전환 전용
] as const;

const booleanArgs = [
    'isGold', 'buyRice',
] as const;

const integerArrayArgs = [
    'destNationIDList', 'destGeneralIDList', 'amountList'
] as const;

type StringKeys = typeof stringArgs[number];
type IntKeys = typeof intArgs[number];
type BooleanKeys = typeof booleanArgs[number];
type IntegerArrayKeys = typeof integerArrayArgs[number];


export type Args = {
    [key in StringKeys]?: string;
} & {
        [key in IntKeys]?: number;
    } & {
        [key in BooleanKeys]?: boolean;
    } & {
        [key in IntegerArrayKeys]?: number[]
    };



export function testSubmitArgs(args: Args): true | ['int' | 'string' | 'boolean' | 'int[]', keyof Args, number | string | boolean | number[]] {
    for (const intKey of intArgs) {
        const testVal = args[intKey];
        if (testVal === undefined) {
            continue;
        }
        if (!isInteger(testVal)) {
            return ['int', intKey, testVal];
        }
    }
    for (const stringKey of stringArgs) {
        const testVal = args[stringKey];
        if (testVal === undefined) {
            continue;
        }
        if (!isString(testVal)) {
            return ['string', stringKey, testVal];
        }
    }
    for (const booleanKey of booleanArgs) {
        const testVal = args[booleanKey];
        if (testVal === undefined) {
            continue;
        }
        if (!isBoolean(testVal)) {
            return ['boolean', booleanKey, testVal];
        }
    }
    for (const integerArrayKey of integerArrayArgs) {
        const testVal = args[integerArrayKey];
        if (testVal === undefined) {
            continue;
        }
        if (!isArray(args[integerArrayKey])) {
            return ['int[]', integerArrayKey, testVal];
        }
        for (const value of testVal) {
            if (!isInteger(value)) {
                return ['int[]', integerArrayKey, testVal];
            }
        }
    }
    return true;
}
