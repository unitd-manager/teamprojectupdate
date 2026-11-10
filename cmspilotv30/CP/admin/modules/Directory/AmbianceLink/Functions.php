<?
class CP_Admin_Modules_Directory_AmbianceLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_ambianceLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'business_ambiance'
           ,'keyField'  => 'business_ambiance_id'
        ));
    }

    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('directory_ambianceLink', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthT' => 90
            ,'maxHeightT' => 140
            ,'maxWidthN' => 280
            ,'maxHeightN' => 400
            ,'count' => 'single'
        ));
    }    
}
