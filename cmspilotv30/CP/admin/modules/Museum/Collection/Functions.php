<?
class CP_Admin_Modules_Museum_Collection_Functions extends CP_Common_Modules_Museum_Collection_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('museum_collection');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
           ,'actBtnsList'   => array('new', 'updateFlickrCache')
        ));
    }
    
    /**
     *
     * @param type $actArray 
     */
    function setActionsArray($actArray){

        $actObj = $actArray->getActionObj('updateFlickrCache');
        $actArray->registerAction($actObj, array(
            'title' => 'Update Flickr Cache'
           ,'url'   => "javascript:cpm.museum.collection.updateFlickrCache()"
        ));
    }    
}