<?
class CP_Common_Lib_Modules
{
    //==================================================================//
    function __construct(){
        $this->setModulesArray();
    }

    //==================================================================//
    function registerModule($modObj, $overrideArr = array()){
        foreach($overrideArr as $key => $value){
            $modObj[$key] = $value;
        }

        $modulesArr[$modObj['name']] = $modObj;
        CP_Common_Lib_Registry::arrayMerge('modulesArr', $modulesArr);
    }

    //==================================================================//
    function setModulesArray(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $tv = Zend_Registry::get('tv');

        $cpCfg = Zend_Registry::get('cpCfg');

        $arr = $cpCfg['cp.availableModules'];

        foreach($arr as $module){
            $fnMod = includeCPClass('ModuleFns', $module);
            if (method_exists($fnMod, 'setModuleArray')) {
                $fnMod->setModuleArray($this);
            } else {
                exit("setModuleArray is missing in {$module}");
            }
        }
    }

    //==================================================================//
    function removeFromAvailableModules($remove_module_arr = array()){
        $cpCfg = Zend_Registry::get('cpCfg');

        $arr = $cpCfg['cp.availableModules'];
        $availableModulesTemp = array();
        foreach($arr as $module){
            if(!in_array($module, $remove_module_arr)){
                $availableModulesTemp[] = $module;
            }
        }
        $cpCfg['cp.availableModules'] = $availableModulesTemp;
        CP_Common_Lib_Registry::arrayMerge('cpCfg', $cpCfg);
    }

    //==================================================================//
    function getModuleObj($module){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $actBtnsList = array('new');
        if ($cpCfg['cp.showDeleteActionBtnInList'] == 1){
            $actBtnsList[] = 'deleteList';
        }

        $modNameInfo = explode('_', $module);
        $modFold = $modNameInfo[0];
        $modName = $modNameInfo[1];

        $actBtnsDetail = array('edit', 'delete');
        $actBtnsNew    = array('cancelNew', 'addNew');
        $actBtnsEdit   = array('save', 'apply', 'cancel', 'delete');

        if (CP_SCOPE == 'www'){
            $actBtnsList = array();
            $actBtnsDetail = array();
            $actBtnsEdit   = array('save', 'cancel');
        }

        $arr['name']              = $module;
        $arr['title']             = ucfirst($modName);
        $arr['tableName']         = $modName;

        $arr['actBtnsList']       = $actBtnsList;
        $arr['actBtnsDetail']     = $actBtnsDetail;
        $arr['actBtnsEdit']       = $actBtnsEdit;
        $arr['actBtnsNew']        = $actBtnsNew;
        $arr['actBtnsSearch']     = array('cancel', 'performSearch');

        $arr['titleList']         = $arr['title']. ' - List';
        $arr['titleDetail']       = $arr['title']. ' - Modify';
        $arr['titleNew']          = $arr['title']. ' - New';

        $arr['listLimit']         = '50';
        $arr['keyField']          = $modName . '_id';
        $arr['titleField']        = 'title';
        $arr['links']             = array();
        $arr['media']             = array();

        $arr['relatedTables']     = array('media');
        $arr['hasMultiLang']      = 0;
        $arr['hasDeleteInList']   = 1;
        $arr['hasCheckboxInList'] = ($cpCfg['cp.hasCheckboxInListGlobal']) ? 1: 0;
        $arr['hasFlagInList']     = 1;
        $arr['publishFromList']= true;
        $arr['changeSortOrderFromList'] = true;
        $arr['defaultSearchSQL']  = '';

        $arr['hasEditInList']     = 1;
        $arr['hasDelete']         = 1; //*** to show delete in detail (used by mod_acc)
        $arr['otherActions']      = array();
        $arr['moduleGroup']       = '';
        $arr['folder']            = '';
        $arr['hideFromNav']       = false;
        $arr['hasDb']             = true;
        $arr['url']               = "index.php?_topRm={$tv['topRm']}&module={$module}";
        $arr['gotoLastPageByDefault']  = false;
        $arr['useRecordIdForDetailEditLink'] = false; //record_id added to detail/edit links
        $arr['showRecordCount'] = true; //show the record count in the list

        /***
        dependant modules to include js/css files
        ex: the js files from quote module needs to be included in projects
        ***/
        $arr['depModulesForJSS'] = array();
        $arr['plugins'] = array('common_media');
        $arr['widgets'] = array();
        $arr['jssKeys'] = array();

        /**
        * used for link modules to find out what is the actual main module for that link module
        * ex: for common_contactLink, main module is: common_contact
        **/
        $arr['mainModuleName'] = '';

        if (substr($module, -4) == 'Link'){
            $arr['mainModuleName'] = substr($module, 0, strlen($module) - 4);
        }

        /***
        normally action buttons are shown on the nav panel..
        if you want to show below the form toggle this on
        ***/
        $arr['showActBtnsBelowForm'] = false;

        /** if the module has only list, set this to true - this is used to check for usergroup modules **/
        $arr['hasOnlyListView'] = false;

        //scroll body content
        $arr['scrollContent'] = true;
        
        //has auto save function in edit page
        $arr['hasAutoSave'] = false;

        return $arr;
    }

    //==================================================================//
    function getValueByKey($module, $key){
        $modulesArr = Zend_Registry::get('modulesArr');
        $value = '';

        if (isset($modulesArr[$module][$key])){
            $value = $modulesArr[$module][$key];
        }

        return $value;
    }

    //==================================================================//
}