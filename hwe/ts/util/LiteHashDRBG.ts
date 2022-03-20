import type { RNG } from "./RNG";

import { sha512 } from 'js-sha512';

import { convertBytesLikeToUint8Array } from "./convertBytesLikeToUint8Array";
import type { BytesLike } from "./BytesLike";

const maxRngSupportBit = 53;
const maxInt = 0x1f_ffff_ffff_ffff; //      NOTE: b 0, 10000110011, 11...11
const maxIntMore1 = 0x20_0000_0000_0000n; //NOTE: b 0, 10000110100, 00...00
const maxIntMore1f = Number(maxIntMore1);
export const bufferByteSize = 512 / 8; //SHA512

const intBitMapMask = new Map([
    [0x1n, 1],
    [0x3n, 2],
    [0x7n, 3],
    [0xfn, 4],
    [0x1fn, 5],
    [0x3fn, 6],
    [0x7fn, 7],
    [0xffn, 8],
    [0x1ffn, 9],
    [0x3ffn, 10],
    [0x7ffn, 11],
    [0xfffn, 12],
    [0x1fffn, 13],
    [0x3fffn, 14],
    [0x7fffn, 15],
    [0xffffn, 16],
    [0x1ffffn, 17],
    [0x3ffffn, 18],
    [0x7ffffn, 19],
    [0xfffffn, 20],
    [0x1fffffn, 21],
    [0x3fffffn, 22],
    [0x7fffffn, 23],
    [0xffffffn, 24],
    [0x1ffffffn, 25],
    [0x3ffffffn, 26],
    [0x7ffffffn, 27],
    [0xfffffffn, 28],
    [0x1fffffffn, 29],
    [0x3fffffffn, 30],
    [0x7fffffffn, 31],
    [0xffffffffn, 32],
    [0x1ffffffffn, 33],
    [0x3ffffffffn, 34],
    [0x7ffffffffn, 35],
    [0xfffffffffn, 36],
    [0x1fffffffffn, 37],
    [0x3fffffffffn, 38],
    [0x7fffffffffn, 39],
    [0xffffffffffn, 40],
    [0x1ffffffffffn, 41],
    [0x3ffffffffffn, 42],
    [0x7ffffffffffn, 43],
    [0xfffffffffffn, 44],
    [0x1fffffffffffn, 45],
    [0x3fffffffffffn, 46],
    [0x7fffffffffffn, 47],
    [0xffffffffffffn, 48],
    [0x1ffffffffffffn, 49],
    [0x3ffffffffffffn, 50],
    [0x7ffffffffffffn, 51],
    [0xfffffffffffffn, 52],
    [0x1fffffffffffffn, 53],
]);

function calcBitMask(n: bigint): bigint {
    n |= n >> 1n;
    n |= n >> 2n;
    n |= n >> 4n;
    n |= n >> 8n;
    n |= n >> 16n;
    n |= n >> 32n;

    return n;
}
export class LiteHashDRBG implements RNG {

    protected buffer!: ArrayBuffer;
    protected bufferIdx!: number;
    protected hq: DataView;
    protected hqIdxPos: number;

    public constructor(protected seed: BytesLike, protected stateIdx = 0, bufferIdx = 0) {
        if(bufferIdx < 0){
            throw new Error(`bufferIdx ${bufferIdx} < 0`);
        }
        if(bufferIdx >= bufferByteSize){
            throw new Error(`bufferidx ${bufferIdx} >= ${bufferByteSize}`);
        }
        if(stateIdx < 0){
            throw new Error(`stateIdx ${stateIdx} < 0`);
        }

        const seedU8 = convertBytesLikeToUint8Array(seed);
        const hqBuffer = new ArrayBuffer(seedU8.byteLength + 4);
        const hqU8 = new Uint8Array(hqBuffer);

        hqU8.set(seedU8, 0);
        this.hq = new DataView(hqBuffer);
        this.hqIdxPos = seedU8.byteLength;

        this.genNextBlock();
        this.bufferIdx = bufferIdx;
    }

    protected genNextBlock(): void {
        this.hq.setUint32(this.hqIdxPos, this.stateIdx, true);
        const digest = sha512.arrayBuffer(this.hq.buffer);
        this.buffer = digest;
        this.bufferIdx = 0;
        this.stateIdx += 1;
    }

    public getMaxInt(): number {
        return maxInt;
    }

    public nextBytes(bytes: number, baseBytes?: number): Uint8Array {
        bytes |= 0;
        if (bytes <= 0) {
            throw new Error(`${bytes} <= 0`);
        }

        if (this.bufferIdx + bytes <= bufferByteSize) {
            if(baseBytes === undefined || bytes >= baseBytes){
                const result = this.buffer.slice(this.bufferIdx, this.bufferIdx + bytes);
                this.bufferIdx += bytes;
                if (this.bufferIdx === bufferByteSize) {
                    this.genNextBlock();
                }
                return new Uint8Array(result);
            }

            const resultBuffer = new ArrayBuffer(Math.max(bytes, baseBytes));
            const result = new Uint8Array(resultBuffer);
            result.set(new Uint8Array(this.buffer, this.bufferIdx, bytes));
            this.bufferIdx += bytes;
            if( this.bufferIdx === bufferByteSize){
                this.genNextBlock();
            }
            return result;
        }

        const resultBuffer = new ArrayBuffer(baseBytes ? Math.max(bytes, baseBytes) : bytes);
        const result = new Uint8Array(resultBuffer);

        result.set(new Uint8Array(this.buffer, this.bufferIdx));
        let offset = bufferByteSize - this.bufferIdx;
        let remain = bytes - offset;

        while (remain > bufferByteSize) {
            this.genNextBlock();
            result.set(new Uint8Array(this.buffer), offset);
            offset += bufferByteSize;
            remain -= bufferByteSize;
        }

        this.genNextBlock();
        if (remain === 0) {
            return result;
        }

        result.set(new Uint8Array(this.buffer, 0, remain), offset);
        this.bufferIdx = remain;
        return result;
    }

    public nextBits(bits: number, baseBytes?: number): Uint8Array {
        bits |= 0;
        const bytes = (bits + 7) >> 3;
        const headBits = bits & 0x7;

        const result = this.nextBytes(bytes, baseBytes);
        if (headBits === 0) {
            return result;
        }

        result[bytes - 1] &= 0xff >> (8 - headBits);
        return result;
    }

    protected _nextInt(bits: number): bigint{
        const buffer = this.nextBits(bits, 8);
        const dataView = new DataView(buffer.buffer);
        return dataView.getBigUint64(0, true);
    }

    public nextInt(max?: number): number {
        if (max === undefined || max === maxInt) {
            return Number(this._nextInt(maxRngSupportBit));
        }
        if (max > maxInt) {
            throw new Error('Over max int');
        }
        if (max === 0) {
            return 0;
        }
        if (max < 0) {
            return -this.nextInt(-max);
        }

        const mask = calcBitMask(BigInt(max));
        const bits = intBitMapMask.get(mask) as number;

        let n = Number(this._nextInt(bits));
        while (n > max){
            n = Number(this._nextInt(bits));
        }
        return n;
    }

    public nextFloat1(): number {
        // eslint-disable-next-line no-constant-condition
        while(true){
            const nInt = this._nextInt(maxRngSupportBit + 1);
            if(nInt < maxIntMore1){
                return Number(nInt) / maxIntMore1f;
            }
            if(nInt === maxIntMore1){
                return 1;
            }
        }
    }

    public static build(seed: BytesLike, stateIdx = 0): LiteHashDRBG{
        return new LiteHashDRBG(seed, stateIdx);
    }
}