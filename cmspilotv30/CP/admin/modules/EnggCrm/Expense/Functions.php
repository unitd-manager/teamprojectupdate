<?
class CP_Admin_Modules_EnggCrm_Expense_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enggCrm_expense');
        $modules->registerModule($modObj, array(
            'title'         => 'Expenses'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enggCrm_expense', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}