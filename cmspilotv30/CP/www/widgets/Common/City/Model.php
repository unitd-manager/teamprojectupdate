<?
class CP_Www_Widgets_Common_City_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT c.*
        FROM city c
        ";
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'c';

        if ($cpCfg['cp.multiCountry'] && isset($_SESSION['cp_country_id'])){
            $searchVar->sqlSearchVar[] = "c.country_id = '{$_SESSION['cp_country_id']}'";
        }

        $searchVar->sqlSearchVar[] = "c.published = 1";
        $searchVar->sortOrder = "c.title";
    }

    /**
     *
     * @return <type>
     */
    function getDataArray(){
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'common_city');

        $arr = array();

        foreach ($dataArray as $row){
            $tmpArr = &$arr[$row['city_id']];
            $tmpArr['id']               = $row['city_id'];
            $tmpArr['title']            = $ln->gfv($row, 'title');
            $tmpArr['url']              = $this->getUrl($row);
            $tmpArr['default_language'] = $row['default_language'];
            $tmpArr['code']             = $row['city_code'];
        }

        $this->dataArray = $arr;
        return $this->dataArray;
    }

    /**
     *
     */
    function getUrl($row){
        $cpUrl = Zend_Registry::get('cpUrl');
        $tv = Zend_Registry::get('tv');

        $c = &$this->controller;

        if ($c->showAsMenu) {
            $url = '/' . $row['default_language'] . $cpUrl->getUriWithNoLang();
        } else {
            $url = $cpUrl->getUrlBySecType($tv['secType']);
            $urlArray = array();
            $urlArray['queryStr'] = "city_id={$row['city_id']}";
            $url = $cpUrl->make_seo_url($urlArray, 0, $url);
        }

        return $url;
    }
}