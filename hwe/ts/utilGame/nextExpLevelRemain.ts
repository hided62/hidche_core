export function nextExpLevelRemain(exp: number, expLevel: number): [number, number] {
  if (exp < 1000) {
    return [exp - expLevel * 100, 100]
  }

  const expBase = 10 * expLevel ** 2;
  const expNext = 10 * (expLevel + 1) ** 2;
  return [exp - expBase, expNext - expBase];
}