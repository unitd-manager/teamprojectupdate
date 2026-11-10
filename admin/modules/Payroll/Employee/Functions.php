<?
class CPL_Admin_Modules_Payroll_Employee_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('payroll_employee');
        $modObj['tableName'] = 'employee';
        $modObj['keyField']  = 'employee_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'import')
           ,'actBtnsEdit'   => array('save', 'apply')
           ,'relatedTables' => array('media')
           ,'titleField'    => "CONCAT_WS(' ', first_name, last_name)"
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('payroll_employee', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        $mediaObj = $mediaArr->getMediaObj('payroll_employee', 'workPermit', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        $mediaObj = $mediaArr->getMediaObj('payroll_employee', 'wsq', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        $mediaObj = $mediaArr->getMediaObj('payroll_employee', 'digitalSign', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('payroll_employee', 'common_interestLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'interest_contact'
           ,'showAnchorInLinkPortal' => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('payroll_employee', 'payroll_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'displayTitleFieldName' => 'a.company_name'
           ,'historyTableName'      => 'employee'
           ,'linkMultiple'          => 0
           ,'keyFieldForHistory'    => 'company_id'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('payroll_employee', 'payroll_trainingLink');        
        $inst->registerLinksArray($linkObj, array(
            'recordTypeForHistory'  => 'Repayment'
           ,'historyTableName'      => 'training_staff'
           ,'historyTableKeyField'  => 'training_staff_id'
           ,'fieldlabel'            => array('Course Title', 'From Date', 'To Date')
           ,'linkingType'           => 'modal'
           ,'hasModalChoose'        => false
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('payroll_employee', 'payroll_jobInformationLink');        
        $inst->registerLinksArray($linkObj, array(
            'recordTypeForHistory'  => 'Job Information'
           ,'historyTableName'      => 'job_information'
           ,'historyTableKeyField'  => 'job_information_id'
           ,'fieldlabel'            => array('Basic Pay', 'From Date', 'To Date')
           ,'linkingType'           => 'modal'
           ,'hasModalChoose'        => false
        ));
    }
}