<?php
namespace sammo\Enums;

/**
 * Penalty Key
 * general의 penalty 항목
 */
enum PenaltyKey: string{
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
}