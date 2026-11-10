<?
class CP_Admin_Modules_WebBasic_ContentLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('webBasic_contentLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'content'
           ,'keyField'      => 'content_id'
        ));
    }
}
