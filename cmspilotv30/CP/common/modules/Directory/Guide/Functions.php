<?
class CP_Common_Modules_Directory_Guide_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_guide');
        $modules->registerModule($modObj, array(
        ));
    }
    
    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('directory_guide', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthT' => 90
            ,'maxHeightT' => 90
            ,'maxWidthN' => 200
            ,'maxHeightN' => 200
            ,'maxWidthL' => 1000
            ,'maxHeightL' => 1000
            ,'count' => 'single'
        ));
    }    

    /**
     *
     */
    function setLinksArray($inst) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_guide', 'directory_businessLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'guide_business'
           ,'displayTitleFieldName'  => 'a.business_name'
           ,'fieldlabel' => array('Name')
        ));        

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('directory_guide', 'directory_contactLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'liked_guides'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
            ,'fieldlabel' => array('Name', 'Email', 'Country')

           ,'additionalFieldsArray' => array(
                'a.email'
               ,'a.country_code'
           )
           ,'hasModalChoose' => false
        ));

    }
}