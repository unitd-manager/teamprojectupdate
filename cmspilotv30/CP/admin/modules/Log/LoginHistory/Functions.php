<?
class CP_Admin_Modules_Common_LoginHistory_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('common_loginHistory');
        $modules->registerModule($modObj, array(
            'title'         => 'Login History'
           ,'tableName'     => 'loginHistory'
           ,'keyField'      => 'login_history_id'
           ,'folder'        => 'loginHistory'
           ,'hasFlagInList' => 0
           ,'hasEditInList' => 0
           ,'actBtnsList'   => array()
        ));
    }
}