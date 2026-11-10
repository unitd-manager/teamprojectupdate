<?
class CP_Admin_Modules_EnterpriseIms_AssessorLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getSQL($linkRecType) {
        $tv = Zend_Registry::get('tv');
        $modObj = getCPModuleObj('enterpriseIms_teacher');
        return $modObj->model->getSQL($linkRecType);
    }

    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');

        $modObj = getCPModuleObj('enterpriseIms_teacher');
        $modObj->model->setSearchVar($linkRecType);

        if ($tv['srcRoom'] == 'enterpriseIms_batch'){
            $searchVar->sqlSearchVar[] = "(t.trainer_type = 'Both' OR t.trainer_type = 'Assessor')";
        }
    }
    
    /**
     *
     */
    function getSQLForPager() {
    }      
}
