export function APIPathGen<T>(obj: T, callback: (path: string[])=>unknown): T;