<?
class CP_Admin_Modules_Pos_CompanyProfile_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('pos_companyProfile');
        $modObj['tableName'] = 'company_profile';
        $modObj['keyField']  = 'company_profile_id';
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
           ,'title' => 'Company Profile'
           ,'actBtnsList' => array('new', 'printListScreen')
           ,'actBtnsDetail' => array('edit', 'delete', 'printListScreen')
        ));
    }
 
    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('pos_companyProfile', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

    }
}