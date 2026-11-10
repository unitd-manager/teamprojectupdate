<?
class CP_Admin_Modules_ManPower_Expense_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('manPower_expense');
        $modules->registerModule($modObj, array(
            'title'         => 'Expense'
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_expense', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}