<?
class CP_Admin_Modules_Payroll_PayrollManagement_Functions {

    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('payroll_payrollManagement');
        $modObj['tableName'] = 'payroll_management';
        $modObj['keyField']  = 'payroll_management_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('import')
           ,'actBtnsEdit'   => array('save', 'apply')
           ,'actBtnsDetail' => array('edit')
           ,'relatedTables' => array('media')
           ,'title'         => 'Payroll Management'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('payroll_payrollManagement', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}