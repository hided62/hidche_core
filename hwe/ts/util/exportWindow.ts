export function exportWindow(obj:unknown, objName: string, targetWindow?: unknown):void{
    const target:unknown = targetWindow ?? window;
    (target as {[v: string]: unknown})[objName] = obj;
}