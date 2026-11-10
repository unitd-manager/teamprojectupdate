<?
class CP_Admin_Modules_Directory_ExternalSource_Functions extends CP_Common_Modules_Directory_ExternalSource_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('directory_externalSource');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'title' => 'External Reviews'
           ,'tableName' => 'external_source'
           ,'keyField' => 'external_source_id'
           ,'actBtnsList' => array('new', 'export')
        ));
    }
}