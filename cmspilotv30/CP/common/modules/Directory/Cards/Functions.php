<?
class CP_Common_Modules_Directory_Cards_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('directory_cards');
        $modules->registerModule($modObj, array(
        	 'hasFlagInList' => 0
            ,'title' => 'Loyalty Card'
            ,'keyField' => 'card_id'
        ));
    }
        
    /**
     *
     * @return <type>
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('directory_cards', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}