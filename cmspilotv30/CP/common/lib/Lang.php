<?
class CP_Common_Lib_Lang
{
    var $langText = array();

    //========================================================//
    function __construct() {
        $tv = Zend_Registry::get('tv');
        $this->setLangArray($tv['lang']);
    }

    //========================================================//
    function setLangArray($lang = 'eng') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpPaths = Zend_Registry::get('cpPaths');
        if (CP_SCOPE == 'www'){
            $lang_path = "{$cpCfg['cp.langFilesPath']}lang-{$lang}.php";

            if(file_exists($lang_path)){
                include($lang_path);
                $this->langText = $LANGARR;
            } else {
                print "Could not load language file";
            }

        } else { //admin
            $LANGARR = array();
            $langArrDef = "{$cpCfg['cp.masterPath']}/lang/{$lang}.php";
            $langLocal = "{$cpCfg['cp.adminLangFilesPath']}/{$lang}.php";

            if (file_exists($langArrDef)) {
                include($langArrDef);
            }

            if (file_exists($langLocal)) {
                include($langLocal);
            }
            
            
            $this->langText = $LANGARR;
        }
    }

    //========================================================//
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

    /**
     *
     * @param <type> $key
     * @return <type>
     */
    function gd($key, $htmlspchar = false, $nltobr = false) {
        $text = $this->getData($key);
        if ($htmlspchar) {
            $text = htmlspecialchars($text, ENT_QUOTES);
        }
        if ($nltobr) {
            $text = nl2br($text);
        }
        return $text;
    }

    /**
     * this function will return empty if no key found in the array
     */
    function gd2($key) {
        if(isset($this->langText[$key])) {
            $text = $this->langText[$key];
            return stripslashes($text);
        }
    }
    
    //========================================================//
    function getLangFieldValue($row, $fieldName, $showDefault = true, $lang='') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        if ($lang == '') {
            $lang = $tv['lang'];
        }

        if($lang == 'eng' || $lang == '') {
            $fieldPrefix = '';
        } else {
            $fieldPrefix = $lang . '_';
        }
        //print $lang . '----------V';

        $fieldVal  = $fn->getIssetParam($row, $fieldPrefix . $fieldName);
        $fieldVal1 = $row[$fieldName];

        if($fieldVal != '') {
            return $fieldVal;
        } else if ($showDefault == 1) {
            return $fieldVal1;
        } else {
            return '';
        }
    }

    //========================================================//
    function gfv($row, $fieldName, $showDefault='1', $lang='') {
        return $this->getLangFieldValue($row, $fieldName, $showDefault, $lang);
    }

    //========================================================//
    function gfp($lang='') {
        return $this->getFieldPrefix($lang);
    }

    //========================================================//
    function getFieldPrefix($lang='') {
        $tv = Zend_Registry::get('tv');

        if ($lang == '') {
            $lang = $tv['lang'];
        }

        if($lang  == 'eng' || $lang  == '') {
            $fieldPrefix = '';
        } else {
            $fieldPrefix = $lang  . '_';
        }

        return $fieldPrefix;
    }

    //========================================================//
    function getLangFolder($lang='') {
        $tv = Zend_Registry::get('tv');

        if($tv['lang'] == 'eng' || $tv['lang'] == '') {
            return '';
        } else {
            return $lang . '/';
        }
    }

    //==================================================================//
    function getLanguageButtons() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $am = Zend_Registry::get('am');
        $pager = Zend_Registry::get('pager');

        $rows      = '';
        $langArray = $cpCfg['cp.availableLanguages'];

        foreach ($langArray as $key => $value) {
            $langKey   = $key;
            $langTitle = $value;

            $searchQueryString = $pager->removeQueryString(array('lang'));

            if ($tv['lang'] == $langKey) {
                $rows .= "<li class='active'>{$langTitle}</li>";
            } else {
                $rows .= "<li><a href='{$searchQueryString}&lang={$langKey}'>{$langTitle}</a></li>";
            }
        }
        $text = "<div id='lang-panel' class='hlist'><ul>{$rows}</ul></div>";

        return $text;
    }

    //========================================================//
    function getSingleLanguageButton($langValue, $value) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $text = "";

        $returnURL  = "index.php?{$_SERVER['QUERY_STRING']}";

        //-----------------------------------------------------------//
        if ( $tv['lang'] == $langValue) {
        } else {
            $lang = $langValue;
            $url      =  "index.php?_spAction=changeLang&lang={$lang}";
            $JSText   = "javascript:DBUtil.changeLang2('{$url}', '{$returnURL}');";
            $textTemp = $fn->getMouseOverImage2($image1, $image2, $JSText);
            $text    .= $textTemp;
        }

        return $text;
    }

    //==================================================================//

}

