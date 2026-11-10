<?
class CP_Admin_Modules_Common_LanguageLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');


        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Title', 'title')}
                {$formObj->getDDRowByArr('Language', 'lang_prefix', $cpCfg['cp.availableLanguages'], '', array('useKey' => true))}
				{$formObj->getYesNoRRow('Published', 'published', 0)}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');


        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('title', 'title', $row['title'])}
                {$formObj->getDDRowByArr('Language', 'lang_prefix', $cpCfg['cp.availableLanguages'], $row['lang_prefix'], array('useKey' => true))}
				{$formObj->getYesNoRRow('Published', 'published', $row['published'])}
            </fieldset>
            <input type='hidden' name='language_id' value='{$row['language_id']}' />
        </form>
        ";

        return $text;
    }
}
