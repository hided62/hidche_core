<?php
namespace sammo;

class TurnExecutionHelper
{
    protected $generalID;
    protected $generalObj;

    public function __construct(int $generalID, int $year, int $month)
    {
        $db = DB::db();
        $this->generalObj = new General();
    }
}