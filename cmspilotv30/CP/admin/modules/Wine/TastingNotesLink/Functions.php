<?
class CP_Admin_Modules_Wine_TastingNotesLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('wine_tastingNotesLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'tasting_notes'
           ,'keyField'  => 'tasting_notes_id'
        ));
    }
}
