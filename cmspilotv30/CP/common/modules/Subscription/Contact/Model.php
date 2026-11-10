<?
class CP_Common_Modules_Subscription_Contact_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');      
        
        $SQL = "
        SELECT c.*
        FROM contact c
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
        $searchVar->mainTableAlias = 'c';

		$contact_id = $fn->getReqParam('contact_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.contact_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   c.first_name LIKE '%{$tv['keyword']}%'
                OR c.last_name LIKE '%{$tv['keyword']}%'
                )";
            }
        }

    }
}
