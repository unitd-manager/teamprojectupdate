<?
class CP_Common_Modules_Directory_Delivery_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{

    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('directory_delivery');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'title' => 'Delivery'
           ,'tableName' => 'delivery'
           ,'keyField' => 'delivery_id'
        ));
    }

    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('directory_delivery', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }    
}