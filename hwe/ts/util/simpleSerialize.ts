import { isInteger, isString } from "lodash";

export function simpleSerialize(...values : (string|number)[]): string{
  const result = [];
  for(const value of values){
    if(isString(value)){
      result.push(`str(${value.length},${value})`);
      continue;
    }
    if(isInteger(value)){
      result.push(`int(${value})`);
    }
    const float6 = value.toLocaleString("en-US", {maximumFractionDigits: 6});
    result.push(`float(${float6})`);
  }
  return result.join('|');
}