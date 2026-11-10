<?
class CP_Common_Modules_Directory_Contact_Functions
{
    /**
     *
     */
    function setMediaArray($mediaArr) {
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('directory_contact', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
            'count' => 'single',
            'maxWidthN' => 260,
            'maxHeightN' => 260
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('directory_contact', 'related_picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_contact', 'common_interestLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'interest_contact'
           ,'showAnchorInLinkPortal' => 0
        ));

        //------------------------------------------------------------------------------//
        $sqlCat = getCPModuleObj('webBasic_category')->model->getCategorySQLByType('Business');
        $result = $db->sql_query($sqlCat);
        $catArr = $dbUtil->getResultsetAsArrayForForm($result);

        $sqlSubCat = getCPModuleObj('webBasic_subCategory')->model->getSubCategorySQL();
        $result = $db->sql_query($sqlSubCat);
        $subCatArr = $dbUtil->getResultsetAsArrayForForm($result);

        $linkObj = $inst->getLinksArrayObj('directory_contact', 'directory_preferenceLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'     => 'contact_preference'
            ,'historyTableKeyField' => 'contact_preference_id'
            ,'linkingType'          => 'grid'
            ,'keyField'             => 'contact_preference_id'
            ,'showLinkPanelInEdit'  => 1
            ,'hasPortalEdit'        => 0
            ,'hasPortalDelete'      => 1
            ,'fieldlabel'           => array('Category', 'Sub Category')
            ,'fieldClassArray'      => array()
            ,'showAnchorInLinkPortal' => false
            ,'gridFieldTypeArray'  => array(
                  array('type' => 'dropdown', 'ddArr' => $catArr)
                 ,array('type' => 'dropdown', 'ddArr' => $subCatArr, 'firstOptionLabel' => 'All')
            )
        ));

        //------------------------------------------------------------------------------//
        $sqlCard = getCPModuleObj('directory_cards')->model->getCardSQL();
        $result = $db->sql_query($sqlCard);
        $cardArr = $dbUtil->getResultsetAsArrayForForm($result);

        $linkObj = $inst->getLinksArrayObj('directory_contact', 'directory_cardsLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'    => 'contact_card'
            ,'historyTableKeyField'=> 'contact_card_id'
            ,'linkingType'         => 'grid'
            ,'keyField'            => 'contact_card_id'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit'       => 0
            ,'hasPortalDelete'     => 1
            ,'fieldlabel'          => array('Loyalty Card')
            ,'fieldClassArray'     => array()
            ,'showAnchorInLinkPortal' => false
            ,'gridFieldTypeArray'  => array(
                  array('type' => 'dropdown', 'ddArr' => $cardArr)
            )
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_contact', 'directory_businessLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'my_business'
           ,'displayTitleFieldName'  => 'a.business_name'
        ));        
 
        //------------------------------------------------------------------------------//
        $sqlCountry = $fn->getDDSql('common_country');
        $result = $db->sql_query($sqlCountry);
        $countryArr = $dbUtil->getResultsetAsArrayForForm($result);

        $sqlArea = $fn->getDDSql('directory_area');
        $result = $db->sql_query($sqlArea);
        $areaArr = $dbUtil->getResultsetAsArrayForForm($result);
        $linkObj = $inst->getLinksArrayObj('directory_contact', 'directory_areaLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'     => 'contact_area'
            ,'historyTableKeyField' => 'contact_area_id'
            ,'linkingType'          => 'grid'
            ,'keyField'             => 'contact_area_id'
            ,'showLinkPanelInEdit'  => 1
            ,'hasPortalEdit'        => 0
            ,'hasPortalDelete'      => 1
            ,'fieldlabel'           => array('Label', 'Country', 'Area')
            ,'fieldClassArray'      => array()
            ,'showAnchorInLinkPortal' => false
            ,'gridFieldTypeArray'  => array(
                  array('type' => 'textbox')
                 ,array('type' => 'dropdown', 'ddArr' => $countryArr)
                 ,array('type' => 'dropdown', 'ddArr' => $areaArr)
            )
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_contact', 'directory_contactLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'  => 'contact_friend'
            ,'linkRoomKeyFldNameInHistTbl' => 'friend_id'
            ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
        ));
    }
}