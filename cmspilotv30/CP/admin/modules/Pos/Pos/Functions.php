<?
class CP_Admin_Modules_Pos_Pos_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('pos_pos');
        $modules->registerModule($modObj, array(
            'actBtnsList' => array()
        ));
    }

   /**
    *
    */
   function setLinksArray($inst) {
        
        //-------------------------- extra ---------------------------------------------//
       $linkObj = $inst->getLinksArrayObj('pos_pos', 'pos_productLink');
       $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'product'
           ,'displayTitleFieldName' => 'a.title'
           ,'linkMultiple'          => 0
        ));

        //-------------------------- extra ---------------------------------------------//
       $linkObj = $inst->getLinksArrayObj('pos_pos', 'pos_contactLink');
       $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'contact'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'linkMultiple'          => 0
        ));
	   //------------------------------------------------------------------------------//
	}
 
}