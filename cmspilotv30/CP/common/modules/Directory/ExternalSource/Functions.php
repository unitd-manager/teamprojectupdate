<?
class CP_Common_Modules_Directory_ExternalSource_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('directory_externalSource');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'title' => 'External Sources'
           ,'tableName' => 'external_source'
           ,'keyField' => 'external_source_id'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('directory_externalSource', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}