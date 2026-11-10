<?
class CP_Admin_Modules_Pms_AssessorLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getSQL($linkRecType) {
        $tv = Zend_Registry::get('tv');
        $modObj = getCPModuleObj('pms_teacher');
        return $modObj->model->getSQL($linkRecType);
    }

    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');

        $modObj = getCPModuleObj('pms_teacher');
        $modObj->model->setSearchVar($linkRecType);

        if ($tv['srcRoom'] == 'pms_batch'){
            $searchVar->sqlSearchVar[] = "(t.trainer_type = 'Both' OR t.trainer_type = 'Assessor')";
        }
    }
    
    /**
     *
     */
    function getSQLForPager() {
    }      
}
