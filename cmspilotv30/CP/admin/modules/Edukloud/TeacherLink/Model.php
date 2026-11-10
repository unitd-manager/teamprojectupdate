<?
class CP_Admin_Modules_Edukloud_TeacherLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $modObj = getCPModuleObj('edukloud_teacher');
        $modObj->model->setSearchVar($linkRecType);

        if ($tv['srcRoom'] == 'edukloud_batch' && $cpCfg['m.edukloud.batch.contactLinkPvt']){
            $searchVar->sqlSearchVar[] = "(t.trainer_type = 'Trainer' OR t.trainer_type = 'Both')";
        }
    }

}
