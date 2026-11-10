<?
class CP_Www_Widgets_Core_SubNav_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget($exp = array()){
        $c = &$this->controller;
        $surroundUl = $c->surroundUl;
        $title      = $c->title;

        $title = ($title != '') ? "<div class='title'>{$title}</div>" : '';

        $text = '';
        $rowsHTML = ($this->rowsHTML != '') ? $this->rowsHTML : $this->getRowsHTML();

        if ($rowsHTML != ''){
            if ($surroundUl) {
                $text = "
                <div class='{$c->class}'>
                    {$title}
                    <nav>
                        <ul class='noDefault {$c->ulClass}'>{$rowsHTML}</ul>
                    </nav>
                </div>
                ";

                if ($c->useUiTabsStyle) {
                    CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                        cpw.core.subNav.useJqUiStyle();
                    "));
                }

            } else {
                $text = $title . $rowsHTML;
            }
        }

        return $text;
    }

    /**
     *
     */
    function isSkipRecord($row) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $skip = false;
        
        if ($row['show_in_nav'] == 0 ){
            $skip = true;
        }

        if ($cpCfg['cp.hasMemberOnlyCategories'] == 1 && $row['member_only'] == 1 && !isLoggedInWWW()){
            $skip = true;
        }
        if ($cpCfg['cp.hasNonMemberOnlyCategories'] == 1 && $row['non_member_only'] == 1 && isLoggedInWWW()){
            $skip = true;
        }

        return $skip ;
    }

    /**
     *
     */
    function getRowsHTML() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $subCat = Zend_Registry::get('subCat');
        $cpCfg = Zend_Registry::get('cpCfg');

        $c = $this->controller;

        $rows = '';

        $dataArray = $this->model->dataArray;

        if ($c->displayShowAllMenu) {

            $arr = array();
            $arr[] = array(
                'category_id' => 0
                ,'title' => $c->showAllMenuText
                ,'titleEng' => $c->showAllMenuText
                ,'category_type' => ''
                ,'url' => $c->showAllMenuUrl
                ,'external_link' => ''
                ,'internal_link' => ''
                ,'show_in_nav' => 1
                ,'module' => ''
            );

            $dataArray = $arr + $dataArray;
        }

        foreach ($dataArray as $key => $row) {
            $class = '';

            if ($this->isSkipRecord($row)){
                continue;
            }
            
            $category_type = $row['category_type'];
            $category_type = strtolower(preg_replace('#[ ]+#', '-', $category_type));
            /*------------------------------------------------------------------*/
            $target = '';
            if ($row['external_link'] != '') {
                $url = $row['external_link'];
                $target = "target='_blank'";

            } else if ($row['internal_link'] != '') {
                $url = $row['internal_link'];

            } else {
                $url = $row["url"];
            }
            $url = $fn->replaceUrlVars($url);

            /*------------------------------------------------------------------*/
            $class = isset($row['css_style_name']) ? " {$row['css_style_name']}" : '';

            if ($c->includeCatID) {
                $class .= " cat_{$key} cat_type_{$category_type}";
            }

            if ($tv['subRoom'] == $key) {
                $subCatRows = ($c->showSubCat != false) ? 
                              $subCat->getWidget(array('categoryType' => $category_type)) : '';

                $rows .= "
                <li class='active{$class}'>
                    <a href='{$url}'{$target}>{$row['title']}</a>
                    {$subCatRows}
                </li>\n
                ";
            } else {
                $rows .= "
                <li class='{$class}'>
                    <a href='{$url}'{$target}>{$row['title']}</a>
                </li>\n
                ";
            }
        }

        return $rows;
    }

    /**
     *
     */
    function getWidgetGrid(){
        $this->model->dataArray = $this->model->getDataArray();
        $this->rowsHTML = $this->getRowsHTMLGrid();
        return $this->getWidget();
    }

    /**
     *
     */
    function getRowsHTMLGrid() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpPaths = Zend_Registry::get('cpPaths');

        $c = $this->controller;

        $rows = '';

        $dataArray = $this->model->dataArray;

        foreach ($dataArray as $key => $row) {
            $class = '';

            if ($row['show_in_nav'] == 0){
                continue;
            }
            if ($cpCfg['cp.hasMemberOnlyCategories'] == 1 && $row['member_only'] == 1 && !isLoggedInWWW()){
                continue;
            }
            if ($cpCfg['cp.hasNonMemberOnlyCategories'] == 1 && $row['non_member_only'] == 1 && isLoggedInWWW()){
                continue;
            }

            /*------------------------------------------------------------------*/
            $target = '';
            if ($row['external_link'] != '') {
                $url = $row['external_link'];
                $target = "target='_blank'";

            } else if ($row['internal_link'] != '') {
                $url = $row['internal_link'];

            } else {
                $url = $row["url"];
            }
            $url = $fn->replaceUrlVars($url);

            /*------------------------------------------------------------------*/
            $class = isset($row['css_style_name']) ? " {$row['css_style_name']}" : '';

            if ($c->includeCatID) {
                $class .= " cat_{$key}";
            }


            $thumb = '';
            $pic = $media->getMediaPicture('webBasic_category', 'picture', $row['category_id']);

            if ($pic == ''){
                $pic = $cpPaths->getTheLastFile('themes', $cpCfg['cp.theme'], 'images/cat-thumb.jpg', true);
                $pic = "<img src='{$pic}' />";
            }

            $desc = ($c->includeDescription) ? $row['description'] : '';

            $rows .= "
            <li>
                <div class='thumb'><a href='{$url}'{$target}>{$pic}</a></div>
                <a href='{$url}'{$target}>
                    <div class='text'>
                        <h4>{$row['title']}</h4>
                        {$desc}
                    </div>
                </a>
            </li>\n
            ";
        }

        return $rows;
    }
}