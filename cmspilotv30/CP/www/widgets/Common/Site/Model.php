<?
class CP_Www_Widgets_Common_Site_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT s.*
        FROM site s
        ";
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 's';

        $searchVar->sqlSearchVar[] = "s.published = 1";
        $searchVar->sortOrder = "s.title";
    }

    /**
     *
     * @return <type>
     */
    function getDataArray(){
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'common_site');

        $arr = array();

        foreach ($dataArray as $row){
            $tmpArr = &$arr[$row['site_id']];
            $tmpArr['id']               = $row['site_id'];
            $tmpArr['title']            = $ln->gfv($row, 'title');
            $tmpArr['url']              = $this->getUrl($row);
            $tmpArr['default_language'] = $row['default_language'];
            $tmpArr['code']             = $row['country_code'];
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
            $url = "http://{$row['site_url']}/{$row['default_language']}" . $cpUrl->getUriWithNoLang();
        } else {
            $url = $cpUrl->getUrlBySecType($tv['secType']);
            $urlArray = array();
            $urlArray['queryStr'] = "site_id={$row['site_id']}";
            $url = $cpUrl->make_seo_url($urlArray, 0, $url);
        }

        return $url;
    }
}