<?
class CPL_Admin_Lib_SpecialAction Extends CP_Common_Lib_SpecialAction
{

    //==================================================================//
    function getCreateDeleteLinkRecord() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $arrayMasterLink = Zend_Registry::get('arrayMasterLink');

        $srcRoom           = $tv['srcRoom'];
        $lnkRoom           = $tv['lnkRoom'];
        $record_id         = $fn->getReqParam('record_id');
        $linkMasterTableID = $fn->getReqParam('linkMasterTableID');
        $currentValue      = $fn->getReqParam('currentValue');

        $clsInst = getCPModuleObj($srcRoom);
        $clsInst->fns->setLinksArray($arrayMasterLink);
        $linksArray     = $arrayMasterLink->linksArray;

        $linkName             = $srcRoom . "#" . $lnkRoom;
        $mainRoomKeyField     = $modulesArr[$srcRoom]["keyField"];
        $linkRoomKeyField     = $modulesArr[$lnkRoom]["keyField"];
        $historyTableName     = $linksArray[$linkName]["historyTableName"];
        $historyTableKeyField = $linksArray[$linkName]["historyTableKeyField"];
        $recordTypeForHistory = $linksArray[$linkName]["recordTypeForHistory"];
        $keyFieldForLinking   = $linksArray[$linkName]["keyFieldForLinking"];
        $mainRoomKeyFldNameInHistTbl = $linksArray[$linkName]["mainRoomKeyFldNameInHistTbl"];
        $linkRoomKeyFldNameInHistTbl = $linksArray[$linkName]["linkRoomKeyFldNameInHistTbl"];

        if ($mainRoomKeyFldNameInHistTbl != ""){
            $mainRoomKeyField  = $mainRoomKeyFldNameInHistTbl;
        }

        if ($keyFieldForLinking != ""){
            $linkRoomKeyField  = $keyFieldForLinking;
        }

        if ($linkRoomKeyFldNameInHistTbl != ""){
            $linkRoomKeyField = $linkRoomKeyFldNameInHistTbl;
        }

        if (!is_numeric ($record_id) || !is_numeric ($linkMasterTableID)) {
            return;
        }

        $append = '';
        if ($recordTypeForHistory != ''){
            $append = "AND record_type = '{$recordTypeForHistory}'";
        }

        $srcRmArr = explode('_', $srcRoom);
        $lnkRmArr = explode('_', $lnkRoom);
        if ($currentValue == 1) {
            $SQL = "
            SELECT {$linkRoomKeyField}
            FROM `{$historyTableName}`
            WHERE {$mainRoomKeyField}= {$linkMasterTableID}
              AND {$linkRoomKeyField} = {$record_id}
              {$append}
            ";
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if ($numRows == 0) {
                $fieldValuesArray = array();
                $fa = &$fieldValuesArray;

                $fa[$mainRoomKeyField] = $linkMasterTableID;
                $fa[$linkRoomKeyField] = $record_id;

                if ($srcRoom == "common_broadcast" && ($lnkRoom == "common_contactLink" ||  $lnkRoom == "common_testRecipientLink")) {
                    $fa['send_tag'] = '0';
                    $fa['status']  = 'To Be Sent';
                }

                if ($recordTypeForHistory != ''){
                    $fa['record_type'] = $recordTypeForHistory;
                }

                if($srcRoom = 'enggCrm_project' && $lnkRoom = 'enggCrm_employeeLink'){
                    $fa['active_in_project']  = 1;
                }

                $fa['creation_date']     = date("Y-m-d H:i:s");
                $fa['modification_date'] = date("Y-m-d H:i:s");

                $SQL   = $dbUtil->getInsertSQLStringFromArray($fieldValuesArray, $historyTableName);
                $result = $db->sql_query($SQL);
                $history_table_id = $db->sql_nextid();


                //ex: getTradingEnquiryTradingProductAddLinkCallback
                $funcName = "get" . ucfirst($srcRmArr[0]) . ucfirst($srcRmArr[1]) .
                                    ucfirst($lnkRmArr[0]) . ucfirst($lnkRmArr[1]) .
                                    "AddLinkCallback";
                $fnMod = getCPModuleObj($srcRoom)->fns;
                if (method_exists($fnMod, $funcName)) {
                    $historyRow = $fn->getRecordRowByID($historyTableName, $historyTableKeyField, $history_table_id);
                    $fnMod->$funcName($history_table_id, $historyRow);
                }
            }

        } else {
            $SQL = "
            DELETE FROM `{$historyTableName}`
            WHERE {$mainRoomKeyField} = {$linkMasterTableID}
              AND {$linkRoomKeyField} = {$record_id}
              {$append}
            ";

            //ex: getTradingEnquiryTradingProductDeleteLinkCallback
            $funcName = "get" . ucfirst($srcRmArr[0]) . ucfirst($srcRmArr[1]) .
                                ucfirst($lnkRmArr[0]) . ucfirst($lnkRmArr[1]) .
                                "DeleteLinkCallback";
            $fnMod = getCPModuleObj($srcRoom)->fns;
            if (method_exists($fnMod, $funcName)) {
                $fnMod->$funcName($linkMasterTableID, $record_id);
            }
            //result is placed here to use any values in the above call back function before //deleting the record.
            $result = $db->sql_query($SQL);
        }
    }
}