<?
class CP_Admin_Modules_Core_Staff_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');

        $modObj = $modules->getModuleObj('core_staff');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'relatedTables' => array('media')
           ,'tableName'     => $cpCfg['cp.modAccessStaffTable']
           ,'keyField'      => $cpCfg['cp.modAccessStaffIdLabel']
           ,'titleField'    => "CONCAT_WS(' ', first_name, last_name)"
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('core_staff', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('core_staff', 'signature', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

    }

    /**
     *
     */
    function setLinksArray($inst) {
        
        $linkObj = $inst->getLinksArrayObj('core_staff', 'project_staffGroupLink');

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
        
        $linkObj = $inst->getLinksArrayObj('core_staff', 'manPower_staffCommissionLink');
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