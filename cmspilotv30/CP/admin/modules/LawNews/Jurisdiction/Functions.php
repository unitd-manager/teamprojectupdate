<?
class CP_Admin_Modules_LawNews_Jurisdiction_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('lawNews_jurisdiction');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
        ));
    }

    /**
     *
     * @return <type>
     */
     function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('lawNews_jurisdiction', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('lawNews_jurisdiction', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
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
            $siteObj->setLinksArrayForSiteLink($inst, 'lawNews_jurisdiction');
        }
    }

    /**
     *
     * @param type $record_id
     * @return string
     */
    function beforeDeleteHandler($record_id){
        $fn = Zend_Registry::get('fn');

        $condn = "
            jurisdiction_id = {$record_id}
        AND published = 1
        ";
        $published_jurCount = $fn->getRecordCount('jurisdiction', $condn);

        if($published_jurCount > 0){
            $arr = array(
                 'status' => 'error'
                ,'message' => "You can't delete published Jurisdiction"
            );
            return $arr;
        }

        $condn = "
        jurisdiction_id = {$record_id}
        ";
        $jur_contentCount = $fn->getRecordCount('jurisdiction_content', $condn);
        if($jur_contentCount > 0){
            $arr = array(
                 'status' => 'error'
                ,'message' => "You can't delete the Jurisdiction with contents attached."
            );
            return $arr;
        }
    }
}