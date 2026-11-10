<?
class CP_Admin_Modules_Tradingsg_CategoryLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getSQL($linkRecType) {
        $modObj = getCPModuleObj('webBasic_category');
        return $modObj->model->getSQL($linkRecType);
    }

    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');

        $modObj = getCPModuleObj('webBasic_category');
        $modObj->model->setSearchVar($linkRecType);

        //$searchVar->sqlSearchVar[] = "v.key_text = 'size'";
    }
    
}