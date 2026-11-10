<?
class CP_Admin_Lib_Lang extends CP_Common_Lib_Lang
{

    function __construct() {
        $tv = Zend_Registry::get('tv');
        $this->setLangArray($tv['langAdminInt']); //set the lang array for the admin interface
    }
    
    function gd($key, $default = '', $nltobr = false) {
        $text = $this->getData($key, $default);
        return $text;
    }

    function getData($key, $default = '') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        
        if ($cpCfg['cp.hasMultiYears'] && CP_SCOPE == 'www') {
            $key = $tv['cp_year'] . '-' . $key;
        }        
        if(isset($this->langText[$key])) {
            $text = $this->langText[$key];
            return stripslashes($text);
        } else if($default != '') {
            return $default;
        } else {
            return $key;
        }
    }
}
