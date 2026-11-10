<?
class CP_Admin_Modules_Directory_AdvertLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{

    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');

        $formAction = "{$cpCfg['cp.scopeRootAlias']}index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('business_advert', 'business_advert_id', $id);
        
        $sqlAdvertiser = $fn->getDDSql('directory_advert');
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL($ln->gd('m.directory.advertLink.lbl.advertiser'), 'advert_id', $sqlAdvertiser, $row['advert_id'])}
                {$formObj->getTARow($ln->gd('m.directory.advertLink.lbl.description'), 'description', $row['description'])}
                {$formObj->getDateRow($ln->gd('m.directory.advertLink.lbl.advertDate'), 'advert_date', $row['advert_date'])}
                {$media->getRightPanelMediaDisplay($ln->gd('m.directory.advertLink.lbl.advertImage'), 'directory_advertLink', 'picture', $row)}
            </fieldset>
            <input type='hidden' name='business_advert_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
}
