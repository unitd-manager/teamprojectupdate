<?
class CP_Admin_Modules_Directory_Business_Functions extends CP_Common_Modules_Directory_Business_Functions
{

    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_business');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'actBtnsDetail' => array('edit', 'delete', 'duplicate',
                                     'closeBusiness', 'duplicateAndCloseBusiness')
           ,'actBtnsList' => array('new', 'import', 'export', 'search',
                                   'bulkPromotion', 'bulk3rdPartyPromotion')
           ,'hasMultiLang' => 1
           ,'hasFlagInList' => 1
           ,'useRecordIdForDetailEditLink' => false
           ,'showRecordCount' => false
           ,'hasAutoSave' => true
        ));
    }
}