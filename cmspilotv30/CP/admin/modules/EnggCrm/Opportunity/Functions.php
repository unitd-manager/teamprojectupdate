<?
class CP_Admin_Modules_EnggCrm_opportunity_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_opportunity');
        $modules->registerModule($modObj, array(
            'actBtnsList'      => array('new')
           ,'actBtnsDetail'    => array('edit')
           ,'actBtnsEdit'      => array('save', 'apply')
           ,'relatedTables'    => array('media')
        ));
    }

    /**
     *
     */
    function setReportsArray($repInst) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $repInst->setReportArrayObj('enggCrm_opportunity', "opportunityList");
        $arr = &$repInst->reportsArray['enggCrm_opportunity']['opportunityList'];
        $arr['jasperFileName'] = 'opportunity_list.jasper';
        $arr['sendRecIds']     = true;
        $arr['sendSortOrder']  = true;
        $arr['outputFileName'] = $cpCfg['cp.companyName'] . '-Opportunities-' . date('Ymd');
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enggCrm_opportunity', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_opportunity', 'core_staffLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'opportunity_staff'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'fieldlabel'             => array('Name', 'Team', 'Type')
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