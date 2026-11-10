<?
class CP_Admin_Modules_Project_DomainHosting_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_domainHosting');
        $modObj['tableName'] = 'renewals';
        $modObj['keyField']  = 'renewal_id';
        $modules->registerModule($modObj, array(
           'title'    => 'Renewals'
           ,'actBtnsList'   => array('new', 'export')
           ,'actBtnsEdit' => array('save', 'apply', 'cancel')
           ,'hasFlagInList' => 0
        ));
    }

    /**
     *
     */
    function getProjectDomainHostingProjectProjectLinkPortalSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $sqlProject = $fn->getValueListSQL('projectStatus');

        $text = "
        <select name='project_status' class='float_right m5'>
            <option value=''>Status</option>
            {$dbUtil->getDropDownFromSQLCols1($db, $sqlProject, "WIP")}
        </select>
        ";

        return $text;
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_company', 'project_projectLink');

        $fieldlabel = array('Project Code', 'Title', 'Project Value', 'Status');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'project'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'fieldlabel'            => $fieldlabel
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_company', 'project_invoiceLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'invoice'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 0
           ,'fieldlabel'            => array('Invoice Code', 'Project Code', 'Invoice Type', 'Invoice Date','Due Date', 'Inv. Amount', 'Status')
        ));

        //------------------------------------------------------------------------------//
       

    }

}