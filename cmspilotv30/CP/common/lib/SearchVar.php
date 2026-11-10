<?
class CP_Common_Lib_SearchVar
{
    var $sqlSearchVar   = array();
    var $sqlHavingVar   = array();
    var $sortOrder      = "";
    var $groupBy        = "";
    var $mainTableAlias = "";
    var $addSiteIdForMultiUniqueSites = true;

    //========================================================//
    function getSearchVar($objName, $includeSort = 1, $linkRecType = '', $exp=array()) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $fileType = $fn->getIssetParam($exp, 'fileType');
        $manualSearchVar = $fn->getIssetParam($exp, 'manualSearchVar');
        $expForSearchVar = $fn->getIssetParam($exp, 'expForSearchVar');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($fileType == 'Plugin'){
            $clsInst = $objName;
        } else if ($fileType == 'Widget'){
            $clsInst = $objName;
        } else if (is_object($objName)){
            $clsInst = $objName;
        } else {
            $clsInst = getCPModuleObj($objName);
        }

        $modName = $clsInst->name;
        $modelObj = $clsInst->model;
        $modelObj->expForSearchVar = $expForSearchVar;


        $funcName = "setSearchVar";
        if (method_exists($modelObj, $funcName)){
            if ($fileType == 'Plugin' || $fileType == 'Widget'){
                $modelObj->$funcName($exp);
            } else {
                $modelObj->$funcName($linkRecType, $modName, $exp);
            }
        } else {
            return;
        }

        $sqlSearchVar = "";

        $arr = is_array($manualSearchVar) ? $manualSearchVar : $this->sqlSearchVar;

        //multi unique site
        if ($cpCfg['cp.hasMultiUniqueSites']){
            $site_id = '';
            if (CP_SCOPE == 'admin'){
                $site_id = '';
                $cp_year = $fn->getSessionParam('cp_year');

                $module = ($tv['lnkRoom'] != '') ? $tv['lnkRoom'] : $tv['module'];
                if (!in_array($module, $cpCfg['w.common_multiUniqueSite.ignoreModules'])) {
                    $site_id = $fn->getSessionParam('cp_site_id');
                }
            } else if ($this->addSiteIdForMultiUniqueSites){
                $site_id = $cpCfg['cp.site_id'];
            }

            if ($site_id != ''){
                if ($this->mainTableAlias == ''){
                    exit("Please add [SearchVar]->mainTableAlias for the module {$modName}");
                } else {
                    $arr[] = "{$this->mainTableAlias}.site_id = '{$site_id}'";
                }
            }
        }

        //multi year
        if ($cpCfg['cp.hasMultiYears']){
            $cp_year = '';
            if (CP_SCOPE == 'www') {
                $cp_year = $fn->getReqParam('cp_year');
                $arr[] = "{$this->mainTableAlias}.cp_year = {$cp_year}";
            } else {//admin
                $cp_year = $fn->getSessionParam('cp_year');
                if (!in_array($tv['module'], $cpCfg['w.common_multiYear.ignoreModules'])) {
                    if ($this->mainTableAlias == ''){
                        exit("Please add [SearchVar]->mainTableAlias for the module {$modName}");
                    } else {
                        $arr[] = "{$this->mainTableAlias}.cp_year = {$cp_year}";
                    }
                }
            }
        }

        $count = 1;
        foreach($arr AS $condn) {
            $sqlSearchVar .= $condn;

            if ($count < count($arr)) {
                $sqlSearchVar .= "\n AND ";
            }

            $count++;
        }

        if ($sqlSearchVar != '') {
            $sqlSearchVar = " WHERE {$sqlSearchVar}";
        }

        if ($includeSort == 1) {
            if ($fileType != 'Plugin' && $fileType != 'Widget'){
                $this->setSortOrder();
            }

            if ($this->groupBy != '') {
                $sqlSearchVar .= "\n GROUP BY {$this->groupBy}";
            }
            if ($this->sortOrder != '') {
                $sqlSearchVar .= "\n ORDER BY {$this->sortOrder}";
            }
        }

        /****** used in jasper pritning *******/
        $tv['sortOrder'] = $this->sortOrder;
        $this->sqlSearchVar = array();
        $this->sortOrder = '';

        return $sqlSearchVar;
    }

    function getHavingVar($objName, $includeSort = 1, $linkRecType = '', $exp=array()) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $fileType = $fn->getIssetParam($exp, 'fileType');
        $manualSearchVar = $fn->getIssetParam($exp, 'manualSearchVar');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($fileType == 'Plugin'){
            $clsInst = $objName;
        } else if ($fileType == 'Widget'){
            $clsInst = $objName;
        } else if (is_object($objName)){
            $clsInst = $objName;
        } else {
            $clsInst = getCPModuleObj($objName);
        }

        $modName = $clsInst->name;
        $modelObj = $clsInst->model;

        $funcName = "setHavingVar";
        if (method_exists($modelObj, $funcName)){
            if ($fileType == 'Plugin' || $fileType == 'Widget'){
                $modelObj->$funcName($exp);
            } else {
                $modelObj->$funcName($linkRecType);
            }
        } else {
            return;
        }

        $arr = $this->sqlHavingVar;
        $sqlHavingVar = join(" AND \n", $arr);
        if ($sqlHavingVar != '') {
            $sqlHavingVar = " HAVING {$sqlHavingVar}";
        }

        return $sqlHavingVar;
    }

    //==================================================================//
    function setSortOrder() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        // if there is a sort order in the $tv, then use that otherwise do nothing
        // because sortorder is already set for each module, widget through search var
        // ex: $searchVar->sortOrder = 'a.title'

        $sortOrder = $tv['sortOrder'];
        if ($sortOrder != '') {
            $this->sortOrder = $sortOrder;
        } else {
            if (CP_SCOPE == 'www') {
                //if sortOrder is overridden in the config
                $sortOrder = $fn->getModuleConfigValue('sortOrder');
                if ($sortOrder != '') {
                    $this->sortOrder = $sortOrder;
                }
            }
        }
    }

}

