import { RNG } from './RNG';

export class RandUtil {
    constructor(protected rng: RNG) {

    }

    public nextFloat1(): number {
        return this.rng.nextFloat1();
    }

    public nextRange(min: number, max: number): number {
        const range = max - min;
        return this.nextFloat1() * (range) + min;
    }

    public nextRangeInt(min: number, max: number): number {
        const range = max - min;
        return this.rng.nextInt(range) + min;
    }

    public nextInt(max?: number): number {
        return this.rng.nextInt(max);
    }

    public nextBit(): boolean {
        const view = new DataView(this.rng.nextBits(1));
        return view.getUint8(0) != 0;
    }

    public nextBool(prob = 0.5): boolean {
        if (prob >= 1) {
            return true;
        }
        return this.nextFloat1() < prob;
    }

    public shuffle<T>(srcArray: T[]): T[] {
        const cnt = srcArray.length;
        if(cnt === 0){
            return [];
        }
        if (cnt > this.rng.getMaxInt()) {
            throw 'Invalid random int range';
        }

        const result: T[] = Array.from(srcArray);
        for (let srcIdx = 0; srcIdx < cnt; srcIdx += 1) {
            const destIdx = this.rng.nextInt(cnt - srcIdx - 1) + srcIdx;
            if(srcIdx === destIdx){
                continue;
            }
            [result[srcIdx], result[destIdx]] = [result[destIdx], result[srcIdx]];
        }

        return result;
    }

    //Object는 integer key에 예외가 있어 shuffleAssoc은 없음

    public choice<T>(items: T[] | Record<string | number, T> | Set<T>): T {
        if (items instanceof Array) {
            if(items.length === 0){
                throw new Error('Empty items');
            }
            const idx = this.rng.nextInt(items.length - 1);
            return items[idx];
        }

        if (items instanceof Set) {
            return this.choice(Array.from(items.values()));
        }

        return items[this.choice(Array.from(Object.keys(items)))];
    }

    public choiceUsingWeight(items: Record<string | number, number>): string | number {
        if(Object.keys(items).length === 0){
            throw new Error('Empty items');
        }
        let sum = 0;
        for (const value of Object.values(items)) {
            if (value <= 0) {
                continue;
            }
            sum += value;
        }

        let rd = this.nextFloat1() * sum;

        for (const [item, value] of Object.entries(items)) {
            if (value <= 0) {
                if (rd <= 0) {
                    return item;
                }
                continue;
            }

            if (rd <= value) {
                return item;
            }
            rd -= value;
        }

        throw new Error('Unreacheable');
    }

    public choiceUsingWeightPair<T>(items: [T, number][]): T {
        if(items.length === 0){
            throw new Error('Empty items');
        }
        let sum = 0;
        for (const [, value] of items) {
            if (value <= 0) {
                continue;
            }
            sum += value;
        }

        let rd = this.nextFloat1() * sum;

        for (const [item, value] of items) {
            if (value <= 0) {
                if (rd <= 0) {
                    return item;
                }
                continue;
            }

            if (rd <= value) {
                return item;
            }
            rd -= value;
        }

        throw new Error('Unreacheable');
    }
}