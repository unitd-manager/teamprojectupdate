<?
class CP_Www_Modules_Edukite_YearGroup_Functions extends CP_Common_Modules_Edukite_YearGroup_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_yearGroup');
        $modObj['listLimit'] = 50;
        $modules->registerModule($modObj, array(
             'tableName' => 'year_group'
            ,'keyField'  => 'year_group_id'
            ,'actBtnsList'   => array('new')
            ,'actBtnsDetail' => array('edit', 'delete')
            ,'actBtnsNew'    => array('cancelNew', 'addNew')
            ,'actBtnsEdit'   => array('save', 'cancel', 'delete')
        ));
    }

}