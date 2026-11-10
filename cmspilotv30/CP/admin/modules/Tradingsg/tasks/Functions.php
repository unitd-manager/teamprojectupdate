<?
class CP_Admin_Modules_Tradingsg_tasks_Functions 
{
    //==================================================================//
   function setModuleArray($modules){

       
	$cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('tradingsg_tasks');
        $modules->registerModule($modObj, array(
            'hasMultiLang'  => 1
           ,'hasFlagInList' => 0
           ,'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete', 'duplicate')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
		   ,'relatedTables' => array('media')
        ));
    }


    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('tradingsg_tasks', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
	
    

    
    /**
     *
     */
   
}