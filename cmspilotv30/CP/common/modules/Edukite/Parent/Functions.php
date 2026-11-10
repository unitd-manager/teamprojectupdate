<?
class CP_Common_Modules_Edukite_Parent_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_parent');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'import')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukite_parent', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukite_parent', 'edukite_studentLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'student_parent'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showAnchorInLinkPortal' => 0
        ));
        //------------------------------------------------------------------------------//
        
    }
}