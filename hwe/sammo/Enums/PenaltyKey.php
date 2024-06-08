<?php

namespace sammo\Enums;

/**
 * Penalty Key
 * general의 penalty 항목
 */
enum PenaltyKey: string
{
    case SendPrivateMsgDelay = 'sendPrivateMsgDelay';
    case NoSendPrivateMsg = 'noSendPrivateMsg';
    case NoSendPublicMsg = 'noSendPublicMsg';
    case NoTopSecret = 'noTopSecret';
    case NoChief = 'noChief';
    case NoAmbassador = 'noAmbassador';
    case NoBanGeneral = 'noBanGeneral';
    case NoChiefTurnInput = 'noChiefTurnInput';
    case NoChiefChange = 'noChiefChange';
    case NoFoundNation = 'noFoundNation';
    case NoChosenAssignment = 'noChosenAssignment';

    public function getHelptext(): string
    {
        return match ($this) {
            PenaltyKey::SendPrivateMsgDelay => '개인 메세지 보내기 제한 시간',
            PenaltyKey::NoSendPrivateMsg => '개인 메세지 보내기 금지',
            PenaltyKey::NoSendPublicMsg => '공개 메세지 보내기 금지',
            PenaltyKey::NoTopSecret => '암행부 열람 금지',
            PenaltyKey::NoChief => '수뇌 금지',
            PenaltyKey::NoAmbassador => '외교권자 금지',
            PenaltyKey::NoBanGeneral => '장수 추방 금지',
            PenaltyKey::NoChiefTurnInput => '수뇌 턴 입력 금지',
            PenaltyKey::NoChiefChange => '수뇌 임명/해임 금지',
            PenaltyKey::NoFoundNation => '건국 금지',
            PenaltyKey::NoChosenAssignment => '지정 임관 금지',
            default => "페널티({$this->value})",
        };
    }
}
