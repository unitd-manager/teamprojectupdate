<?
class CP_Common_Modules_Directory_BusinessGroup_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_businessGroup');
        $modules->registerModule($modObj, array(
            'tableName' => 'business_group'
           ,'keyField'  => 'business_group_id'
           ,'hasFlagInList' => 0
           ,'title'  => 'Business Group'
        ));
    }


    function setMediaArray($mediaArr) {
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('directory_businessGroup', 'logo', 'image');
        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthT'  => 90
            ,'maxHeightT' => 140
            ,'maxWidthN'  => 280
            ,'maxHeightN' => 400
            ,'count'      => 'single'
            ,'isMediaLangSpecific' => false
        ));
    }
}