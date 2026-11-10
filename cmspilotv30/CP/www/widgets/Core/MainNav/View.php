<?
class CP_Www_Widgets_Core_MainNav_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     * @return <type>
     */
    function getWidget() {
        $c = &$this->controller;
        $surroundUl = $c->surroundUl;
        $class      = $c->class;
        $animate    = $c->animate;

        $text = '';
        if ($c->showAsTree){
            $wUiTree = getCPWidgetObj('ui_tree');
            $text = $wUiTree->getWidget(array(
                'btnPos' => $c->btnPos
            ));

        } else if ($c->showFullNavStructure){
            $rowsHTML = $this->getMenuDataRowsHTML($c->btnPos);

            if ($rowsHTML != ''){
                if ($surroundUl) {
                    $text = "
                    <div class='{$class}'>
                        <ul class='{$c->ulClass}'>{$rowsHTML}</ul>
                    </div>
                    ";
                } else {
                    $text = $rowsHTML;
                }
            }

        } else if ($this->getRowsHTML() != ''){
            if ($surroundUl) {
                $text = "
                <div class='{$class}'>
                    <ul class='{$c->ulClass}'>{$this->getRowsHTML()}</ul>
                </div>
                ";
            } else {
                $text = $this->getRowsHTML();
            }

            if ($animate){
                if ($c->animateType == 'bgPosition'){
                    CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jqBackgroundpos-v1.0'));

                    $cssPath = CP_WIDGETS_PATH_ALIAS . 'Core/MainNav/animate-bg-pos.css';
                    CP_Common_Lib_Registry::arrayMerge('cssFilesArr', array($cssPath));

                    CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                        $('.w-core-mainNav .hlist').addClass('animateBg');
                        cpw.core.mainNav.animageBg();
                    "));
                }
            }
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $c = &$this->controller;

        $btnPos         = $c->btnPos;
        $includeSecID   = $c->includeSecID;
        $hasSlidingDoorBtn = $c->hasSlidingDoorBtn;

        $rows = '';
        foreach ($this->model->dataArray as $key => $value) {
            $row = $this->model->dataArray[$key];

            $section_type = $row['section_type'];
            $section_type = strtolower(preg_replace('#[ ]+#', '-', $section_type));
            
            if ($this->isSkipRecord($row, $btnPos)){
                continue;
            }
            
            //--------------------//
            $target = '';
            if ($row['external_link'] != '') {
                $url = $row['external_link'];
                $target = " target='_blank'";

            } else if ($row['internal_link'] != '') {
                $url = $row['internal_link'];

            } else {
                $url = $row["url"];
            }

            $url = $fn->replaceUrlVars($url);

            //--------------------//
            $class = "";

            if ($includeSecID) {
                $class = " sec_{$key} sec_type_{$section_type}";
            }

            $class2 = isset($row['css_style_name']) ? " {$row['css_style_name']}" : '';
            $caption = isset($row['caption']) ? "<span class='caption'>{$row['caption']}</span>" : '';

            //--------------------//
            $classText = '';
            if ($tv['room'] == $key) {
                $classText = "active{$class}{$class2}";
            } else {
                $classText = "{$class}{$class2}";
            }

            $btnText = '';
            if ($hasSlidingDoorBtn) {
                $btnText = "<span>{$row['title']}</span>";
            } else {
                $btnText = "{$row['title']}";
            }

            $rows .= "
            <li class='{$classText}'>
                <a href='{$url}'{$target}><span>{$btnText}{$caption}</span></a>
            </li>\n
            ";
        }
        return $rows;
    }

    /**
     *
     */
    function isSkipRecord($row, $btnPos) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $section_type = $row['section_type'];
        
        $skip = false;
        
        if ($row['show_in_nav'] == 0){
            $skip = true;
        }

        if ($btnPos != '' && $btnPos != $row['button_position']){
            $skip = true;
        }

        if ($cpCfg['cp.hasMemberOnlySections'] == 1 && $row['member_only'] == 1 && !isLoggedInWWW()){
            $skip = true;
        }
        if ($cpCfg['cp.hasPublicOnlySections'] == 1 && $row['non_member_only'] == 1 && isLoggedInWWW()){
            $skip = true;
        }

        return $skip ;
    }

    /**
     *
     */
    function getMenuDataRowsHTML($btnPos, $exp = array()) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $subNav = getCPWidgetObj('core_subNav');
        $subCat = getCPWidgetObj('core_subCat');

        $noOfSecToDisplay = $fn->getIssetParam($exp, 'noOfSecToDisplay', 100);
        $noOfCatToDisplay = $fn->getIssetParam($exp, 'noOfCatToDisplay', 100);
        $noOfSubCatToDisplay = $fn->getIssetParam($exp, 'noOfSubCatToDisplay', 100);

        $rows = '';
        $menuArr = $this->model->getMenuDataArray();
        $arr = $menuArr['sections'];

        $secCounter = 0;
        
        foreach($arr AS $secId => $row) {
            $secCounter++;
            $categories = $fn->getIssetParam($row, 'categories');

            if ($this->isSkipRecord($row, $btnPos) || $noOfCatToDisplay == 0){
                continue;
            }

            $catCounter = 0;
            $catRows = '';
            foreach($categories AS $catId => $rowCat) {
                $catCounter++;
                $subCategories = $fn->getIssetParam($rowCat, 'subCategories');

                if ($subNav->view->isSkipRecord($rowCat)){
                    continue;
                }

                $subCatCounter = 0;
                $subCatRows = '';
                foreach($subCategories AS $subCatId => $rowSubCat) {
                    if ($subCat->view->isSkipRecord($rowSubCat) || $noOfSubCatToDisplay == 0){
                        continue;
                    }

                    $subCatCounter++;
                    $class = ($tv['subCat'] == $subCatId) ? " class='active'" : '';
                    $subCatRows .= "
                    <li{$class}>
                        {$this->getUrlTextForMenu($rowSubCat)}
                    </li>
                    ";

                    if($subCatCounter >= $noOfSubCatToDisplay){
                        break;
                    }
                }

                $subCatRows = ($subCatRows != '') ? "<ul>{$subCatRows}</ul>" : '';

                $class = ($tv['subRoom'] == $catId) ? " class='active'" : '';
                $catRows .= "
                <li{$class}>
                    {$this->getUrlTextForMenu($rowCat)}
                    {$subCatRows}
                </li>
                ";

                if($catCounter >= $noOfCatToDisplay){
                    break;
                }
            }

            $catRows = ($catRows != '') ? "<ul>{$catRows}</ul>" : '';

            $section_type = $row['section_type'];
            $section_type = strtolower(preg_replace('#[ ]+#', '-', $section_type));

            $class = ($tv['room'] == $secId) ? "active" : '';
            $class .= " sec_type_{$section_type}";
            $class2 = isset($row['css_style_name']) ? " {$row['css_style_name']}" : '';

            $rows .= "
            <li class='{$class}{$class2}'>
                {$this->getUrlTextForMenu($row)}
                {$catRows}
            </li>
            ";

            if($secCounter >= $noOfSecToDisplay){
                break;
            }
        }

        return $rows;
    }

    /**
     *
     * @param type $row
     * @return type
     */
    function getUrlTextForMenu($row) {
        $fn = Zend_Registry::get('fn');

        $target = '';
        if ($row['external_link'] != '') {
            $url = $row['external_link'];
            $target = " target='_blank'";

        } else if ($row['internal_link'] != '') {
            $url = $row['internal_link'];

        } else {
            $url = $row["url"];
        }

        $noFollow = ($row['no_follow'] == 1) ? " rel='nofollow'" : '';
        
        $url = $fn->replaceUrlVars($url);

        return "<a href='{$url}'{$target} {$noFollow}><span>{$row['title']}</span></a>";
    }
}