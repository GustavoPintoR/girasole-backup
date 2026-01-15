export function sqmToHa(value: number, precision = 2): number {
    return Number((value / 10_000).toFixed(precision));
}

export function sqmToKm2(value: number, precision = 2): number {
    return Number((value / 1_000_000).toFixed(precision));
}
