<?
class CP_Common_Modules_Directory_Promotion_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('directory_promotion');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('directory_promotion', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthT' => 192
            ,'maxHeightT' => 142
            ,'maxWidthN' => 350
            ,'maxHeightN' => 350
            ,'count' => 'single'
        ));
    }

    /**
     *
     */
    function getPromotionRecordTypeArray(){
        $arr=
        array(
             'IH'
            ,'3P'
        );

        return $arr;
    }

    /**
     *
     */
    function getPromotionTypeArray(){
        $arr=
        array(
             '2:1 Deal'
            ,'Happy Hour'
            ,'Special Discount'
            ,'Sale'
            ,'Other'
        );

        return $arr;
    }

    /**
     *
     */
    function getPromotionItemTypeArray(){
        $arr=
        array(
             'Dish'
            ,'Meal'
            ,'Brand'
            ,'Product'
            ,'Other'
        );

        return $arr;
    }
}