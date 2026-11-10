<?
class CP_Www_Modules_WebBasic_News_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getSQL() {
        return getCPModuleObj('webBasic_content')->model->getSQL();
    }

    /**
     *
     */
    function setSearchVar() {
        return getCPModuleObj('webBasic_content')->model->setSearchVar();
    }
}