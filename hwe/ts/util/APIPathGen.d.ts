export function APIPathGen<T, V>(
    obj: T,
    callback: (path: string[], tail: V, pathParam?: Record<string, string | number>) => unknown,
    pathParam?: Record<string, string | number>
): T;

export function StrVar<PathType extends string>(paramKey: string): <NextCall>(next: NextCall) => {
    [v in PathType]: NextCall
};

export function NumVar<NextCall>(paramKey: string, next: NextCall): {
    [v: number]: NextCall
};

/*
const apiPath = {
    SomePath: someFunc,
    User: StrVar<'a'|'b'>('name')({
        Update: someFunc,
        Delete: someFunc,
    }),
    NationInfo: NumVar('id', {
        show: someFunc
    })
}
*/