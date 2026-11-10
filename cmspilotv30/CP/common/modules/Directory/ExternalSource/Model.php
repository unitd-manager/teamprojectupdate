<?
class CP_Common_Modules_Directory_ExternalSource_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $sql = "
        SELECT es.*
        	  ,co.title AS country_title
        FROM external_source es
        LEFT JOIN country co ON co.country_id = es.country_id
        ";
        return $sql;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'ec';
    }
}
