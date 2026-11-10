<?
class CP_Admin_Modules_EnterpriseIms_Expenses_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_expenses');
        $modules->registerModule($modObj, array(
            'title'         => 'Expenses'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enterpriseIms_expenses', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}