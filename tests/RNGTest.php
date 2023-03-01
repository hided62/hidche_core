<?php

use sammo\LiteHashDRBG;
use sammo\RandUtil;

use \PHPUnit\Framework\TestCase;

const BLOCK_SIZE = LiteHashDRBG::BUFFER_BYTE_SIZE;

function fillBlock(string $src, string $filler = '\0', int $length = BLOCK_SIZE)
{
    $tmp = [$src];
    $fillerLen = strlen($filler);
    if ($fillerLen < 1) {
        throw new \InvalidArgumentException('filler must have length');
    }

    $remainFill = intdiv($length - strlen($src), $fillerLen);
    for (; $remainFill > 0; $remainFill--) {
        $tmp[] = $filler;
    }

    $result = join("", $tmp);

    $moreLen = $length - strlen($result);
    if ($moreLen === 0) {
        return $result;
    }

    return $result . substr($filler, 0, $moreLen);
}

class DummyBlockRNG extends LiteHashDRBG
{
    private int $repeatBlockCnt;
    /**
     *
     * @param string[] $repeatBlock
     * @param int $stateIdx
     * @return void
     * @throws RuntimeException
     */
    public function __construct(protected array $repeatBlock, protected int $stateIdx = 0)
    {
        foreach ($repeatBlock as $block) {
            $blockLen = strlen($block);
            if (strlen($block) != BLOCK_SIZE) {
                throw new RuntimeException("Invalid repeat block {$blockLen} != " . BLOCK_SIZE);
            }
        }
        $this->repeatBlockCnt = count($this->repeatBlock);
        $this->genNextBlock();
    }

    protected function genNextBlock(): void
    {
        $this->buffer = $this->repeatBlock[$this->stateIdx];
        $this->bufferIdx = 0;
        $this->stateIdx = ($this->stateIdx + 1) % $this->repeatBlockCnt;
    }
}

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
function getRngTestVector(){
    static $v = null;
    if($v === null){
        $v = hex2bin(join('', [
            '24d9ccd648556255fd0ee9f5b29918de90617341958b3b354d572167e4dee02b757816a2bbe0b502c52413ffd384381a9d7b4e193df6f4345d6a95e111d661c4',
            '2e9264512f6f4b080cf1376b74fab6878ecf4a6e185942d2e5b22cf923885b9952d40601a414225d6901417fd4ce9368ac77e4a63d3fc9b58ab952bb8c33f165',
            '8e2ebf5af6283a1b18f4c044c86c20d02be3890613c4cc8b7c6b7b35581263b972a82630df69a9289988422d7c3a9be5edf78d5de16fabd01e5dd4e458068d8a',
            '398596047ba547bfe371ec863a3e019ab0dbc4bb3b27e9077685aae4283ff6bbccfd981d92f9358f7efffbb72a940414802d98466d132e2ad0a16a12946d5f47',
            'b3606fe9b18c4aa7315e78bb9e47cb51cc4e203fcc2e631f0405c1b872c8e1cb5b6415ea74bbb77fffaaadb002b47cb4f4628dc0709634365b187667f5c708cb',
        ]));
    }
    if($v === false){
        throw new RuntimeException('Invalid Test Vector');
    }

    return $v;
}
final class RNGTest extends TestCase
{
    const FIXED_KEY = 'HelloWorld';

    public function testConvTestVector(){
        $tmp = [
            '24d9ccd648556255fd0ee9f5b29918de90617341958b3b354d572167e4dee02b757816a2bbe0b502c52413ffd384381a9d7b4e193df6f4345d6a95e111d661c4',
            '2e9264512f6f4b080cf1376b74fab6878ecf4a6e185942d2e5b22cf923885b9952d40601a414225d6901417fd4ce9368ac77e4a63d3fc9b58ab952bb8c33f165',
            '8e2ebf5af6283a1b18f4c044c86c20d02be3890613c4cc8b7c6b7b35581263b972a82630df69a9289988422d7c3a9be5edf78d5de16fabd01e5dd4e458068d8a',
            '398596047ba547bfe371ec863a3e019ab0dbc4bb3b27e9077685aae4283ff6bbccfd981d92f9358f7efffbb72a940414802d98466d132e2ad0a16a12946d5f47',
            'b3606fe9b18c4aa7315e78bb9e47cb51cc4e203fcc2e631f0405c1b872c8e1cb5b6415ea74bbb77fffaaadb002b47cb4f4628dc0709634365b187667f5c708cb',
        ];
        $this->assertEquals(BLOCK_SIZE * 2, strlen($tmp[0]));
        $tmp = join('', $tmp);
        $this->assertEquals(true, is_string($tmp));

        $this->assertEquals(BLOCK_SIZE * 5 * 2, strlen($tmp));
        echo $tmp;

        $tmp = hex2bin($tmp);
        $this->assertEquals(BLOCK_SIZE * 5, strlen($tmp));

        global $rngTestVector;
        $this->assertEquals(true, is_string(getRngTestVector()));
    }

    public function testDummyRNG()
    {
        //Block의 값을 고정하고 입력값을 확인
        $rng = new DummyBlockRNG([
            fillBlock('', "\x00\x11\x22\x33\x44\x55\x66\x77\x88\x99\xaa\xbb\xcc\xdd\xee\xff")
        ]);

        //$this->setName('DummyRNG-SimpleByte');
        $this->assertEquals("\x00", $rng->nextBytes(1));
        $this->assertEquals("\x11\x22", $rng->nextBytes(2));
        $this->assertEquals("\x33\x44\x55", $rng->nextBytes(3));
        $this->assertEquals("\x66\x77\x88\x99", $rng->nextBytes(4));

        //$this->setName('DummyRNG-OverflowBlock');
        foreach (range(1, 16) as $_i) {
            $this->assertEquals(
                "\xaa\xbb\xcc\xdd\xee\xff\x00\x11\x22\x33\x44\x55\x66\x77\x88\x99",
                $rng->nextBytes(16)
            );
        }

        //$this->setName('DummyRNG-MultiBlock');
        $this->assertEquals(
            fillBlock('', "\xaa\xbb\xcc\xdd\xee\xff\x00\x11\x22\x33\x44\x55\x66\x77\x88\x99", BLOCK_SIZE * 2),
            $rng->nextBytes(BLOCK_SIZE * 2)
        );

        //$this->setName('DummyRNG-BitTest');
        $this->assertEquals("\x00", $rng->nextBits(1)); //aa
        $this->assertEquals("\x01", $rng->nextBits(1)); //bb
        $this->assertEquals("\xcc", $rng->nextBits(8)); //cc
        $this->assertEquals("\xdd\x02", $rng->nextBits(10)); //ddee
        $this->assertEquals("\x7f", $rng->nextBits(7)); //ff
        $this->assertEquals("\x00\x11\x22\x33\x44\x55\x06", $rng->nextBits(53));

        //$this->setName('DummyRNG-Int');
        $this->assertEquals(0x77, $rng->nextInt(0xff));
        $this->assertEquals(0x9988, $rng->nextInt((1 << 16) - 1));
        $this->assertEquals(0xddccbbaa, $rng->nextInt((1 << 32) - 1));
        $this->assertEquals(0x0433221100ffee, $rng->nextInt());
        $this->assertEquals(0x05, $rng->nextInt(0x0f)); //55
        $this->assertEquals(0x06, $rng->nextInt(0x12)); //66
        $this->assertEquals(0x08, $rng->nextInt(99)); //77(119 -> 7bit) -> 88(136 -> 8bit -> 8)
        $this->assertEquals(0x99, $rng->nextInt(0x99)); //99
        $this->assertEquals(0xaa, $rng->nextInt(0xaa)); //aa (fit Max)

        $floatMax = 1 << 53;
        //$this->setName('DummyRNG-Float'); //7개씩
        $fa = $rng->nextFloat1();
        $this->assertEquals(0x1100ffeeddccbb / $floatMax, $fa, 'float1-1');
        $this->assertIsFloat($fa);
        $this->assertLessThan(0.5313720384, $fa);
        $this->assertGreaterThan(0.5313720383, $fa);

        $fb = $rng->nextFloat1();
        $this->assertEquals(0x08776655443322 / $floatMax, $fb, 'float1-2');
    }

    public function testRandUtilDummy_shuffle(){
        $rng = new DummyBlockRNG([
            fillBlock('', "\x17\x16\x15\x14\x13\x12\x11\x10")
        ]);
        $randUtil = new RandUtil($rng);

        /**
         * 7, [7,1,2,3,4,5,6,0]
         * 6, [7,0,2,3,4,5,6,1]
         * 5, [7,0,1,3,4,5,6,2]
         * 4, [7,0,1,2,4,5,6,3]
         * 3, [7,0,1,2,3,5,6,4]
         * 2, [7,0,1,2,3,4,6,5]
         * 1, [7,0,1,2,3,4,5,6]
         */
        $this->assertEquals(
            $randUtil->shuffle(range(0, 7)),
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
        $this->assertEquals(
            $randUtil->shuffle(range(0, 9)),
            [0, 8, 1, 2, 3, 4, 5, 6, 7, 9]
        );


        $rng = new DummyBlockRNG([
            fillBlock('', "\x17\x16\x15\x14\x13\x12\x11\x10")
        ]);
        $randUtil = new RandUtil($rng);
        //Same as first, but assoc.
        $this->assertEquals($randUtil->shuffleAssoc([
            'a' => 0,
            'b' => 1,
            'c' => 2,
            'd' => 3,
            'e' => 4,
            'f' => 5,
            'g' => 6,
            'h' => 7,
        ]), [
            'h' => 7,
            'a' => 0,
            'b' => 1,
            'c' => 2,
            'd' => 3,
            'e' => 4,
            'f' => 5,
            'g' => 6,
        ]);
    }

    public function testRandUtilDummy_choiceSeries(){
        $rng = new DummyBlockRNG([
            fillBlock('', "\x17\x16\x15\x14\x13\x12\x11\x10")
        ]);
        $randUtil = new RandUtil($rng);

        //0x17(7), 0x16(6)
        $this->assertEquals($randUtil->choice([0, 1, 2, 3, 4, 5]), 5);

        //0x15(5), Set 순서 유지
        $this->assertEquals($randUtil->choice([5, 3, 1, 2, 8, 0]), 8);

        //0x14(4), Js의 Object와 순서 다름!
        $this->assertEquals($randUtil->choice([
            2=>'t', 3=>'q', 4=>'x',
            'c'=> 'c', 'a'=> 'a', 'b'=> 'b'
        ]), 'c');

        //0.6275740099377194 * 38.1 = 23.91
        $this->assertEquals($randUtil->choiceUsingWeight([
            "a"=> 0.1,
            "b"=> 10,
            "tt"=> 2,
            "x"=> -1,
            "c"=> 20,
            "d"=> 0,
            "e"=> 6
        ]), 'c');

        //0.658946544056166
        $this->assertEquals($randUtil->choiceUsingWeightPair([
            ['xx', 10],
        ]), 'xx');

        //0.6903152783785083 * 27.3 = 18.84560709973328
        $this->assertEquals($randUtil->choiceUsingWeightPair([
            ['e', 10],
            ['d', 4],
            ['c', 0.1],
            ['baba', 0.2],
            ['q', 9],
            ['xt', 4]
        ]), 'q');
    }

    public function testRNGInvalidNextBits0()
    {
        $rng = new LiteHashDRBG(self::FIXED_KEY);
        $this->expectException('\InvalidArgumentException');
        $rng->nextBits(0);
    }

    public function testRNGInvalidNextBitsMinus1()
    {
        $rng = new LiteHashDRBG(self::FIXED_KEY);
        $this->expectException('\InvalidArgumentException');
        $rng->nextBits(-1);
    }

    public function testRNGInvalidNextBytes0()
    {
        $rng = new LiteHashDRBG(self::FIXED_KEY);
        $this->expectException('\InvalidArgumentException');
        $rng->nextBytes(0);
    }

    public function testRNGInvalidNextBytesMinus1()
    {
        $rng = new LiteHashDRBG(self::FIXED_KEY);
        $this->expectException('\InvalidArgumentException');
        $rng->nextBytes(-1);
    }

    public function testRNGInvalidNextIntOverflow()
    {
        $rng = new LiteHashDRBG(self::FIXED_KEY);
        $this->expectException('\InvalidArgumentException');
        $rng->nextInt(1 << 53);
    }

    public function testRandUtilEmptyChoice()
    {
        $this->expectException('\InvalidArgumentException');
        $rng = new LiteHashDRBG(self::FIXED_KEY);
        $randUtil = new RandUtil($rng);
        $randUtil->choice([]);
    }

    public function testRandUtilEmptychoiceUsingWeight()
    {
        $this->expectException('\InvalidArgumentException');
        $rng = new LiteHashDRBG(self::FIXED_KEY);
        $randUtil = new RandUtil($rng);
        $randUtil->choiceUsingWeight([]);
    }

    public function testRandUtilEmptychoiceUsingWeightPair()
    {
        $this->expectException('\InvalidArgumentException');
        $rng = new LiteHashDRBG(self::FIXED_KEY);
        $randUtil = new RandUtil($rng);
        $randUtil->choiceUsingWeightPair([]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testRNGAcceptable()
    {
        $rng = new LiteHashDRBG(self::FIXED_KEY);
        $rng->nextInt(0);
        $rng->nextInt(1 << 53 - 1);
        $rng->nextBytes(65);
        $rng->nextBits(512);

        $randUtil = new RandUtil($rng);
        $randUtil->choice([0, 0, 0]);
        $randUtil->choiceUsingWeight([0 => 0, 1 => -1]);
        $randUtil->choiceUsingWeightPair([
            [0, 0],
            [1, 0],
            [2, -1],
        ]);
        $randUtil->nextBool(1.1);
        $randUtil->nextBool(-0.1);
        $randUtil->shuffle([]);
        $randUtil->shuffle([1]);
        $randUtil->shuffleAssoc([]);
        $randUtil->shuffleAssoc([1]);
        $randUtil->nextRange(0, 0);
        $randUtil->nextRangeInt(0, 0);
        $randUtil->nextRange(1, -1);
        $randUtil->nextRangeInt(1, -1);

        $longKey = self::FIXED_KEY;
        foreach(range(0, 7) as $_){
            $longKey.=$longKey;
        }
        $rngLong = new LiteHashDRBG($longKey);
        foreach(range(0, 9) as $_){
            $rngLong->nextBytes(16);
        }
    }

    public function testRNGBytes()
    {
        $testVector = getRngTestVector();

        $rng = new LiteHashDRBG(static::FIXED_KEY);

        $offset = 0;
        $this->assertEquals($rng->nextBytes(10), substr($testVector, $offset, 10));
        $offset += 10;
        $this->assertEquals($rng->nextBytes(32), substr($testVector, $offset, 32));
        $offset += 32;
        $this->assertEquals($rng->nextBytes(1), substr($testVector, $offset, 1));
        $offset += 1;
        $this->assertEquals($rng->nextBytes(64), substr($testVector, $offset, 64));
        $offset += 64;
        $this->assertEquals($rng->nextBytes(5), substr($testVector, $offset, 5));
        $offset += 5;
        $this->assertEquals($rng->nextBytes(16), substr($testVector, $offset, 16));
        $offset += 16;
    }

    public function testRNGBits()
    {
        $testVector = getRngTestVector();

        $rng = new LiteHashDRBG(static::FIXED_KEY);

        $offset = 0;
        $testBits = [10, 4, 15, 32, 7, 99, 512, 1, 2, 3];

        foreach($testBits as $bits){
            $bytes = intdiv($bits + 7, 8);
            $A = $rng->nextBits($bits);
            $B = substr($testVector, $offset, $bytes);

            $offset += $bytes;

            if($bits % 8 != 0){
                $bitMask = 0xff >> (8 - ($bits % 8));
                $B[$bytes - 1] = chr(ord($B[$bytes - 1]) & $bitMask);
            }
            $this->assertEquals($A, $B);
        }
    }

    public function testRNGFloat()
    {
        $testVector = getRngTestVector();

        $rng = new LiteHashDRBG(static::FIXED_KEY);
        $rng2 = new DummyBlockRNG([
            substr($testVector, BLOCK_SIZE * 0, BLOCK_SIZE),
            substr($testVector, BLOCK_SIZE * 1, BLOCK_SIZE),
            substr($testVector, BLOCK_SIZE * 2, BLOCK_SIZE),
            substr($testVector, BLOCK_SIZE * 3, BLOCK_SIZE),
            substr($testVector, BLOCK_SIZE * 4, BLOCK_SIZE),
        ]);

        foreach(range(0, 17) as $idx){
            $this->assertEquals($rng->nextFloat1(), $rng2->nextFloat1(), "float{$idx}");
        }
    }
}
