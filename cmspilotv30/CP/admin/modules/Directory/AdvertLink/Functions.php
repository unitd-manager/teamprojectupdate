<?
class CP_Admin_Modules_Directory_AdvertLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_advertLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'business_advert'
           ,'keyField'  => 'business_advert_id'
        ));
    }

    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('directory_advertLink', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthT' => 90
            ,'maxHeightT' => 140
            ,'maxWidthN' => 280
            ,'maxHeightN' => 400
            ,'count' => 'single'
        ));
    }    
}
