<?
class CP_Www_Modules_Museum_Donation_Functions
{

    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('museum_donation');
        $modules->registerModule($modObj, array(
        ));
    }
}
