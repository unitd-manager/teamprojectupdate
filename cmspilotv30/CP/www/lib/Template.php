<?
class CP_Www_Lib_Template extends CP_Common_Lib_Template
{
    //==================================================================//
    function getBodySpAction($content, $bodyClass = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($tv['bodyExtraClass'] != ''){
            $bodyClass .= " {$tv['bodyExtraClass']}";
        }

        $text = "
        <body class='{$bodyClass}'>
        <div class='page_margins'>
            <div class='page'>
                {$content}
            </div>
        </div>
        ";
        return $text;
    }
    
    function getFooter($exp = array()) {
        $text = parent::getFooter($exp);
        return $text;
    }    
}