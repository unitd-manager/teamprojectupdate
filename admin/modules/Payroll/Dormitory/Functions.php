<?
class CPL_Admin_Modules_Payroll_Dormitory_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('payroll_dormitory');
        $modObj['tableName'] = 'dormitory';
        $modObj['keyField']  = 'dormitory_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'actBtnsList'   => array('new')
           ,'actBtnsEdit'   => array('save', 'apply')
           ,'relatedTables' => array('media')
           ,'titleField'    => "name"
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

    }
    
    /**
     *
     */
    function setLinksArray($inst) {

    }
}