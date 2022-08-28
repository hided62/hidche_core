export interface RNG {

    /**
     * nextInt()가 반환 가능한 최댓값
     */
    getMaxInt(): number;

    nextBytes(bytes: number): Uint8Array;
    nextBits(bits: number): Uint8Array;

    nextInt(max?: number): number;
    nextFloat1(): number;
}