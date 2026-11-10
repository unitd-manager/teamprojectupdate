<?
class CP_Admin_Modules_Tradingsg_ContactLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingsg_contactLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'contact'
           ,'keyField'  => 'contact_id'
        ));
    }
}
