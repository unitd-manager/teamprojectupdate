<?
class CP_Admin_Modules_AceIms_AssessorLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getSQL($linkRecType) {
        $modObj = getCPModuleObj('aceIms_teacher');
        return $modObj->model->getSQL($linkRecType);
    }

    /**
     *
     */
    function setSearchVar($linkRecType) {
        $tv        = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');

        $modObj = getCPModuleObj('aceIms_teacher');
        $modObj->model->setSearchVar($linkRecType);

        if ($tv['srcRoom'] == 'aceIms_batch'){
            $searchVar->sqlSearchVar[] = "(t.trainer_type = 'Both' OR t.trainer_type = 'Assessor')";
        }
    }
    
    /**
     *
     */
    function getSQLForPager() {
    }      
}
