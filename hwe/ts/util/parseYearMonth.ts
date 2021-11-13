export function parseYearMonth(yearMonth: number): [number, number] {
    return [(yearMonth / 12) | 0, yearMonth % 12 + 1];
}