<?
class CP_Admin_Modules_Ecommerce_Color_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecommerce_color');
        $modules->registerModule($modObj, array(
        ));
    }
    
    //==================================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ecommerce_color', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
             'maxHeightT' => '18'
            ,'maxWidthT'  => '30'
            ,'maxHeightN' => '90'
            ,'maxWidthN'  => '150'
            ,'maxHeightL' => '180'
            ,'maxWidthL'  => '300'
            ,'count' => 'single'
            ,'resize' => true
        ));

    }

    
    /**
     *
     */
    function getColorSQL() {

        $SQL = "
        SELECT c.code
              ,CONCAT_WS(' - ', c.title, c.code) AS title
        FROM color c
        ORDER BY c.title
        ";

         return $SQL;

    }
}