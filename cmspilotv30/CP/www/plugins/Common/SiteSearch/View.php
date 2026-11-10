<?
class CP_Www_Plugins_Common_SiteSearch_View extends CP_Common_Lib_PluginViewAbstract
{

    //========================================================//
    function getSearchBox($exp = array()){
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpUrl = Zend_Registry::get('cpUrl');
        
        $c = $this->controller;

        $boxTextColor      = isset($exp['boxTextColor']) ? $exp['boxTextColor'] : $cpCfg['p.common.siteSearch.boxTextColor'];
        $boxTextColorFocus = isset($exp['boxTextColorFocus']) ? $exp['boxTextColorFocus'] : $cpCfg['p.common.siteSearch.boxTextColorFocus'];
        $actionUrl = isset($exp['url']) ? $exp['url'] : $cpUrl->getUrlBySecType('Site Search');
        $showAdvSearchLink = isset($exp['showAdvSearchLink']) ? $exp['showAdvSearchLink'] : false;
        $useCssGoBtn = isset($exp['useCssGoBtn']) ? $exp['useCssGoBtn'] : false;
        
        $advSearchLink = '';
        if($showAdvSearchLink){
            $advSearchUrl = isset($exp['advSearchUrl']) ? $exp['advSearchUrl'] : $actionUrl;
            $advSearchLink = "
            <div class='float_right advSearchWrap'>
                <a href='{$advSearchUrl}' class='advsearch'>
                    {$ln->gd('p.common.siteSearch.btn.advancedSearch')}
                </a>
            </div>                
            ";
        }

        $goBtn = '';
        if ($useCssGoBtn) {
            $goBtn = "
            <a href=\"javascript:$('#frmKeyword').submit();\" class='submit'>GO
                <span class='hideme'>{$ln->gd('p.common.siteSearch.btn.search')}</span>
            </a>
            ";
        } else {
            $goBtn = "
            <a href=\"javascript:$('#frmKeyword').submit();\" class='submit'>
                <span class='hideme'>{$ln->gd('p.common.siteSearch.btn.search')}</span>
            </a>
            ";
        }
        
        $text = "
        <div id='siteSearch'>
            <form name='search' action='{$actionUrl}' method='get' id='frmKeyword'>
                <div class='floatbox'>
                    <div class='float_left keywordWrap'>
                        <input type='text' class='keyword' name='keyword' id='keyword' rel='pptxt: {$ln->gd('p.common.siteSearch.form.search.fld.keyword.lbl')}' value=''>
                    </div>
                    {$advSearchLink}
                    <div class='float_right submit'>{$goBtn}</div>
                </div>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getView($exp = array()) {
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');

        $keyword = qstrRev($tv['keyword']);
        
        if ($this->getRowsHTML() == ''){
            $text = "
            {$ln->gd('noSearchResults')}
            ";
        } else {
            $text = "
            <h3>{$ln->gd('p.common.siteSearch.lbl.searchResultsFor')}: '{$keyword}'</h3>
            <div id='siteSearchResult'>
                {$this->getRowsHTML()}
            </div>
            ";
        }

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $rows = '';

        $dataArray = $this->model->dataArray;

        if($dataArray != ''){
            foreach($dataArray AS $row) {
                $rows .= "
                <div class='row'>
                    <h4><a href='{$row['url']}'>{$row['title']}</a></h4>
                    <p>{$cpUtil->getSubString($row['description'], 300)}...<p>
                    <div class='readMore'><a href='{$row['url']}'>{$ln->gd('cp.lbl.more')}</a></div>
                </div>
                ";
            }
        }

        return $rows;
    }
}
