<?
class CP_Admin_Modules_Pos_StyleLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getSQL($linkRecType) {
        $modObj = getCPModuleObj('pos_valuelistLink');
        return $modObj->model->getSQL($linkRecType);
    }

    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');

        $modObj = getCPModuleObj('pos_valuelistLink');
        $modObj->model->setSearchVar($linkRecType);

        $searchVar->sqlSearchVar[] = "v.key_text = 'style'";
    }
    
    /**
     *
     */
    function getSQLForPager() {
    }      
}
