<?
class CP_Admin_Lib_Template extends CP_Common_Lib_Template
{

    //==================================================================//
    function getHeader() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "<!DOCTYPE html>
        <html>
            <head>
                {$this->getHTMLHeaderTags()}
                <meta name='robots' content='noindex, nofollow' />
            </head>
        ";

        return $text;
    }

    //==================================================================//
    function getBodySpAction($content) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text  = "
        <div class='page_margins'>
            <div class='page'>
                {$content}
            </div>
        </div>
        <input type='hidden' id='masterPathAlias' value='{$cpCfg['cp.masterPathAlias']}' />
        ";

        return $text;
    }
}
