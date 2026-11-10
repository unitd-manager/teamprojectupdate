<?
class CP_Admin_Modules_Directory_Lib_Functions
{
    //==================================================================//
    function setActionsArray($actArrayObj){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');

        //=============== Business =================//
        $actArrayObj->actionsArr['duplicate']['title'] = 'Copy';
        
        //=============== Duplicate and Close=================//
        $actObj = $actArrayObj->getActionObj('duplicateAndCloseBusiness');
        $actArrayObj->registerAction($actObj, array(
            'title' => 'Move'
           ,'url' => "javascript:cpm.directory.business.duplicateAndClose();"
        ));
        //=============== Close =================//
        $actObj = $actArrayObj->getActionObj('closeBusiness');
        $actArrayObj->registerAction($actObj, array(
            'title' => 'Close'
           ,'url' => "javascript:cpm.directory.business.close();"
        ));
        //=============== Bulk Custom Promotion =================//
        $url = "index.php?module=directory_business&_spAction=bulkPromotionForm&showHTML=0";
        $actObj = $actArrayObj->getActionObj('bulkPromotion');
        $actArrayObj->registerAction($actObj, array(
            'title' => "{$ln->gd('cp.actionButton.lbl.bulkPromo', 'Promo')}"
           ,'url' => $url
        ));
        
        //=============== Bulk 3rd Party Promotion =================//
        $url = "index.php?module=directory_business&_spAction=bulk3rdPartyPromotionForm&showHTML=0";
        $actObj = $actArrayObj->getActionObj('bulk3rdPartyPromotion');
        $actArrayObj->registerAction($actObj, array(
            'title' => "{$ln->gd('cp.actionButton.lbl.bulk3rdPartyPromo', '3P Promo')}"
           ,'url' => $url
        ));
        
        //=============== Bulk 3rd Party Promotion =================//
        $url = "index.php?module=directory_businessGroup&_spAction=updateBusinesses&showHTML=0";
        $actObj = $actArrayObj->getActionObj('updateBusinesses');
        $actArrayObj->registerAction($actObj, array(
            'title' => 'Update Businesses'
           ,'url' => $url
        ));
    }

}
