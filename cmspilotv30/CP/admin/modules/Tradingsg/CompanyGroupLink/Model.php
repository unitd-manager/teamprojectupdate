<?
class CP_Admin_Modules_Tradingsg_CompanyGroupLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{

    /**
     *
     */
    function getSQL($linkRecType) {
        $tv = Zend_Registry::get('tv');
        $modObj = getCPModuleObj('tradingsg_company');
        return $modObj->model->getSQL($linkRecType);
    }


    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $modObj = getCPModuleObj('tradingsg_company');
        $modObj->model->setSearchVar($linkRecType);

    }
    
}