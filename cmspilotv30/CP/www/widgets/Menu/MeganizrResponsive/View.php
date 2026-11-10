<?
class CP_Www_Widgets_Menu_MeganizrResponsive_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('meganizr-1.1.0');
    /**
     *
     */
    function getWidget() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $c = &$this->controller;

        $text = '';
        if ($this->getRowsHTML() != ''){

            $text = "
            ";

            $text = "
            <ul class='meganizr mzr-slide mzr-responsive mzr-megamenu'>
                {$this->getRowsHTML()}
            </ul>
            ";

//            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
//                exp = {
//                     classParent: '{$c->classParent}'
//                    ,classContainer: '{$c->classContainer}'
//                    ,classSubLink: '{$c->classSubLink}'
//                    ,rowItems: '{$c->rowItems}'
//                    ,speed: '{$c->speed}'
//                    ,effect: '{$c->effect}'
//                    ,event: '{$c->event}'
//                    ,fullWidth: {$cpUtil->getBoolStr($c->fullWidth)}
//                }
//                cpw.menu.megaMenu.run(exp);
//            "));
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $mainNav = Zend_Registry::get('mainNav');
        $c = &$this->controller;

        $exp = array(
             'noOfSecToDisplay' => $c->noOfSecToDisplay
            ,'noOfCatToDisplay' => $c->noOfCatToDisplay
            ,'noOfSubCatToDisplay' => $c->noOfSubCatToDisplay
        );

        $rows = $this->getMenuDataRowsHTML($this->controller->btnPos, $exp);

        return $rows;
    }
    
   /**
     *
     */
    function getMenuDataRowsHTML($btnPos, $exp = array()) {
        $mainNav = Zend_Registry::get('mainNav');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $c = &$this->controller;
        
        $noOfSecToDisplay = $fn->getIssetParam($exp, 'noOfSecToDisplay', 100);
        $noOfCatToDisplay = $fn->getIssetParam($exp, 'noOfCatToDisplay', 100);
        $noOfSubCatToDisplay = $fn->getIssetParam($exp, 'noOfSubCatToDisplay', 100);

        $rows = '';
        $menuArr = $mainNav->model->getMenuDataArray();
        $arr = $menuArr['sections'];

        $secCounter = 0;
        foreach($arr AS $secId => $row) {
            $categories = $fn->getIssetParam($row, 'categories');

            if ($row['show_in_nav'] == 0 || ($btnPos != '' && $btnPos != $row['button_position']) || $noOfCatToDisplay == 0){
                continue;
            }

            $secCounter++;
            $catCounter = 0;
            $catRows = '';
            foreach($categories AS $catId => $rowCat) {
                $subCategories = $fn->getIssetParam($rowCat, 'subCategories');

                if ($rowCat['show_in_nav'] == 0 ){
                    continue;
                }

                $catCounter++;
                $subCatCounter = 0;
                $subCatRows = '';
                foreach($subCategories AS $subCatId => $rowSubCat) {
                    if ($rowSubCat['show_in_nav'] == 0 || $noOfSubCatToDisplay == 0){
                        continue;
                    }

                    $subCatCounter++;
                    $class = ($tv['subCat'] == $subCatId) ? " class='active'" : '';
                    $subCatRows .= "
                    <li{$class}>
                        {$mainNav->view->getUrlTextForMenu($rowSubCat)}
                    </li>
                    ";

                    if($subCatCounter >= $noOfSubCatToDisplay){
                        break;
                    }
                }

                $subCatRows = ($subCatRows != '') ? "<ul class='mzr-links'>{$subCatRows}</ul>" : '';

                $class = ($tv['subRoom'] == $catId) ? " active" : '';
                $catRows .= "
                <div class='{$class} one-col'>
                    {$mainNav->view->getUrlTextForMenu($rowCat)}
                    {$subCatRows}
                </div>
                ";

                if($catCounter >= $noOfCatToDisplay){
                    break;
                }
            }

            
            if($catRows != '') {
                $noOfColClass = '';
                if($catCounter > 0 && $catCounter <= 6){
                    $numText = $cpUtil->convertNumberToWords($catCounter);
                    $noOfColClass = "drop-{$numText}-columns";
                }
                
                $catRows = "
                <div class='mzr-content {$noOfColClass}'>
                    {$catRows}
                </div>
                ";
            }

            $section_type = $row['section_type'];
            $section_type = strtolower(preg_replace('#[ ]+#', '-', $section_type));

            $class = ($tv['room'] == $secId) ? "active" : '';
            $class .= " sec_type_{$section_type}";
            $class2 = isset($row['css_style_name']) ? " {$row['css_style_name']}" : '';
            $ddClass = ($catRows != '') ? 'mzr-drop' : '';
            $fullWidthClass = $c->fullWidth ? 'mzr-full-width' : '';
            
            $rows .= "
            <li class='{$class}{$class2} {$ddClass} {$fullWidthClass}'>
                {$mainNav->view->getUrlTextForMenu($row)}
                {$catRows}
            </li>
            ";

            if($secCounter >= $noOfSecToDisplay){
                break;
            }
        }

        return $rows;
    }    
}