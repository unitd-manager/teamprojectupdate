<?
class CP_Www_Modules_Edukite_DailyActivity_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_dailyActivity');
        $modObj['tableName'] = 'daily_activity';
        $modObj['keyField']  = 'daily_activity_id';
        $modObj['listLimit'] = 50;
        $modules->registerModule($modObj, array(
             'actBtnsList'   => array('new')
            ,'hasFlagInList' => 0
        ));
    }

}