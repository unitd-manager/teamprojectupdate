<?
class CP_Www_Modules_WebBasic_Gallery_Model extends CP_Common_Lib_ModuleModelAbstract
{
    //==================================================================//
    function getSQL() {
        $modObj = getCPModuleObj('webBasic_content');
        return $modObj->model->getSQL();
    }

    //==================================================================//
    function setSearchVar($linkRecType) {
        $modObj = getCPModuleObj('webBasic_content');
        $modObj->model->setSearchVar($linkRecType);
    }
}