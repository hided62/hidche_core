<?php
require_once('_common.php');

class _DB {
    private $objDB;
    private $setting = 'none';

    public function __construct($host, $id, $pw, $db) {
        $this->objDB = ADONewConnection('mysqli'); // 예 'mysql' 또는 'postgres'
//        $this->objDB->debug = true;
        $this->objDB->debug = false;
        $this->objDB->Connect($host, $id, $pw, $db) or Error('DB Connect() error: '.$db);
        // 성능을 고려해 PConnect 조사
        $this->objDB->SetFetchMode(ADODB_FETCH_ASSOC);
        $this->objDB->Execute('set names utf8');

        $this->setting = "{$host}//{$id}//{$db}";
    }

    public function GetSetting() {
        return $this->setting;
    }

    public function QueryNoError($strQuery) {
        return $this->objDB->Execute($strQuery);
    }

    public function Query($strQuery) {
        $rs = $this->objDB->Execute($strQuery) or Error($strQuery);
        return $rs;
    }

    public function Count($rs) {
        if($rs == null) {
            return 0;
        }
        return $rs->RecordCount();
    }

    public function HasNext($rs) {
        return !$rs->EOF;
    }

    public function GetAll($rs) {
        return $rs->GetRows();
    }

    public function Get($rs) {
        return $rs->fields;
    }

    public function MoveNext($rs) {
        $rs->MoveNext();
    }

    public function Next($rs) {
        $obj = $rs->fields;
        $rs->MoveNext();
        return $obj;
    }

    public function Select($strFields, $strTable, $strCondition=NULL, $strGroupByField=NULL, $strHavingCondition=NULL) {
        $strQuery = "SELECT {$strFields} FROM {$strTable}";
        if($strCondition != NULL) {
            $strQuery .= " WHERE {$strCondition}";
            if($strGroupByField != NULL) {
                $strQuery .= " GROUP BY {$strGroupByField}";
                if($strHavingCondition != NULL) {
                    $strQuery .= " HAVING {$strHavingCondition}";
                }
            }
        }

        return $this->objDB->Execute($strQuery);
    }

    public function Insert($strTable, $strFields, $strValues) {
        $strQuery = "INSERT INTO {$strTable} ({$strFields}) VALUES ({$strValues})";
        $this->objDB->Execute($strQuery);
    }

    public function InsertArray($strTable, $arrVals) {
        $arrFields = array();
        $arrValues = array();

        foreach($arrVals as $strKey => $strVal) {
            $arrFields[] = $strKey;
            $arrValues[] = $strVal;
        }

        $strFields = implode(',', $arrFields);
        $strValues = implode("','", $arrValues);
        $strValues = "'".$strValues."'";

        $strQuery = "INSERT INTO {$strTable} ({$strFields}) VALUES ({$strValues})";

        $this->objDB->Execute($strQuery);
    }

    public function Update($strTable, $strSetting, $strCondition=NULL) {
        $strQuery = "UPDATE {$strTable} SET {$strSetting}";
        if($strCondition != NULL) {
            $strQuery .= " WHERE {$strCondition}";
        }

        $this->objDB->Execute($strQuery);
    }

    public function UpdateArray($strTable, $arrVals, $strCondition=NULL) {
        $arrSetting = array();

        foreach($arrVals as $strKey => $strVal) {
            $arrSetting[] = "{$strKey}='{$strVal}'";
        }

        $strSetting = implode(',', $arrSetting);

        $strQuery = "UPDATE {$strTable} SET {$strSetting}";
        if($strCondition != NULL) {
            $strQuery .= " WHERE {$strCondition}";
        }

        $this->objDB->Execute($strQuery);
    }

    public function Delete($strTable, $strCondition=NULL) {
        $strQuery = "DELETE FROM {$strTable}";
        if($strCondition != NULL) {
            $strQuery .= " WHERE {$strCondition}";
        }

        $this->objDB->Execute($strQuery);
    }

    public function __destruct() {
        $this->objDB->Close();
    }

    public function Qstr($str) {
        return $this->objDB->qstr($str);
    }
}


