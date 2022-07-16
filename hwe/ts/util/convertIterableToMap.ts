export function convertIterableToMap<T extends object, K extends keyof T, V extends T[K] & (string | number | symbol)>(
  values: Iterable<T>,
  key: K
): Map<V, T> {
  const result = new Map<V, T>();
  for (const obj of values) {
    result.set(obj[key] as V, obj);
  }
  return result;
}
