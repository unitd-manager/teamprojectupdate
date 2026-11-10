<?
class CP_Common_Modules_Directory_CardsLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_cardsLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'cards'
           ,'keyField'  => 'card_id'
        ));
    }
}
