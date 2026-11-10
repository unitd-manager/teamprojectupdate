<?
class CP_Admin_Modules_Web2_Feed_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('web2_feed');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'actBtnsList'   => array('updateFeed')
        ));
    }
    
    /**
     *
     * @param type $actArray 
     */
    function setActionsArray($actArray){

        $actObj = $actArray->getActionObj('updateFeed');
        $actArray->registerAction($actObj, array(
            'title' => 'Update Feed'
           ,'url'   => "javascript:cpm.web2.feed.updateFeed()"
        ));
    }    
}