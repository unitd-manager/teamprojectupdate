<?
class CP_Common_Modules_Core_Valuelist_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT * 
        FROM valuelist v
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'v';

        $valuelist_id = $fn->getReqParam('valuelist_id');
        $key_text     = $fn->getReqParam('key_text');

        if ($valuelist_id != '' ) {
            $searchVar->sqlSearchVar[] = "v.valuelist_id  = '{$valuelist_id}'";

        } else if ($tv['record_id'] != ''){
            $searchVar->sqlSearchVar[] = "v.valuelist_id  = '{$tv['record_id']}'";

        } else {
            if ($key_text != '' ) {
                $searchVar->sqlSearchVar[] = "v.key_text  = '{$key_text}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    v.key_text    LIKE '%{$tv['keyword']}%'  OR
                    v.value       LIKE '%{$tv['keyword']}%'
                )";
            }
            $searchVar->sortOrder = "v.sort_order ASC";
            
        }
    }

    /**
     *
     */
    function getValuelistSQL($key_text, $exp = array()) {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $useCode = $fn->getIssetParam($exp, 'useCode', false);
        // if you need to display the text in Chi but want to store the english value, turn this to true //
        $useEngValueAsKey = $fn->getIssetParam($exp, 'useEngValueAsKey', false); 
        $orderBy = $fn->getIssetParam($exp, 'orderBy', 'sort_order ASC, value ASC');
                
        $lnPfx = $ln->getFieldPrefix();
        
        if ($useEngValueAsKey){
            $keyFld = 'value';
        } else {
            $keyFld = $useCode ? 'code' : 'valuelist_id';
        }
        
        $sql = "
        SELECT {$keyFld}
              ,IF({$lnPfx}value != '', {$lnPfx}value, value)AS vl_text
        FROM valuelist 
        WHERE key_text = '{$key_text}'
        ORDER BY {$orderBy}
        ";
        return $sql;
    }

    function getValuelistAsArray($valuelist, $exp = array()) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL    = $this->getValueListSQL($valuelist, $exp);
        $result = $db->sql_query($SQL);
        $arr    = $dbUtil->getResultsetAsArrayForForm($result);

        return $arr;
    }   
}
