<?
class CP_Www_Modules_Edukite_Achievement_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('edukite_achievement');
        $modules->registerModule($modObj, array(
             'actBtnsList'   => array('new')
            ,'actBtnsDetail' => array('edit', 'delete')
            ,'actBtnsNew'    => array('cancelNew', 'addNew')
            ,'actBtnsEdit'   => array('save', 'cancel', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukite_achievement', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
            'count' => 'single'
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukite_achievement', 'edukite_parentLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'student_parent'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showAnchorInLinkPortal' => 0
        ));
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukite_achievement', 'edukite_classLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'class_student'
           ,'displayTitleFieldName'  => "title"
           ,'showAnchorInLinkPortal' => 0
        ));
        //------------------------------------------------------------------------------//
        
    }
}