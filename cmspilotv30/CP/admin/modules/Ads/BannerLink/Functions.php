<?
class CP_Admin_Modules_Ads_BannerLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('ads_bannerLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'banner'
           ,'keyField'  => 'banner_id'
        ));
    }
}
