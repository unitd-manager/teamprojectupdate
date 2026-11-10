<?
class CP_Admin_Modules_Ek_Teacher_Functions extends CP_Common_Modules_Ek_Teacher_Functions
{
    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ek_teacher', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ek_teacher', 'ek_classLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'staff_class'
           ,'showAnchorInLinkPortal' => 0
        ));
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ek_teacher', 'ek_subjectLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'staff_subject'
           ,'showAnchorInLinkPortal' => 0
        ));

    }
}