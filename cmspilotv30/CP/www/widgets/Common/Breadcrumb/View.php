<?
class CP_Www_Widgets_Common_Breadcrumb_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget(){
        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;
        $text = '';
        if ($this->getRowsHTML() != ""){
            $prefixText = '';
            if($c->showPrefixText){
                $prefixText = "<span class='prefixText'>{$ln->gd('w.common.breadcrumb.prefixText')}</span>";
            }

            if($c->includeHomeUrl){
                $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
                $prefixText .= "<a href='/'>{$ln->gfv($secRec, 'title')}</a>{$c->seperator}";
            }

            $text = "
            {$prefixText}
            {$this->getRowsHTML()}
            ";

            if ($c->includeNavTag){
                $text = "
                <nav>
                    {$text}
                </nav>
                ";
            }
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $tv = Zend_Registry::get('tv');
        $c = &$this->controller;
        $seperator = $c->seperator;

        $rows = '';

        if($c->hideInHome && $tv['secType'] == 'Home'){
            return $rows;
        }

        $secText = $this->getSectionBread();
        $catText = $this->getCategoryBread();
        $subCatText = $this->getSubCategoryBread();
        $recordText = $this->getRecordBread();

        $c->hideInHome = '';

        if($secText != ''){
            $rows .= $secText;
        }

        if($catText != ''){
            $rows .= $seperator . $catText;
        }

        if($subCatText != ''){
            $rows .= $seperator . $subCatText;
        }

        if($recordText != ''){
            $rows .= $seperator . $recordText;
        }

        return $rows;
    }

   /**
     *
     * @return type
     */
    function getSectionBread(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $text = '';
        if (is_numeric($tv['room'])){
            // $urlArray = $this->model->getSectionUrlArray();
            // $url = $cpUrl->make_seo_url($urlArray);

            $rec = $fn->getRecordRowByID('section', 'section_id', $tv['room']);

            $wMainNav = getCPWidgetObj('core_mainNav');
            $url = $wMainNav->model->getUrl($rec);

            $text = "
            <a href='{$url}'>{$ln->gfv($rec, 'title')}</a>
            ";
        }

        return $text;
    }

    /**
     *
     * @return type
     */
    function getCategoryBread(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $text = '';

        if (is_numeric($tv['subRoom']) ){
            // $urlArray = $this->model->getCategoryUrlArray();
            // $url = $cpUrl->make_seo_url($urlArray);

            $SQL = "
            SELECT c.*
                  ,s.title AS section_title
            FROM category c
            JOIN section s ON (s.section_id = c.section_id)
            WHERE category_id = {$tv['subRoom']}
            ";            
            $rec = $fn->getRecordBySQL($SQL);

            $wSubNav = getCPWidgetObj('core_subNav');
            $url = $wSubNav->model->getUrl($rec);
            
            $text = "
            <a href='{$url}'>{$ln->gfv($rec, 'title')}</a>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getSubcategoryBread(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $text = '';

        if (is_numeric($tv['subCat']) ){
            $urlArray = $this->model->getSubCategoryUrlArray();
            $url = $cpUrl->make_seo_url($urlArray);
            $rec = $fn->getRecordRowByID('sub_category', 'sub_category_id', $tv['subCat']);

            $text = "
            <a href='{$url}'>{$ln->gfv($rec, 'title')}</a>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getRecordBread(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $modulesArr = Zend_Registry::get('modulesArr');
        $c = &$this->controller;

        $text = '';

        if (is_numeric($tv['record_id']) && $c->showRecordTitle){
            $urlArray = $this->model->getRecordUrlArray();
            $url = $cpUrl->make_seo_url($urlArray);

            $keyField   = $modulesArr[$tv['module']]["keyField"];
            $tableName  = $modulesArr[$tv['module']]["tableName"];
            $titleField = $modulesArr[$tv['module']]["titleField"];

            $rec = $fn->getRecordRowByID($tableName, $keyField, $tv['record_id']);

            $text = "
            <a href='{$url}'>{$ln->gfv($rec, $titleField)}</a>
            ";
        }

        return $text;
    }

}