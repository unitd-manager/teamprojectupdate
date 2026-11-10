<?
class CP_Www_Widgets_Common_Country_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT c.*
        FROM country c
        ";
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'c';

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'common_country');

        $arr = array();

        foreach ($dataArray as $row){
            $tmpArr = &$arr[$row['country_id']];
            $tmpArr['id']               = $row['country_id'];
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
            $url = '/' . $row['default_language'] . $cpUrl->getUriWithNoLang();
        } else {
            $url = $cpUrl->getUrlBySecType($tv['secType']);
            $urlArray = array();
            $urlArray['queryStr'] = "country_id={$row['country_id']}";
            $url = $cpUrl->make_seo_url($urlArray, 0, $url);
        }

        return $url;
    }
}