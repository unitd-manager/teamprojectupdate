<?
class CP_Admin_Modules_Payroll_Salary_Functions {

    /**
     *
     */

    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('payroll_salary');
        $modObj['tableName'] = 'salary';
        $modObj['keyField']  = 'salary_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
           ,'title'         => 'Salary'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('payroll_salary', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

}