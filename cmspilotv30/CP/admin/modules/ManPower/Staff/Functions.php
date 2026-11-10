<?
class CP_Admin_Modules_ManPower_Staff_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');

        $modObj = $modules->getModuleObj('manPower_staff');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'relatedTables' => array('media')
           ,'tableName'     => $cpCfg['cp.modAccessStaffTable']
           ,'keyField'      => $cpCfg['cp.modAccessStaffIdLabel']
           ,'titleField'    => "CONCAT_WS(' ', first_name, last_name)"
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        $arr = &$inst->mediaArray['staff']['picture'];

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'signature', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        $arr = &$inst->mediaArray['staff']['signature'];

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment1', 'attachment1');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment1', 'attachment1');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment2', 'attachment2');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment3', 'attachment3');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment4', 'attachment4');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment5', 'attachment5');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment6', 'attachment6');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment7', 'attachment7');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment8', 'attachment8');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment9', 'attachment9');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment10', 'attachment10');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment11', 'attachment11');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment12', 'attachment12');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment13', 'attachment13');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment14', 'attachment14');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment15', 'attachment15');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment16', 'attachment16');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment17', 'attachment17');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment18', 'attachment18');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_staff', 'attachment19', 'attachment19');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        
        $linkObj = $inst->getLinksArrayObj('manPower_staff', 'project_staffGroupLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'staff_group_history'
           ,'keyField'         => 'staff_group_id'
        ));

        //------------------------------------------------------------------------------//
        if ($_SESSION['userGroupName'] == "Super Administrator") {
            $edit = 1;
        } else {
            $edit = 0;
        }
        
        $linkObj = $inst->getLinksArrayObj('manPower_staff', 'manPower_staffCommissionLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'staff_commission'
           ,'keyField'         => 'staff_commission_id'
           ,'linkingType'      => 'portal'
           ,'hasPortalEdit'    => $edit
           ,'fieldlabel'       => array('Project Code', 'Date', 'Amount', 'Status')
        ));
    }

    /**
     *
     */
    function getStaffStatusArray(){
        $arr = array(
             "Current"
            ,"Archive"
        );

        return $arr;
    }
}