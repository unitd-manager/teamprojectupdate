<?
class CP_Admin_Modules_Museum_Facility_Functions extends CP_Common_Modules_Museum_Facility_Functions
{

    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('museum_facility');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'actBtnsList' => array('new', 'export')
           ,'hasMultiLang' => 1
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $dayArr = $cpCfg['m.museum.facility.dayArr'];
        $availArr = $cpCfg['m.museum.facility.availArr'];
                    
        //------------------------------------------------------------------------------//        
        $linkObj = $inst->getLinksArrayObj('museum_facility', 'museum_facilityAvailabilityLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName' => 'facility_availability'
            ,'linkingType' => 'grid'
            ,'historyTableKeyField' => 'facility_availability_id'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit' => 0
            ,'hasPortalDelete' => 1
            ,'fieldlabel' => array('Day'
                                 ,'Date From'
                                 ,'Date To'
                                 ,'Time From'
                                 ,'Time To'
                                 ,'Availability'
                            )
            , 'gridFieldTypeArray' => array(
                array('type' => 'dropdown', 'ddArr' => $dayArr, 'useKey' => 1)
               ,array('type' => 'date')
               ,array('type' => 'date')
               ,array('type' => 'time')
               ,array('type' => 'time')
               ,array('type' => 'dropdown', 'ddArr' => $availArr, 'useKey' => 1)
            )
        ));

    }

}