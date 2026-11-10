<?
class CP_Common_Modules_Directory_Promotion3PartyLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        
        $expCheck = array('isLabelOnLeft' => true);
        
        $sqlCards = getCPModuleObj('directory_cards')->model->getCardSQL();
        $formAction = "{$cpCfg['cp.scopeRootAlias']}index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL('Loyalty Card', 'card_id', $sqlCards)}
                {$formObj->getTBRow('Title', 'title')}
                {$formObj->getTARow('Custom Text', 'custom_text')}
                {$formObj->getSingleCheckBoxRow('Is Happy Hr. Promo.?', 'is_happy_hour_promo',
                                                '', $expCheck)}
                {$formObj->getDateRow('Start Date', 'start_date')}
                {$formObj->getDateRow('End Date', 'end_date')}
                {$formObj->getTimeRow('Start Time', 'start_time')}
                {$formObj->getTimeRow('End Time', 'end_time')}
                {$formObj->getTBRow('Promo URL', 'promotion_url')}
                {$formObj->getDaysOfWeeksRow('Days of Week', 'days_of_week')}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
            <input type='hidden' name='record_type' value='3P' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');

        $formAction = "{$cpCfg['cp.scopeRootAlias']}index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('promotion', 'promotion_id', $id);
        
        $expPromoUrl = array('urliseContent' => true);
        $expCheck = array('isLabelOnLeft' => true);
        
        $sqlCards = $fn->getDDSql('directory_cards');
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL('Loyalty Card', 'card_id', $sqlCards, $row['card_id'])}
                {$formObj->getTBRow('Title', 'title', $row['title'])}
                {$formObj->getTARow('Custom Text', 'custom_text', $row['custom_text'])}
                {$formObj->getSingleCheckBoxRow('Is Happy Hr. Promo.?', 'is_happy_hour_promo',
                                                $row['is_happy_hour_promo'], $expCheck)}
                {$formObj->getDateRow('Start Date', 'start_date', $row['start_date'])}
                {$formObj->getDateRow('End Date', 'end_date', $row['end_date'])}
                {$formObj->getTimeRow('Start Time', 'start_time', $row['start_time'])}
                {$formObj->getTimeRow('End Time', 'end_time', $row['end_time'])}
                {$formObj->getTBRow('Promo URL', 'promotion_url', $row['promotion_url'], $expPromoUrl)}
                {$formObj->getDaysOfWeeksRow('Days of Week', 'days_of_week', $row['days_of_week'])}
                {$media->getRightPanelMediaDisplay('Promotions Image', 'directory_promotion', 'picture', $row)}
            </fieldset>
            <input type='hidden' name='promotion_id' value='{$id}' />
            <input type='hidden' name='record_type' value='3P' />
        </form>
        ";

        return $text;
    }
}