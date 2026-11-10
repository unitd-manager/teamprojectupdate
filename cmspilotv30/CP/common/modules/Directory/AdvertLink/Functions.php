<?
class CP_Common_Modules_Directory_AdvertLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_advertLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'business_advert'
           ,'keyField'  => 'business_advert_id'
        ));
    }
}
