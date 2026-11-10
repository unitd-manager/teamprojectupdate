<?
class CP_Www_Modules_Edukloud_ClassLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    /**
     *
     */
    function getSQL($linkRecType) {
        $modObj = getCPModuleObj('edukloud_class');
        return $modObj->model->getSQL($linkRecType);
    }

    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');

        $modObj = getCPModuleObj('edukloud_class');
        $modObj->model->setSearchVar($linkRecType);
    }
}