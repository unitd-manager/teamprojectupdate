<?
class CP_Common_Modules_Directory_Payment_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{

    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('directory_payment');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'title' => 'Payments'
           ,'keyField' => 'payment_id'
           ,'actBtnsList' => array('new', 'export')
        ));
    }

    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('directory_payment', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }    
}