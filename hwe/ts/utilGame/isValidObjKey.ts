export function isValidObjKey<T>(key: T|'None'|undefined|null): boolean{
  if(key === 'None' || key === undefined || key === null){
    return false;
  }
  return true;
}