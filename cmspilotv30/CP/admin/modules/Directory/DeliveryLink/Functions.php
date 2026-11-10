<?
class CP_Admin_Modules_Directory_DeliveryLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_deliveryLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'business_delivery'
           ,'keyField'  => 'business_delivery_id'
        ));
    }

    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('directory_deliveryLink', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthT' => 90
            ,'maxHeightT' => 140
            ,'maxWidthN' => 280
            ,'maxHeightN' => 400
            ,'count' => 'single'
        ));
    }
}
