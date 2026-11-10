<?
class CP_Admin_Modules_Directory_Promotion_Functions extends CP_Common_Modules_Directory_Promotion_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_promotion');
        $modules->registerModule($modObj, array(
        	 'hasFlagInList' => 0
        	,'hasEditInList' => 0
            ,'actBtnsDetail' => array('delete')
            ,'actBtnsList' => array('export')
        ));
    }
}