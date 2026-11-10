<?
class CP_Admin_Modules_ELearn_Teacher_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('elearn_teacher');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
        ));
    }

    //==================================================================//
    //==================================================================//
    //==================================================================//
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');

        $teacher_id = $fn->getReqParam('teacher_id');
        $school     = $fn->getReqParam('school_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "t.teacher_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 't.teacher_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   t.first_name LIKE '%{$tv['keyword']}%'
                OR t.last_name LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($school != '') {
                $searchVar->sqlSearchVar[] = "t.school_id = '{$school}'";
            }

            if ($teacher_id != "") {
                $searchVar->sqlSearchVar[] = "t.teacher_id = '{$teacher_id}'";
            }
        }
    }

    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('elearn_teacher', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     * @return <type>
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('elearn_teacher', 'elearn_bookLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'teacher_book'
           ,'showAnchorInLinkPortal' => 0
        ));
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('elearn_teacher', 'elearn_studentLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'teacher_student'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('elearn_teacher', 'elearn_klassLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'klass_teacher'
        ));
    }

}