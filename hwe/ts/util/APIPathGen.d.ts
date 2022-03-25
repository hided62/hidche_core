export function APIPathGen<T>(obj: T, callback: (path: string[])=>unknown): T;

export function StrVar<PathType extends string>(): <NextCall>(next: NextCall)=>{
    [v in PathType]: NextCall
};

export function NumVar<NextCall>(next: NextCall):{
    [v: number]: NextCall
};

/*
const apiPath = {
    SomePath: someFunc,
    User: StrVar<'a'|'b'>()({
        Update: someFunc,
        Delete: someFunc,
    }),
    NationInfo: NumVar({
        show: someFunc
    })
}
*/