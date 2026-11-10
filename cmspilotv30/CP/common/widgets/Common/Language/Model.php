<?
class CP_Common_Widgets_Common_Language_Model extends CP_Common_Lib_WidgetModelAbstract
{

    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT l.*
        FROM language l
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $c = $this->controller;
        $searchVar = $this->searchVar;
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 'l';

        $searchVar->sqlSearchVar[] = "l.published = 1";
        if ($c->countryId != ''){
            $searchVar->sqlSearchVar[] = "l.country_id = '{$c->countryId}'";
        } else if ($cpCfg['cp.multiCountry'] && isset($_SESSION['cp_country_id'])){
            $searchVar->sqlSearchVar[] = "l.country_id = '{$_SESSION['cp_country_id']}'";
        }

        if (($cpCfg['cp.hasMultiSites'] || $cpCfg['cp.hasMultiUniqueSites'])
             && isset($cpCfg['cp.site_id'])){
            $searchVar->sqlSearchVar[] = "l.site_id = '{$cpCfg['cp.site_id']}'";
        }

        $searchVar->sortOrder = "l.title";
    }

    /**
     *
     */
    function getDataArray() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $c = $this->controller;

        if (($cpCfg['cp.multiCountry'] && !$cpCfg['cp.useLangsInConfigForMultiCountry'])
            || ($cpCfg['cp.hasMultiSites'] && !$cpCfg['cp.useLangsInConfigForMultiSite'])){
            $modelHelper = Zend_Registry::get('modelHelper');
            $dataArray = $modelHelper->getWidgetDataArray($c, 'common_language');

            $arr = array();
            foreach ($dataArray as $key => $row){
                $url = $this->getUrl($row['lang_prefix']);
                $active = ($tv['lang'] == $key) ? 1 : 0;
                $arr[$row['lang_prefix']] = array(
                    'title' => $row['title'],
                    'url' => $url,
                    'active' => $active
                );
            }

            $this->dataArray = $arr;
            return $this->dataArray;

        } else {
            $arr = $cpCfg['cp.availableLanguages'];

            foreach ($cpCfg['cp.availableLanguages'] as $key => $langTitle){
                $active = ($tv['lang'] == $key) ? 1 : 0;
                $url = $this->getUrl($key);
                $arr[$key] = array('title' => $langTitle, 'url' => $url);

                $arr[$key] = array(
                    'title' => $langTitle,
                    'url' => $url,
                    'active' => $active
                );
            }
            
            if ($c->moveCurrentLangToEnd) {
                $currLangArr = $arr[$tv['lang']];
                unset($arr[$tv['lang']]);
                $arr[$tv['lang']] = $currLangArr;
            }
                        
            $this->dataArray = $arr;
            return $arr;
        }
    }

    /**
     *
     */
    function getUrl($key) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');

        $url = '';
        if (!$cpCfg['cp.useSEOUrl']){ //** ex: admin **//
            $searchQueryString = $pager->removeQueryString(array('lang'));
            $url = "{$searchQueryString}&lang={$key}";

        } else {
            $url = '';
            if ($cpCfg['cp.hasMultiUniqueSites'] && $tv['cp_site'] != ''){ // eg: /financial-services/eng/home/
                $pos = strlen($tv['cp_site']) + 5;
                if (substr($_SERVER['REQUEST_URI'], $pos) == ''){
                   	$url = "/{$tv['cp_site']}/{$key}/";
                } else {
                   	$url = "/{$tv['cp_site']}/{$key}" . substr($_SERVER['REQUEST_URI'], $pos);
                }
            } else {
                if (substr($_SERVER['REQUEST_URI'], 4) == ''){
                   	$url = "/{$key}/";
                } else {
                   	$url = "/{$key}" . substr($_SERVER['REQUEST_URI'], 4);;
                }
            }
        }
        return $url;
    }
}
