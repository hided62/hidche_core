<?php

namespace sammo\Constraint;

class ConstraintHelper{
    
    static function AdhocCallback(callable $callback):array{
        return [__FUNCTION__, $callback];
    }

    static function AllowDiplomacyStatus(int $nationID, array $allowList, string $errMsg):array{
        return [__FUNCTION__, [$nationID, $disallowList, $errMsg]];
    }

    static function AllowDiplomacyBetweenStatus(array $allowDipCodeList, string $errMsg):array{
        return [__FUNCTION__, [$allowDipCodeList, $errMsg]];
    }

    static function AllowDiplomacyWithTerm(int $allowDipCode, int $allowMinTerm, string $errMsg):array{
        return [__FUNCTION__, [$allowDipCode, $allowMinTerm, $errMsg]];
    }
    
    static function AllowJoinAction():array{
        return [__FUNCTION__];
    }

    static function AllowJoinDestNation(int $relYear):array{
        return [__FUNCTION__, $relYear];
    }

    static function AllowRebellion():array{
        return [__FUNCTION__];
    }

    static function AllowWar():array{
        return [__FUNCTION__];
    }

    static function AlwaysFail(string $failMessage):array{
        return [__FUNCTION__, $failMessage];
    }

    static function AvailableRecruitCrewType(int $crewTypeID):array{
        return [__FUNCTION__, $crewTypeID];
    }

    static function AvailableStrategicCommand():array{
        return [__FUNCTION__];
    }

    static function BattleGroundCity():array{
        return [__FUNCTION__];
    }

    static function BeChief():array{
        return [__FUNCTION__];
    }

    static function BeLord():array{
        return [__FUNCTION__];
    }

    static function BeNeutral():array{
        return [__FUNCTION__];
    }

    static function BeOpeningPart(int $relYear):array{
        return [__FUNCTION__, $relYear];
    }
    
    static function CheckNationNameDuplicate(string $nationName):array{
        return [__FUNCTION__, $nationName];
    }
    
    static function ConstructableCity():array{
        return [__FUNCTION__];
    }

    static function DifferentNationDestGeneral():array{
        return [__FUNCTION__];
    }

    static function DifferentDestNation():array{
        return [__FUNCTION__];
    }

    static function DisallowDiplomacyBetweenStatus(array $disallowList):array{
        return [__FUNCTION__, $disallowList];
    }

    static function DisallowDiplomacyStatus(int $nationID, array $disallowList):array{
        return [__FUNCTION__, [$nationID, $disallowList]];
    }

    static function ExistsAllowJoinNation(int $relYear, array $excludeNationList):array{
        return [__FUNCTION__, [$relYear, $excludeNationList]];
    }

    static function ExistsDestGeneral():array{
        return [__FUNCTION__];
    }
    
    static function ExistsDestNation():array{
        return [__FUNCTION__];
    }
    
    static function FriendlyDestGeneral():array{
        return [__FUNCTION__];
    }

    static function HasRoute():array{
        return [__FUNCTION__];
    }

    static function HasRouteWithEnemy():array{
        return [__FUNCTION__];
    }

    static function MustBeNPC():array{
        return [__FUNCTION__];
    }

    static function MustBeTroopLeader():array{
        return [__FUNCTION__];
    }
    
    static function NearCity(int $distance):array{
        return [__FUNCTION__, $distance];
    }

    static function NearNation():array{
        return [__FUNCTION__];
    }

    static function NotBeNeutral():array{
        return [__FUNCTION__];
    }

    static function NotCapital(bool $ignoreOfficer=false):array{
        return [__FUNCTION__, $ignoreOfficer];
    }

    static function NotChief():array{
        return [__FUNCTION__];
    }

    static function NotLord():array{
        return [__FUNCTION__];
    }

    static function NotNeutralDestCity():array{
        return [__FUNCTION__];
    }

    static function NotOccupiedDestCity():array{
        return [__FUNCTION__];
    }

    static function NotOpeningPart(int $relYear):array{
        return [__FUNCTION__, $relYear];
    }

    static function NotSameDestCity():array{
        return [__FUNCTION__];
    }

    static function NotWanderingNation():array{
        return [__FUNCTION__];
    }

    static function OccupiedCity(bool $allowNeutral=false):array{
        return [__FUNCTION__, $allowNeutral];
    }

    static function OccupiedDestCity():array{
        return [__FUNCTION__];
    }
    
    static function RemainCityCapacity($key, string $actionName):array{
        return [__FUNCTION__, [$key, $actionName]];
    }
    
    static function RemainCityTrust(string $actionName):array{
        return [__FUNCTION__, $actionName];
    }

    static function ReqCityCapacity($key, string $keyNick, $reqVal):array{
        return [__FUNCTION__, [$key, $keyNick, $reqVal]];
    }
    
    static function ReqCityTrust(float $minTrust):array{
        return [__FUNCTION__, $minTrust];
    }

    static function ReqCityValue($key, string $keyNick, string $comp, $reqVal, ?string $errMsg=null):array{
        return [__FUNCTION__, [$key, $keyNick, $comp, $reqVal, $errMsg]];
    }

    static function ReqDestCityValue($key, string $keyNick, string $comp, $reqVal, ?string $errMsg=null):array{
        return [__FUNCTION__, [$key, $keyNick, $comp, $reqVal, $errMsg]];
    }

    static function ReqCityTrader(int $npcType):array{
        return [__FUNCTION__, $npcType];
    }

    static function ReqDestNationValue($key, string $keyNick, string $comp, $reqVal, ?string $errMsg=null):array{
        return [__FUNCTION__, [$key, $keyNick, $comp, $reqVal, $errMsg]];
    }

    static function ReqEnvValue($key, string $comp, $reqVal, string $failMessage):array{
        return [__FUNCTION__, [$key, $comp, $reqVal, $failMessage]];
    }

    static function ReqGeneralAtmosMargin(int $maxAtmos):array{
        return [__FUNCTION__, $maxAtmos];
    }

    static function ReqGeneralCrew():array{
        return [__FUNCTION__];
    }

    static function ReqGeneralCrewMargin(int $crewTypeID):array{
        return [__FUNCTION__, $crewTypeID];
    }

    static function ReqGeneralGold(int $reqGold):array{
        return [__FUNCTION__, $reqGold];
    }

    static function ReqGeneralRice(int $reqRice):array{
        return [__FUNCTION__, $reqRice];
    }

    static function ReqGeneralTrainMargin(int $maxTrain):array{
        return [__FUNCTION__, $maxTrain];
    }

    static function ReqGeneralValue($key, string $keyNick, string $comp, $reqVal, ?string $errMsg=null):array{
        return [__FUNCTION__, [$key, $keyNick, $comp, $reqVal, $errMsg]];
    }

    static function ReqNationGold(int $reqGold):array{
        return [__FUNCTION__, $reqGold];
    }

    static function ReqNationRice(int $reqRice):array{
        return [__FUNCTION__, $reqRice];
    }

    static function ReqNationValue($key, string $keyNick, string $comp, $reqVal, ?string $errMsg=null):array{
        return [__FUNCTION__, [$key, $keyNick, $comp, $reqVal, $errMsg]];
    }

    static function ReqNationAuxValue($key, string $keyNick, string $comp, $reqVal, ?string $errMsg=null):array{
        return [__FUNCTION__, [$key, $keyNick, $comp, $reqVal, $errMsg]];
    }

    static function ReqTroopMembers():array{
        return [__FUNCTION__];
    }

    static function SuppliedCity():array{
        return [__FUNCTION__];
    }

    static function SuppliedDestCity():array{
        return [__FUNCTION__];
    }
    
    static function WanderingNation():array{
        return [__FUNCTION__];
    }
}