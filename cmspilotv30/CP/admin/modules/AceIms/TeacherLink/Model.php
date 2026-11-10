<?
class CP_Admin_Modules_AceIms_TeacherLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $modObj = getCPModuleObj('aceIms_teacher');
        $modObj->model->setSearchVar($linkRecType);

        if ($tv['srcRoom'] == 'aceIms_batch' && $cpCfg['m.aceIms.batch.contactLinkPvt']){
            $searchVar->sqlSearchVar[] = "(t.trainer_type = 'Trainer' OR t.trainer_type = 'Both')";
        }
    }
}
