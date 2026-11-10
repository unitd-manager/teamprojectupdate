<?
class CP_Common_Modules_Edukite_Teacher_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_teacher');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'import')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukite_teacher', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukite_teacher', 'edukite_classLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'staff_class'
           ,'showAnchorInLinkPortal' => 0
        ));
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukite_teacher', 'edukite_subjectLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'staff_subject'
           ,'showAnchorInLinkPortal' => 0
        ));

    }
}