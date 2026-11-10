<?
class CP_Admin_Modules_WebBasic_Content_Functions extends CP_Common_Modules_WebBasic_Content_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('webBasic_content');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        
        if($cpCfg['cp.hasMultiSites']){
            $siteObj = getCPFnObj('common_site');
            $siteObj->setLinksArrayForSiteLink($inst, 'webBasic_content');
        }           

        if ($cpCfg['m.webBasic.content.hasRelatedContent'] == 1){
            $linkObj = $inst->getLinksArrayObj('webBasic_content', 'webBasic_contentLink', array(
                 'historyTableName'    => 'related_content'
                ,'keyFieldForHistory'  => 'related_content_id'
                ,'keyField'            => 'content_id'
                ,'keyFieldForLinking'  => 'content_id_rel'
                ,'className'           => 'Content'
                ,'showAnchorInLinkPortal' => 0
                ,'anchorFieldsArr'     => array('title' => $inst->getLinkAnchorObj('title', 'content_id'))
                ,'fieldlabel' => array(
                     'Content Title'
                )
            ));
            
            $inst->registerLinksArray($linkObj);
        }

        if ($cpCfg['m.webBasic.content.hasHistory'] == 1){
            $linkObj = $inst->getLinksArrayObj('webBasic_content', 'webBasic_contentHistoryLink');
    
            $inst->registerLinksArray($linkObj, array(
                'historyTableName'      => 'content_history'
               ,'linkingType'           => 'portal'
               ,'title'                 => 'Content History'
               ,'showLinkPanelInEdit'   => 1
               ,'hasPortalEdit'         => 1
               ,'hasPortalDelete'       => 1
               ,'portalDialogWidth'     => 900
               ,'portalDialogHeight'    => 625
            ));
        }
        //------------------------------------------------------------------------------//
        if ($cpCfg['m.webBasic.content.hasTab'] == 1){
            $linkObj = $inst->getLinksArrayObj('webBasic_content', 'webBasic_tabContentLink');
    
            $inst->registerLinksArray($linkObj, array(
                'historyTableName'      => 'tab_content'
               ,'linkingType'           => 'portal'
               ,'title'                 => 'Tab Content'
               ,'showLinkPanelInEdit'   => 1
               ,'hasPortalEdit'         => 1
               ,'hasPortalDelete'       => 1
               ,'portalDialogWidth'     => 900
               ,'portalDialogHeight'    => 625
            ));
        }
    }
}