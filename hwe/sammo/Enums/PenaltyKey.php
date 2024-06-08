<?php
namespace sammo\Enums;

/**
 * Penalty Key
 * general의 penalty 항목
 */
enum PenaltyKey: string{
    case SendPrivateMsgDelay = 'sendPrivateMsgDelay';
    case NoTopSecret = 'noTopSecret';
    case NoChief = 'noChief';
    case NoAmbassador = 'noAmbassador';
}