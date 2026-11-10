<?
class CPL_Admin_Modules_EnggCrm_opportunity_Functions extends CP_Admin_Modules_EnggCrm_opportunity_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_opportunity');
        $modules->registerModule($modObj, array(
            'actBtnsList'      => array('new', 'export')
           ,'actBtnsDetail'    => array('edit')
           ,'actBtnsEdit'      => array('save', 'apply','delete')
           ,'relatedTables'    => array('media')
           ,'title'         => 'Quotation'
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_opportunity', 'enggCrm_employeeLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'opportunity_employee'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name)"
           ,'fieldlabel'             => array('Name', 'Category', 'Status')
           ,'showAnchorInLinkPortal' => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_opportunity', 'enggCrm_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'opportunity'
           ,'linkMultiple'          => 0
        ));
    }
}