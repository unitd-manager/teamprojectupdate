<?
class CP_Admin_Modules_Tradingsg_CategoryLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingsg_categoryLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'category'
           ,'keyField'  => 'category_id'
        ));
    }
}
