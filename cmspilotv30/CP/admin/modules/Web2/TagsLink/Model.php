<?
class CP_Admin_Modules_Web2_TagsLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
   /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT t.*
        FROM tags t
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
        $searchVar->mainTableAlias = 't';

        $tags_id     = $fn->getReqParam('tags_id');


        if ($tags_id != "") {
            $searchVar->sqlSearchVar['tags_id'] = "t.tags_id = '{$tags_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar['tags_id'] = "t.tags_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 't.tags_id');
        }
    }

}