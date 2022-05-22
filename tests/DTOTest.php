<?php

use sammo\DTO\Attr\Convert;
use sammo\DTO\DTO;
use sammo\DTO\Attr\JsonString;
use sammo\DTO\Attr\NullIsUndefined;
use sammo\DTO\Converter\ArrayConverter;
use sammo\DTO\Converter\Converter;
use sammo\DTO\Converter\MapConverter;
use sammo\Json;

class TypeA extends DTO
{
  public function __construct(
    public string $a,
    public string $b,
    public bool $c,
    public ?int $d,
    public float $e,
  ) {
  }
}

class TypeBA extends DTO
{
  public function __construct(
    public int $ba1,
    public int $ba2,
  ) {
  }
}

class TypeB extends DTO
{
  public function __construct(
    public string $a,
    public TypeBA $ba,
  ) {
  }
}

class TypeC extends DTO
{
  public function __construct(
    public string $a,
    #[JsonString]
    public TypeBA $ba,
  ) {
  }
}

enum EnumA
{
  case A;
  case B;
  case cc;
}

class TypeD extends DTO
{
  public function __construct(
    public ?string $a,
    public EnumA $b,
  ) {
  }
}

class TypeE extends DTO
{
  public function __construct(
    public ?string $a,
    #[NullIsUndefined]
    public ?string $b,
  ) {
  }
}

class ConverterDouble implements Converter
{
  public function __construct(array $types, ...$args)
  {
  }
  public function convertFrom(string|array|int|float|bool|null $raw): mixed
  {
    return $raw * 2;
  }
  public function convertTo(mixed $data): string|array|int|float|bool|null
  {
    return $data / 2;
  }
}

class TypeF extends DTO
{
  public function __construct(
    public ?string $a,
    #[Convert(ConverterDouble::class)]
    public int $b,
  ) {
  }
}

class TypeArr extends DTO
{
  public function __construct(
    public array $a,
    public array $b,
  ) {
  }
}


class TypeArrConverter extends DTO
{
  public function __construct(
    public array $a,
    #[Convert(ArrayConverter::class, ['string'])]
    public array $b,
  ) {
  }
}


class TypeMap extends DTO
{
  public function __construct(
    public int $a,
    #[Convert(MapConverter::class, ['null', 'string'])]
    public array $b,
  ) {
  }
}

class TypeNestedMap extends DTO
{
  public function __construct(
    public int $a,
    #[Convert(MapConverter::class, ['null', TypeBA::class])]
    public array $b,
  ) {
  }
}

class DTOTest extends PHPUnit\Framework\TestCase
{
  public function testBasic()
  {
    $rawType = [
      'a' => '123',
      'b' => 'aa',
      'c' => false,
      'd' => null,
      'e' => 123.123,
    ];
    $obj = TypeA::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testNested()
  {
    $rawType = [
      'a' => '123',
      'ba' => [
        'ba1' => 3,
        'ba2' => 4,
      ]
    ];
    $obj = TypeB::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testNestedJson()
  {
    $x = Json::encode([
      'ba1' => 3,
      'ba2' => 4,
    ]);
    $rawType = [
      'a' => '123',
      'ba' => $x
    ];
    $obj = TypeC::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testEnum()
  {
    $rawType = [
      'a' => '123',
      'b' => 'B',
    ];
    $obj = TypeD::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testNull()
  {
    $rawType = [
      'a' => null,
    ];
    $obj = TypeE::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testConverter()
  {
    $rawType = [
      'a' => '123',
      'b' => 123,
    ];
    $obj = TypeF::fromArray($rawType);
    $this->assertEquals($obj->b, 246);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testNullIsUndefined()
  {
    $rawType = [
      'a' => null,
    ];
    $obj = TypeE::fromArray($rawType);
    $this->assertEquals($obj->a, null);
    $this->assertEquals($obj->b, null);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testArr()
  {
    $rawType = [
      'a' => [1, 2, 3],
      'b' => [4, 5, 6],
    ];
    $obj = TypeArr::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testArrConverterErr()
  {
    $this->expectException(Exception::class);
    $rawType = [
      'a' => [1, 2, 3],
      'b' => [4, 5, 6],
    ];
    $obj = TypeArrConverter::fromArray($rawType);
    $testType = $obj->toArray();


    $this->assertEquals($rawType, $testType);
  }

  public function testArrConverterErr2()
  {
    $this->expectException(Exception::class);
    $rawType = [
      'a' => [1, 2, 3],
      'b' => [4 => 6, 5 => 2, 6 => 3],
    ];
    $obj = TypeArrConverter::fromArray($rawType);
    $testType = $obj->toArray();


    $this->assertEquals($rawType, $testType);
  }

  public function testArrConverter()
  {
    $rawType = [
      'a' => [1, 2, 3],
      'b' => ['1', '2', '3'],
    ];
    $obj = TypeArrConverter::fromArray($rawType);
    $testType = $obj->toArray();


    $this->assertEquals($rawType, $testType);
  }

  public function testMap(){
    $rawType = [
      'a' => 1,
      'b' => [
        '1' => '1',
        '2' => null,
        'ba' => '3',
      ],
    ];
    $obj = TypeMap::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }

  public function testNestedMap(){
    $rawType = [
      'a' => 1,
      'b' => [
        'aaa' => [
          'ba1' => 1,
          'ba2' => 2,
        ],
        'xed' => null,
        'ccc' => [
          'ba1' => 3,
          'ba2' => 4,
        ],
      ],
    ];
    $obj = TypeNestedMap::fromArray($rawType);
    $testType = $obj->toArray();

    $this->assertEquals($rawType, $testType);
  }
}
