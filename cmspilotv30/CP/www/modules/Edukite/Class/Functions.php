<?
class CP_Www_Modules_Edukite_Class_Functions extends CP_Common_Modules_Edukite_Class_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_class');
        $modObj['listLimit'] = 50;
        $modules->registerModule($modObj, array(
             'actBtnsList'   => array('new')
            ,'actBtnsDetail' => array('edit', 'delete')
            ,'actBtnsNew'    => array('cancelNew', 'addNew')
            ,'actBtnsEdit'   => array('save', 'cancel', 'delete')
        ));
    }
    
}