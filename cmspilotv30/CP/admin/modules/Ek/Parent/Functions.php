<?
class CP_Admin_Modules_Ek_Parent_Functions extends CP_Common_Modules_Ek_Parent_Functions
{
    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ek_parent', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ek_parent', 'ek_studentLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'student_parent'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showAnchorInLinkPortal' => 0
        ));
        //------------------------------------------------------------------------------//
        
    }
}