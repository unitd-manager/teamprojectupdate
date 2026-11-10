<?
class CP_Admin_Modules_Payroll_LeavePolicy_Functions {

    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('payroll_leavePolicy');
        $modObj['tableName'] = 'leave_policy';
        $modObj['keyField']  = 'leave_policy_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply')
           ,'relatedTables' => array('media')
           ,'title'         => 'Leave Policy'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('payroll_leavePolicy', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $linkObj = $inst->getLinksArrayObj('payroll_leavePolicy', 'payroll_leave_policy_employee_typeLink');


        $inst->registerLinksArray($linkObj, array(
             'historyTableName'      => 'leave_policy_employee_type'
            ,'historyTableKeyField'  => 'leave_policy_employee_type_id'
            ,'showLinkPanelInNew'    => 0
            ,'showLinkPanelInEdit'   => 1
            ,'linkingType'           => 'portal'
            ,'hasPortalEdit'         => 1
            ,'hasPortalDelete'       => 1
            ,'portalDialogWidth'     => 700
            ,'portalDialogHeight'    => 500
            ,'fieldlabel' => array(
                 'Employee Group'
                ,'No of Days'
           )
        ));

    }
}