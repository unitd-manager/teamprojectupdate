<?
class CP_Admin_Modules_Common_InterestLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('common_interestLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'interest'
           ,'keyField'  => 'interest_id'
        ));
    }
}
