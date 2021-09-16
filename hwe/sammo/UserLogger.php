<?php

namespace sammo;

class UserLogger
{
    protected $userID;
    protected $year;
    protected $month;
    protected $autoFlush;
    protected $log = [];

    function __construct(int $userID, ?int $year = null, ?int $month = null, bool $autoFlush = true)
    {
        $this->userID = $userID;
        if ($year === null || $month === null) {
            $db = DB::db();
            $gameStor = KVStorage::getStorage($db, 'game_env');
            $gameStor->cacheValues(['year', 'month']);

            if ($year === null) {
                $year = $gameStor->year;
            }
            if ($month === null) {
                $month = $gameStor->month;
            }
        }
        $this->year = $year;
        $this->month = $month;
        $this->autoFlush = $autoFlush;
    }

    public function __destruct()
    {
        if ($this->autoFlush) {
            $this->flush();
        }
    }

    public function rollback()
    {
        $backup = $this->log;
        $this->log = [];

        return $backup;
    }

    public function flush()
    {
        if(!$this->log){
            return;
        }
        if(!$this->userID){
            return;
        }

        $db = DB::db();
        $date = TimeUtil::now();
        $serverID = UniqueConst::$serverID;
        $request = array_map(function ($textAndType) use ($date, $serverID) {
            [$text, $type] = $textAndType;
            return [
                'user_id' => $this->userID,
                'server_id' => $serverID,
                'log_type' => $type,
                'year' => $this->year,
                'month' => $this->month,
                'date' => $date,
                'text' => $text
            ];
        }, array_values($this->log));
        $db->insert('user_record', $request);
        $this->log = [];
    }

    public function push($text, string $type)
    {
        if (!$text) {
            return;
        }
        if (is_array($text)) {
            foreach ($text as $textItem) {
                $this->log[] = [$textItem, $type];
            }
            return;
        }
        $this->log[] = [$text, $type];
    }
}
