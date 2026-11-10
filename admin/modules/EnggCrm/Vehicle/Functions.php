<?
class CPL_Admin_Modules_EnggCrm_Vehicle_Functions 
{
    //==================================================================//
   function setModuleArray($modules){

       
	$cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('enggCrm_vehicle');
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
        $mediaObj = $mediaArr->getMediaObj('enggCrm_vehicle', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
	
    

    
    /**
     *
     */
   
}