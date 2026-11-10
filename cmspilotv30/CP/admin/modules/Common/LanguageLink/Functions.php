<?
class CP_Admin_Modules_Common_LanguageLink_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('common_languageLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'language'
           ,'keyField'  => 'language_id'
        ));
    }
}
