<?
class CP_Admin_Modules_Tradingus_CompanyLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $modObj = getCPModuleObj('tradingsg_company');
        $modObj->model->setSearchVar($linkRecType);

        if ($tv['srcRoom'] == 'tradingsg_product'){
            $searchVar->sqlSearchVar[] = "(c.category = 'Supplier')";
        }
    }
    
}