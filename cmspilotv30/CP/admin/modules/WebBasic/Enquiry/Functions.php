<?
class CP_Admin_Modules_WebBasic_Enquiry_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('webBasic_enquiry');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('export')
        ));
    }
}