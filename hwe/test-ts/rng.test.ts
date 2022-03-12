import chai, { assert } from 'chai';
import chaiBytes from 'chai-bytes';
import { bufferByteSize, LiteHashDRBG } from '../ts/util/LiteHashDRBG';
import { RandUtil } from '../ts/util/RandUtil';
import { convertBytesLikeToArrayBuffer } from '../ts/util/convertBytesLikeToArrayBuffer';
import { convertBytesLikeToUint8Array as toBytes } from '../ts/util/convertBytesLikeToUint8Array';
import _ from 'lodash';

chai.use(chaiBytes);

type Bytes = ArrayBuffer | DataView | Uint8Array;
type MaybeBytes = Bytes | string;

function fillBlock(body: MaybeBytes, filler: MaybeBytes = '\0', length = bufferByteSize): Uint8Array {
    const u8Body = toBytes(body);
    const u8Filler = toBytes(filler, false);

    if (u8Filler.byteLength < 1) {
        throw new Error('filler must have length');
    }

    const buffer = new Uint8Array(length);
    buffer.set(u8Body, 0);
    let bufferIdx = u8Body.byteLength;

    while (bufferIdx + u8Filler.byteLength < length) {
        buffer.set(u8Filler, bufferIdx);
        bufferIdx += u8Filler.byteLength;
    }

    if (bufferIdx < length) {
        const slice = new Uint8Array(u8Filler.buffer, u8Filler.byteOffset, length - bufferIdx);
        buffer.set(slice, bufferIdx);
    }

    return buffer;
}


class DummyBlockRNG extends LiteHashDRBG {
    private repeatBlockCnt: number;
    private repeatBlock: ArrayBuffer[];

    public constructor(repeatBlock: MaybeBytes[], stateIdx = 0) {
        super('x');

        this.repeatBlock = [];
        for (const rawBlock of repeatBlock) {
            const block = convertBytesLikeToArrayBuffer(rawBlock);
            if (block.byteLength !== bufferByteSize) {
                throw new Error;
            }
            this.repeatBlock.push(block);
        }
        this.repeatBlockCnt = this.repeatBlock.length;
        this.stateIdx = stateIdx;
        this.bufferIdx = 0;
        this.genNextBlock();
    }

    protected genNextBlock() {
        if (!this.repeatBlock) {
            return;
        }
        this.buffer = this.repeatBlock[this.stateIdx];
        this.bufferIdx = 0;
        this.stateIdx = (this.stateIdx + 1) % this.repeatBlockCnt;
    }
}

const fixedKey = 'HelloWorld';


describe('RNGtestDummy', () => {
    const rng = new DummyBlockRNG([
        fillBlock('', "\x00\x11\x22\x33\x44\x55\x66\x77\x88\x99\xaa\xbb\xcc\xdd\xee\xff")
    ]);

    it('BasicConvert', () => {
        assert.equal(toBytes("\x00\x11\x22\x33\x44\x55\x66\x77\x88\x99\xaa\xbb\xcc\xdd\xee\xff", false).length, 16);
    });
    it('SimpleByte', () => {
        assert.equalBytes(toBytes("\x00", false), rng.nextBytes(1), 'b1');
        assert.equalBytes(toBytes("\x11\x22", false), rng.nextBytes(2), 'b2');
        assert.equalBytes(toBytes("\x33\x44\x55", false), rng.nextBytes(3), 'b3');
        assert.equalBytes(toBytes("\x66\x77\x88\x99", false), rng.nextBytes(4), 'b4');
    });

    it('OverflowBlock', () => {
        for (const idx in _.range(16)) {
            assert.equalBytes(
                toBytes("\xaa\xbb\xcc\xdd\xee\xff\x00\x11\x22\x33\x44\x55\x66\x77\x88\x99", false),
                rng.nextBytes(16)
            );
        }
    });

    it('MultiBlock', () => {
        assert.equalBytes(
            fillBlock('', "\xaa\xbb\xcc\xdd\xee\xff\x00\x11\x22\x33\x44\x55\x66\x77\x88\x99", bufferByteSize * 2),
            rng.nextBytes(bufferByteSize * 2)
        );
    });

    it('bitTest', () => {
        assert.equalBytes(toBytes("\x00", false), rng.nextBits(1)); //aa
        assert.equalBytes(toBytes("\x01", false), rng.nextBits(1)); //bb
        assert.equalBytes(toBytes("\xcc", false), rng.nextBits(8)); //cc
        assert.equalBytes(toBytes("\xdd\x02", false), rng.nextBits(10)); //ddee
        assert.equalBytes(toBytes("\x7f", false), rng.nextBits(7)); //ff
        assert.equalBytes(toBytes("\x00\x11\x22\x33\x44\x55\x06", false), rng.nextBits(53));
    });

    it('int', () => {
        assert.equal(0x77, rng.nextInt(0xff));
        assert.equal(0x9988, rng.nextInt((1 << 16) - 1));
        assert.equal(0xddccbbaa, rng.nextInt(0xffffffff));
        assert.equal(0x0433221100ffee, rng.nextInt());
        assert.equal(0x05, rng.nextInt(0x0f)); //55
        assert.equal(0x06, rng.nextInt(0x12)); //66
        assert.equal(0x08, rng.nextInt(99)); //77(119 -> 7bit) -> 88(136 -> 8bit -> 8)
        assert.equal(0x99, rng.nextInt(0x99)); //99
        assert.equal(0xaa, rng.nextInt(0xaa)); //aa (fit Max)
    });

    it('float', () => {
        const floatMax = 2 ** 53;
        const fa = rng.nextFloat1();
        assert.equal(0x1100ffeeddccbb / floatMax, fa);
        assert.isTrue(0.5313720384 > fa);
        assert.isTrue(0.5313720383 < fa);
        const fb = rng.nextFloat1();
        assert.equal(0x08776655443322 / floatMax, fb);
    });
});

describe('RandUtilDummy', () => {
    it('shuffle', () => {
        const rng = new DummyBlockRNG([fillBlock('', '\x17\x16\x15\x14\x13\x12\x11\x10')]);
        const randUtil = new RandUtil(rng);
        /**
         * 7, [7,1,2,3,4,5,6,0]
         * 6, [7,0,2,3,4,5,6,1]
         * 5, [7,0,1,3,4,5,6,2]
         * 4, [7,0,1,2,4,5,6,3]
         * 3, [7,0,1,2,3,5,6,4]
         * 2, [7,0,1,2,3,4,6,5]
         * 1, [7,0,1,2,3,4,5,6]
         */
        assert.deepEqual(
            randUtil.shuffle(Array.from(_.range(8))),
            [7, 0, 1, 2, 3, 4, 5, 6]
        );

        /**
         * 0, [0,1,2,3,4,5,6,7,8,9]
         * 7, [0,8,2,3,4,5,6,7,1,9]
         * 6, [0,8,1,3,4,5,6,7,2,9]
         * 5, [0,8,1,2,4,5,6,7,3,9]
         * 4, [0,8,1,2,3,5,6,7,4,9]
         * 3, [0,8,1,2,3,4,6,7,5,9]
         * 2, [0,8,1,2,3,4,5,7,6,9]
         * 1, [0,8,1,2,3,4,5,6,7,9]
         * 0, [0,8,1,2,3,4,5,6,7,9]
         */
        assert.deepEqual(
            randUtil.shuffle(Array.from(_.range(10))),
            [0, 8, 1, 2, 3, 4, 5, 6, 7, 9]
        );
    });

    const rng = new DummyBlockRNG([fillBlock('', '\x17\x16\x15\x14\x13\x12\x11\x10')]);
    const randUtil = new RandUtil(rng);
    it('choice', () => {


        //0x17(7), 0x16(6)
        assert.equal(randUtil.choice([0, 1, 2, 3, 4, 5]), 5);

        //0x15(5), Set 순서 유지
        assert.equal(randUtil.choice(new Set([5, 3, 1, 2, 8, 0])), 8);

        //0x14(4), 정렬 순서상 숫자(소-대) > 문자열(삽입순) > 심볼 순서
        assert.equal(randUtil.choice({ c: 'c', a: 'a', b: 'b', 4: 'x', 2: 't', '3': 'q' }), 'c');

    });

    it('choiceUsingWeight', () => {
        //0.6275740099377194 * 38.1 = 23.91
        assert.equal(randUtil.choiceUsingWeight({
            a: 0.1,
            b: 10,
            tt: 2,
            x: -1,
            c: 20,
            d: 0,
            e: 6
        }), 'c');

        //0.658946544056166
        assert.equal(randUtil.choiceUsingWeightPair([
            ['xx', 10],
        ]), 'xx');

        //0.6903152783785083 * 27.3 = 18.84560709973328
        assert.equal(randUtil.choiceUsingWeightPair([
            ['e', 10],
            ['d', 4],
            ['c', 0.1],
            ['baba', 0.2],
            ['q', 9],
            ['xt', 4]
        ]), 'q');
    });
});

describe('RNGexpectedError', () => {
    const rng = new LiteHashDRBG(fixedKey);
    it('nextBits0', () => {
        assert.throw(() => rng.nextBits(0));
    });
    it('nextBits-1', () => {
        assert.throw(() => rng.nextBits(-1));
    });

    it('nextBytes0', () => {
        assert.throw(() => rng.nextBytes(0));
    });
    it('nextBytes-1', () => {
        assert.throw(() => rng.nextBytes(-1));
    });

    const randUtil = new RandUtil(rng);
    it('utilEmptyChoice', () => {
        assert.throw(() => randUtil.choice([]));
    });
    it('utilEmptyChoiceUsingWeight', () => {
        assert.throw(() => randUtil.choiceUsingWeight({}));
    });
    it('utilEmptyChoiceUsingWeightPair', () => {
        assert.throw(() => randUtil.choiceUsingWeightPair([]));
    });
});

describe('RNGAcceptable', () => {
    const rng = new LiteHashDRBG(fixedKey);
    it('RNG', () => {
        rng.nextInt(0);
        rng.nextInt(2 ** 53 - 1);
        rng.nextBytes(65);
        rng.nextBits(512);
    });

    const randUtil = new RandUtil(rng);
    it('RandUtil', () => {
        randUtil.choice([0, 0, 0]);
        randUtil.choiceUsingWeight({
            0: 0,
            1: -1
        });
        randUtil.choiceUsingWeightPair([
            [0, 0],
            [1, 0],
            [2, -2]
        ]);
        randUtil.nextBool(1.1);
        randUtil.nextBool(-0.1);
        randUtil.shuffle([]);
        randUtil.shuffle([1]);
        randUtil.nextRange(0, 0);
        randUtil.nextRangeInt(0, 0);
        randUtil.nextRange(1, -1);
        randUtil.nextRangeInt(1, -1);
    })

    it('RNGLong', () => {
        const longKey = fixedKey;
        for(const _a of _.range(8)){
            longKey.concat(longKey);
        }
        const rngLong = new LiteHashDRBG(longKey);
        for(const _a of _.range(10)){
            rngLong.nextBytes(16);
        }
    })
});

/* Python TestVector
import hashlib
import struct

fixedKey = 'HelloWorld'.encode('utf-8')

def hash(key, idx):
    idxV = struct.pack("<I", idx)
    return hashlib.sha512(key + idxV).digest()

for idx in range(5):
    print(hash(fixedKey, idx).hex())
*/
describe('RNG', () => {

    //JS - PHP 일치 확인 정도로.

    const testVector = Buffer.from([
        '24d9ccd648556255fd0ee9f5b29918de90617341958b3b354d572167e4dee02b757816a2bbe0b502c52413ffd384381a9d7b4e193df6f4345d6a95e111d661c4',
        '2e9264512f6f4b080cf1376b74fab6878ecf4a6e185942d2e5b22cf923885b9952d40601a414225d6901417fd4ce9368ac77e4a63d3fc9b58ab952bb8c33f165',
        '8e2ebf5af6283a1b18f4c044c86c20d02be3890613c4cc8b7c6b7b35581263b972a82630df69a9289988422d7c3a9be5edf78d5de16fabd01e5dd4e458068d8a',
        '398596047ba547bfe371ec863a3e019ab0dbc4bb3b27e9077685aae4283ff6bbccfd981d92f9358f7efffbb72a940414802d98466d132e2ad0a16a12946d5f47',
        'b3606fe9b18c4aa7315e78bb9e47cb51cc4e203fcc2e631f0405c1b872c8e1cb5b6415ea74bbb77fffaaadb002b47cb4f4628dc0709634365b187667f5c708cb',
    ].join(''), 'hex');

    it('bytes', ()=>{
        const rng = new LiteHashDRBG(fixedKey);

        let offset = 0;
        assert.equalBytes(rng.nextBytes(10), testVector.slice(offset, offset + 10), '1');
        offset += 10;
        assert.equalBytes(rng.nextBytes(32), testVector.slice(offset, offset + 32), '2');
        offset += 32;
        assert.equalBytes(rng.nextBytes(1), testVector.slice(offset, offset + 1), '3');
        offset += 1;
        assert.equalBytes(rng.nextBytes(64), testVector.slice(offset, offset + 64), '4');
        offset += 64;
        assert.equalBytes(rng.nextBytes(5), testVector.slice(offset, offset + 5), '5');
        offset += 5;

        const lastA = rng.nextBytes(16, 18);
        const lastB = new Uint8Array(18);
        lastB.set(testVector.slice(offset, offset + 16));
        assert.equalBytes(lastA, lastB);
    });

    it('bits', ()=>{
        const rng = new LiteHashDRBG(fixedKey);

        let offset = 0;

        const testBits = [10, 4, 15, 32, 7, 99, 512, 1, 2, 3];

        for(const bits of testBits){
            const bytes = Math.ceil(bits / 8)
            const A = rng.nextBits(bits);
            const B = new Uint8Array(testVector.slice(offset, offset + bytes));
            offset += bytes;

            if(bits % 8 != 0){
                const bitMask = 0xff >> (8 - (bits % 8));
                B[bytes - 1] &= bitMask;
            }
            assert.equalBytes(A, B);
        }
    });

    it('float', ()=>{
        const rng = new LiteHashDRBG(fixedKey);
        const rng2 = new DummyBlockRNG([
            new Uint8Array(testVector.slice(bufferByteSize * 0, bufferByteSize * 1)),
            new Uint8Array(testVector.slice(bufferByteSize * 1, bufferByteSize * 2)),
            new Uint8Array(testVector.slice(bufferByteSize * 2, bufferByteSize * 3)),
            new Uint8Array(testVector.slice(bufferByteSize * 3, bufferByteSize * 4)),
            new Uint8Array(testVector.slice(bufferByteSize * 4, bufferByteSize * 5)),
        ]);

        for(const idx of _.range(18)){
            assert.equal(rng.nextFloat1(), rng2.nextFloat1(), `float${idx}`);
        }

    });
});