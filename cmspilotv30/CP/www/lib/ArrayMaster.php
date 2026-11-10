<?
class CP_Www_Lib_ArrayMaster extends CP_Common_Lib_ArrayMaster
{
    var $topVars    = array();
    var $resultset  = '';

    //==================================================================//
    function getInterfaceLanguage(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        if ($cpCfg['cp.multiCountry']){
            $this->setCountryId();
        }

        $langArr = $cpCfg['cp.availableLanguages'];

        $fn = Zend_Registry::get('fn');

        if ($cpCfg['cp.multiLang'] == '0'){
            $lang = $cpCfg['cp.defaultLanguage'];
        } else {
            $langReq  = $fn->getReqParam('lang');
            $langSess = $fn->getSessionParam('cpWwwLang');
            $langBrowserDefault = $cpCfg['cp.detectBrowserDefaultLang'] ? $fn->getBrowserDefaultLang() : '';

            $lang = '';

            if ($langReq != ''){
                $lang = $langReq;
            } else if ($langSess != ''){
                $lang = $langSess;
            } else if ($langBrowserDefault != ''){
                $lang = $langBrowserDefault;
            } else {

                if($cpCfg['cp.hasMultiSites'] || $cpCfg['cp.hasMultiUniqueSites']){
                    $lang = $cpCfg['cp.siteDefaultLang'];
                } else {
                    $lang = $cpCfg['cp.defaultLanguage'];
                }
            }

            if ($lang != '' && !array_key_exists($lang, $langArr)){
                $lang = $cpCfg['cp.defaultLanguage'];
            }

            $_SESSION['cpWwwLang'] = $lang;
        }
        return $lang;
    }

    //==================================================================//
    function getCountryCode(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        if ($cpCfg['cp.prefixCountryCodeInUrl'] == false){
            return;
        }
        return $_SESSION['cpCountryCode'];
    }

    //==================================================================//
    function setCountryId(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $countryObj = getCPWidgetObj('common_country', false);
        $countryObj->fns->setCountryIdInSession();
        $cpCountryId = $fn->getSessionParam('cp_country_id');
    }

    /**
     *
     */
    function setModuleNameInGlobal(){
        $tv = Zend_Registry::get('tv');

        /**
        the module value will be set for all special actions, hence initially set in the topvars
        otherwise, fetch the module name from the rooms array and set in $tv['module']
        **/
        $roomsArray = Zend_Registry::get('roomsArray');
        $subRoomsArray = Zend_Registry::get('subRoomsArray');
        $subCatArray = Zend_Registry::get('subCatArray');

        $modSec = isset($roomsArray[$tv['room']]) ? $roomsArray[$tv['room']]['module'] : '';
        $modCat = isset($subRoomsArray[$tv['subRoom']]) ? $subRoomsArray[$tv['subRoom']]['module'] : '';
        $modSubCat = isset($subCatArray[$tv['subCat']]) ? $subCatArray[$tv['subCat']]['module'] : '';

        $recTypeSec    = isset($roomsArray[$tv['room']]) ? $roomsArray[$tv['room']]['section_type'] : '';
        $recTypeCat    = isset($subRoomsArray[$tv['subRoom']]) ? $subRoomsArray[$tv['subRoom']]['category_type'] : '';
        $recTypeSubCat = isset($subCatArray[$tv['subCat']]) ? $subCatArray[$tv['subCat']]['sub_category_type'] : '';

        if ($tv['module'] == ''){
            if ($tv['subCat'] != ''){
                $tv['module'] = ($modSubCat != 'webBasic_content') ? $modSubCat : ($modCat != 'webBasic_content' ? $modCat : $modSec);

            } else if ($tv['subRoom'] != ''){
                $tv['module'] = ($modCat != 'webBasic_content') ? $modCat : $modSec;

            } else if ($tv['room'] != ''){
                $tv['module'] = $modSec;
            }
            list($tv['moduleGroup']) = explode('_', $tv['module']);

            $currentViewRecType = '';

            /******************************************************/
            if ($tv['subCat'] != '' && $recTypeSubCat != 'Content'){
                $currentViewRecType = $recTypeSubCat;
            } else if ($tv['subCat'] != ''){
                $currentViewRecType = $recTypeCat;
            }

            /******************************************************/
            if ($recTypeSubCat == 'Content' || $recTypeSubCat == ''){
                if ($tv['subRoom'] != '' && $recTypeCat != 'Content'){
                    $currentViewRecType = $recTypeCat;
                } else if ($tv['subRoom'] != ''){
                    $currentViewRecType = $recTypeSec;
                }
            }

            if ($tv['room'] != '' && ($currentViewRecType == '' || $currentViewRecType == 'Content')){
                $currentViewRecType = $recTypeSec;
            }
            $tv['currentViewRecType'] = $currentViewRecType;
        }
        //$tv['action'] = getReqParam('_action');
        CP_Common_Lib_Registry::arrayMerge('tv', $tv);
    }

    /**
     *
     */
    function setRoomIDInGlobal(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        if ($tv['room'] == '' && $tv['spAction'] == ''){
            $tv['room'] = $fn->getFirstRoom();
        }
    }

    /**
     *
     */
    function setArrays() {
        parent::setArrays();
        $this->setBasketArray();
    }

    /**
     *
     */
    function setBasketArray(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $basketObj = getCPModuleObj('ecommerce_basket');
        $cpCfg['cp.basketArray']['ecommerce_product'] = $basketObj->model->getBasketObj();

        CP_Common_Lib_Registry::arrayMerge('cpCfg', $cpCfg);
    }
}