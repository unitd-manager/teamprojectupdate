<?
class CP_Common_Modules_Edukite_Student_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_student');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'import')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukite_student', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
            'count' => 'single'
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukite_student', 'edukite_parentLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'student_parent'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showAnchorInLinkPortal' => 0
        ));
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukite_student', 'edukite_classLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'class_student'
           ,'displayTitleFieldName'  => "title"
           ,'showAnchorInLinkPortal' => 0
        ));
        //------------------------------------------------------------------------------//
        
    }
}