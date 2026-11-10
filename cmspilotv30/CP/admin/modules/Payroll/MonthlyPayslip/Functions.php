<?
class CP_Admin_Modules_Payroll_MonthlyPayslip_Functions {

    /**
     *
     */

    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('payroll_monthlyPayslip');
        $modObj['tableName'] = 'payroll_management';
        $modObj['keyField']  = 'payroll_management_id';
        $modules->registerModule($modObj, array(
            //'actBtnsList'   => array('new')
           'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('payroll_monthlyPayslip', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }  

}