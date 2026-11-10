<?
class CP_Admin_Modules_Directory_AmbianceLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{

    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');

        $formAction = "{$cpCfg['cp.scopeRootAlias']}index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('business_ambiance', 'business_ambiance_id', $id);
        
        $sqlAmbiance = $fn->getDDSql('directory_ambiance');
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL($ln->gd('m.directory.ambianceLink.lbl.ambiance'), 'ambiance_id', $sqlAmbiance, $row['ambiance_id'])}
                {$media->getRightPanelMediaDisplay($ln->gd('m.directory.ambianceLink.link.ambianceImage'), 'directory_ambianceLink', 'picture', $row)}
            </fieldset>
            <input type='hidden' name='business_ambiance_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
}
