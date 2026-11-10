<?
class CP_Admin_Modules_Pos_ValuelistLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getSQL($linkRecType) {
        $modObj = getCPModuleObj('pos_valuelist');
        return $modObj->model->getSQL($linkRecType);
    }

    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');

        $modObj = getCPModuleObj('pos_valuelist');
        $modObj->model->setSearchVar($linkRecType);
    }
}
