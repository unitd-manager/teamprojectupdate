<?
class CP_Admin_Modules_Web2_Poll_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('web2_poll');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 0
           ,'hasFlagInList' => 0
        ));
    }

    /**
     *
     * @return <type>
     */    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('web2_venue', 'picture', 'picture');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     * @return <type>
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('web2_poll', 'web2_pollHistoryLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'       => 'poll_history'
            ,'historyTableKeyField'   => 'poll_history_id'
            ,'linkingType'            => 'grid'
            ,'showLinkPanelInEdit'    => 1
            ,'hasPortalEdit'          => 0
            ,'hasPortalDelete'        => 1
            ,'fieldlabel'             => array('Answer', 'Sort', 'Answer Count')
            ,'additionalFieldsArray'  => array(
                 'b.title'
                ,'b.sort_order'
                ,'b.answer_count'
            )
            ,'showAnchorInLinkPortal' => false
            ,'fieldClassArray'     => array('', 'w50 txtRight', 'w100 txtRight')
        ));
    }

    /**
     *
     * @return <type>
     */
    }