<?
class CP_Common_Modules_Edukloud_Parent_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        
        $SQL = "
        SELECT p.*
              ,CONCAT_WS(' ', p.first_name, p.last_name ) AS parent_name
        FROM parent p
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        
        $searchVar->mainTableAlias = 'p';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.parent_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.parent_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   p.first_name LIKE '%{$tv['keyword']}%'
                OR p.last_name LIKE '%{$tv['keyword']}%'
                )";
            }
        }

    }
}
