<?
class CP_Www_Widgets_Core_SubCat_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget(){
        $c = &$this->controller;
        $ulClass    = $c->ulClass;
        $surroundUl = $c->surroundUl;

        $text = '';

        $heading = ($c->heading != '') ? "<div class='title'>{$c->heading}</div>" : '';
        $rowsHTML = ($this->rowsHTML != '') ? $this->rowsHTML : $this->getRowsHTML();
        if ($rowsHTML != ""){
            if ($surroundUl) {
                $text = "{$heading}<ul class='noDefault {$ulClass}'>{$rowsHTML}</ul>";
            } else {
                $text = $heading . $rowsHTML;
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

        if ($cpCfg['cp.hasMemberOnlySubCategories'] == 1 && $row['member_only'] == 1 && !isLoggedInWWW()){
            $skip = true;
        }
        if ($cpCfg['cp.hasNonMemberOnlySubCategories'] == 1 && $row['non_member_only'] == 1 && isLoggedInWWW()){
            $skip = true;
        }

        return $skip ;
    }
    
    /**
     *
     */
    function getRowsHTML() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        $gGroupName = '';
        foreach ($this->model->dataArray as $key => $value) {
            $class = '';

            $row = $this->model->dataArray[$key];

            if ($this->isSkipRecord($row)){
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

            $groupName = '';
            if($cpCfg['w.core.subCat.hasGrouping'] && $row['group_name'] != $gGroupName){
                $groupName = "
                <li class='groupName'>{$row['group_name']}</li>
                ";
                $gGroupName = $row['group_name'];
            }

            /*------------------------------------------------------------------*/
            if ($tv['subCat'] == $key) {
                $rows .= "
                {$groupName}
                <li class='active'>
                    <a href='{$url}'{$target}>{$row['title']}</a>
                </li>\n
                ";
            } else {
                $rows .= "
                {$groupName}
                <li>
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $media = Zend_Registry::get('media');

        $c = &$this->controller;

        $rows = '';
        foreach ($this->model->dataArray as $key => $value ) {
            $class = '';

            $row = $this->model->dataArray[$key];
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
            
            $thumb = '';
            if($c->showThumbForGrid){
                $pic = $media->getMediaPicture('webBasic_subCategory', 'picture', $row['sub_category_id'], array('zoomImage' => false));

                if ($pic == '' && $c->showDefaultImgForThumb){
                    $pic = "<img src='{$cpCfg['cp.themePathAlias']}{$cpCfg['cp.theme']}/images/sub-cat-thumb.jpg' />";
                }
                
                if($pic != ''){
                    $thumb = "<div class='thumb'><a href='{$url}'{$target}>{$pic}</a></div>";
                }
            }
            
            
            
            $desc = ($c->includeDescription) ? $row['description'] : '';

            $rows .= "
            <li>
                {$thumb}
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