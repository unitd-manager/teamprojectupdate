<?
class CP_Admin_Modules_Directory_AltSpelling_Functions 
{
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('directory_altSpelling');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'title' => 'Alt Spelling'
           ,'tableName' => 'alt_spelling'
           ,'keyField' => 'alt_spelling_id'
           ,'actBtnsList' => array('new', 'export')
        ));
    }
      
}