<?php
namespace sammo\TextDecoration;
use \sammo\Util;

class SightseeingMessage{

    const IncExp            =    0x1;
    const IncHeavyExp       =    0x2;
    const IncLeadership     =   0x10;
    const IncPower          =   0x20;
    const IncIntel          =   0x40;
    const IncGold           =  0x100;
    const IncRice           =  0x200;
    const DecGold           =  0x400;
    const DecRice           =  0x800;
    const Wounded           = 0x1000;
    const HeavyWounded      = 0x2000;

    static protected $messages = null;

    protected static function getMessageList():array{
        /* [[속성, [
                텍스트 리스트,
                텍스트 리스트2,
            ]], 가중치]
            (가중치는 텍스트 수와 연관이 없음)
         */
        return [
            [[self::IncExp,[
                '아무일도 일어나지 않았습니다.',
                '명사와 설전을 벌였으나 망신만 당했습니다.',
                '동네 장사와 힘겨루기를 했지만 망신만 당했습니다.',
            ]], 1],
            [[self::IncHeavyExp, [
                '주점에서 사람들과 어울려 술을 마셨습니다.',
                '위기에 빠진 사람을 구해주었습니다.',
            ]], 1],
            [[self::IncHeavyExp|self::IncLeadership, [
                '백성들에게 현인의 가르침을 설파했습니다.',
                '어느 집의 도망친 가축을 되찾아 주었습니다.',
            ]], 2],
            [[self::IncHeavyExp|self::IncPower,[
                '동네 장사와 힘겨루기를 하여 멋지게 이겼습니다.',
                '어느 집의 무너진 울타리를 고쳐주었습니다.',
            ]], 2],
            [[self::IncHeavyExp|self::IncIntel,[
                '어느 명사와 설전을 벌여 멋지게 이겼습니다.',
                '거리에서 글 모르는 아이들을 모아 글을 가르쳤습니다.',
            ]], 2],
            [[self::IncExp|self::IncGold,[
                '지나가는 행인에게서 금을 <C>:goldAmount:</> 받았습니다.',
            ]], 1],
            [[self::IncExp|self::IncRice,[
                '지나가는 행인에게서 쌀을 <C>:riceAmount:</> 받았습니다.',
            ]], 1],
            [[self::IncExp|self::DecGold,[
                '산적을 만나 금 <C>:goldAmount:</>을 빼앗겼습니다.',
                '돈을 <C>:goldAmount:</> 빌려주었다가 떼어먹혔습니다.',
            ]], 1],
            [[self::IncExp|self::DecRice,[
                '쌀을 <C>:riceAmount:</> 빌려주었다가 떼어먹혔습니다.',
            ]], 1],
            [[self::IncExp|self::Wounded,[
                '호랑이에게 물려 다쳤습니다.',
                '곰에게 할퀴어 다쳤습니다.',
            ]], 1],
            [[self::IncHeavyExp|self::Wounded,[
                '위기에 빠진 사람을 구해주다가 다쳤습니다.',
            ]], 1],
            [[self::IncExp|self::HeavyWounded,[
                '호랑이에게 물려 크게 다쳤습니다.',
                '곰에게 할퀴어 크게 다쳤습니다.',
            ]], 1],
            [[self::IncHeavyExp|self::Wounded|self::HeavyWounded,[
                '위기에 빠진 사람을 구하다가 죽을뻔 했습니다.',
            ]], 1],
            [[self::IncHeavyExp|self::IncPower|self::IncGold,[
                '산적과 싸워 금 <C>:goldAmount:</>을 빼앗았습니다.',
            ]], 1],
            [[self::IncHeavyExp|self::IncPower|self::IncRice,[
                '호랑이를 잡아 고기 <C>:riceAmount:</>을 얻었습니다.',
                '곰을 잡아 고기 <C>:riceAmount:</>을 얻었습니다.',
            ]], 1],
            [[self::IncHeavyExp|self::IncIntel|self::IncGold,[
                '돈을 빌려주었다가 이자 <C>:goldAmount:</>을 받았습니다.',
            ]], 1],
            [[self::IncHeavyExp|self::IncIntel|self::IncRice,[
                '쌀을 빌려주었다가 이자 <C>:riceAmount:</>을 받았습니다.',
            ]], 1],
            
        ];
    }

    protected static function initMessageList():void{
        static::$messages = static::getMessageList();
    }

    function __construct()
    {
        //TODO: 장수 이름이 들어가는 경우도 고려?
        if(static::$messages === null){
            static::initMessageList();
        }
    }

    public function pickAction():array{
        [$type, $texts] = Util::choiceRandomUsingWeightPair(static::$messages);
        $text = Util::choiceRandom($texts);

        return [$type, $text];
    }
}