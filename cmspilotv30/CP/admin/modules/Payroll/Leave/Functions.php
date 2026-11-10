<?
class CP_Admin_Modules_Payroll_Leave_Functions {

    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('payroll_leave');
        $modObj['tableName'] = 'leave';
        $modObj['keyField']  = 'leave_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply')
           ,'relatedTables' => array('media')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('payroll_leave', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

    }
}