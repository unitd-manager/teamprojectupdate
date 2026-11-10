<?
class CP_Admin_Modules_EnterpriseIms_ProgramGroup_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_programGroup');
        $modObj['tableName'] = 'program_group';
        $modObj['keyField']  = 'program_group_id';
        $modules->registerModule($modObj, array(
             'hasFlagInList' => 0
            ,'title' => 'Program Group'
        ));
    }

   /**
    *
    */
   function setLinksArray($inst) {
       $linkObj = $inst->getLinksArrayObj('enterpriseIms_programGroup', 'enterpriseIms_subsidyDiscountLink');
       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'program_group_subsidy_discount'
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'program_group_subsidy_discount_id'
            ,'showLinkPanelInEdit'       => 1
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
       ));
   }    
}