<?
class CP_Admin_Modules_Ek_Student_Functions extends CP_Common_Modules_Ek_Student_Functions
{
    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ek_student', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ek_student', 'ek_parentLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'student_parent'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showAnchorInLinkPortal' => 0
        ));
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ek_student', 'ek_classLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'student_class'
           ,'displayTitleFieldName'  => "title"
           ,'showAnchorInLinkPortal' => 0
        ));
        //------------------------------------------------------------------------------//
        
    }
}