<?
class CP_Common_Modules_WebBasic_SubCategory_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');

        $modObj = $modules->getModuleObj('webBasic_subCategory');
        $modObj['tableName'] = 'sub_category';
        $modObj['keyField']  = 'sub_category_id';
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $mediaObj = $mediaArr->getMediaObj('webBasic_subCategory', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj);

        $mediaObj = $mediaArr->getMediaObj('webBasic_subCategory', 'banner', 'image');
        $mediaArr->registerMedia($mediaObj);
    }
}