<?php

namespace sammo\Command;

use \sammo\Util;

abstract class UserActionCommand extends BaseCommand
{
    const USER_ACTION_KEY = 'user_action';

    public function getNextExecuteKey(): string
    {
        //NOTE: 일반 턴 체계와 다르므로 쓸 일은 없음
        $turnKey = static::$actionName;
        $userActionKey = static::USER_ACTION_KEY;
        $generalID = $this->getGeneral()->getID();
        $executeKey = "next_execute_{$generalID}_{$userActionKey}_{$turnKey}";
        return $executeKey;
    }

    public function getNextAvailableTurn(): ?int
    {
        if ($this->isArgValid && !$this->getPostReqTurn()) {
            return null;
        }
        $rawUserAction = $this->generalObj->getAuxVar(static::USER_ACTION_KEY);
        if ($rawUserAction === null) {
            return null;
        }
        $userAction = \sammo\DTO\UserAction::fromArray($rawUserAction);
        $nextAvailableTurn = $userAction->nextAvailableTurn;
        if ($nextAvailableTurn === null) {
            return null;
        }
        $nextAvailableTurn = $nextAvailableTurn[static::$actionName] ?? null;
        return $nextAvailableTurn;
    }

    public function setNextAvailable(?int $yearMonth = null)
    {
        if (!$this->getPostReqTurn()) {
            return;
        }
        $rawUserAction = $this->generalObj->getAuxVar(static::USER_ACTION_KEY);
        if ($rawUserAction === null) {
            $rawUserAction = [];
        }
        $userAction = \sammo\DTO\UserAction::fromArray($rawUserAction);

        if ($userAction->nextAvailableTurn === null) {
            $userAction->nextAvailableTurn = [];
        }

        if ($yearMonth === null) {
            $yearMonth = Util::joinYearMonth($this->env['year'], $this->env['month'])
                + $this->getPostReqTurn() - $this->getPreReqTurn();
        }
        $userAction->nextAvailableTurn[static::$actionName] = $yearMonth;
        $this->generalObj->setAuxVar(static::USER_ACTION_KEY, $userAction->toArray());
    }
};
