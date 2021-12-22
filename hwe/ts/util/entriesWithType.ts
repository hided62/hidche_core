type Entries<T> = {
    [K in keyof T]: [K, T[K]];
}[keyof T][];

export function entriesWithType<T>(value: T): Entries<T>{
    return Object.entries(value) as unknown as Entries<T>;
}