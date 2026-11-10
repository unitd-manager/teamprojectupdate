<?
class CP_Admin_Modules_Tradingsg_ProductGroupLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
   /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT pg.*
        FROM product_group pg
        ";
        
        return $SQL;
    }

   /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 'pg';

        $product_group_id  = $fn->getReqParam('product_group_id');


        if ($product_group_id != "") {
            $searchVar->sqlSearchVar['product_group_id'] = "pg.product_group_id = '{$product_group_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar['product_group_id'] = "pg.product_group_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'pg.product_group_id');
        }
    }

}