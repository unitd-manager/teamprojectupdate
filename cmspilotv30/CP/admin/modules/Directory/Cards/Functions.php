<?
class CP_Admin_Modules_Directory_Cards_Functions extends CP_Common_Modules_Directory_Cards_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('directory_cards');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'title' => 'Loyalty Card'
           ,'keyField' => 'card_id'
            ,'actBtnsList' => array('new', 'export')
        ));
    }
}