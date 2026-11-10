<?
class CP_Www_Widgets_Content_Record_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $roomsArray = Zend_Registry::get('roomsArray');

        $c = &$this->controller;
        $contentType  = $c->contentType;
        $global       = $c->global;
        $strictToPage = $c->strictToPage;

        $condn = '';
        $numRows = 0;
        /** if the content does not actually need to belong to any section or category use this sql **/
        if ($global){
            $SQL     = $this->getSQLCommon($contentType);
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
        } else {


            /**
            * if the content does not exist in a lower level (ex: sub cat or cat)
            * then try to show from the upper levels (ex: section)
            * if $strictToPage is set to true then the above fallback will not happen
            */
            if ($strictToPage){
                $alias_to_section_id = getCPModelObj('webBasic_Section')->getSectionIdAliasedTo();
                $exp = array();
                //has content records for Sub Category (with section & category of course)
                if ($tv['subCat'] != "" && is_numeric($tv['subCat'])){
                    if ($alias_to_section_id != ''){
                        $exp['useMultiUniqueSiteId'] = false;
                        $condn = "
                        AND c.section_id = {$alias_to_section_id}
                        AND c.category_id = {$tv['subRoom']}
                        AND c.sub_category_id = {$tv['subCat']}
                        ";
                    } else {
                        $condn = "
                        AND c.category_id = {$tv['subRoom']}
                        AND c.sub_category_id = {$tv['subCat']}
                        ";
                    }
                    $SQL     = $this->getSQLCommon($contentType, $condn, $exp);
                    $result  = $db->sql_query($SQL);
                    $numRows = $db->sql_numrows($result);

                //has content records for Category (with section of course)
                } else if ($tv['subRoom'] != "" && is_numeric($tv['subRoom'])){
                    $condn = "
                    AND c.section_id = {$tv['room']}
                    AND c.category_id = {$tv['subRoom']}
                    ";
                    $SQL     = $this->getSQLCommon($contentType, $condn);
                    $result  = $db->sql_query($SQL);
                    $numRows = $db->sql_numrows($result);

                //has content records for Section only
                } else if ($tv['room'] != "" && is_numeric($tv['room'])){
                    $condn = "
                    AND c.section_id = {$tv['room']}
                    AND (c.category_id = '' OR c.category_id is null)
                    ";
                    $SQL     = $this->getSQLCommon($contentType, $condn);
                    $result  = $db->sql_query($SQL);
                    $numRows = $db->sql_numrows($result);
                }

            } else {
                if ($tv['subCat'] != "" && is_numeric($tv['subCat'])){
                    $condn = "
                    AND c.category_id = {$tv['subRoom']}
                    AND c.sub_category_id = {$tv['subCat']}
                    ";
                    $SQL     = $this->getSQLCommon($contentType, $condn);
                    $result  = $db->sql_query($SQL);
                    $numRows = $db->sql_numrows($result);
                }

                if ($numRows == 0 && $tv['subRoom'] != "" && is_numeric($tv['subRoom'])){
                    $condn = "
                    AND c.section_id = {$tv['room']}
                    AND c.category_id = {$tv['subRoom']}
                    ";
                    $SQL     = $this->getSQLCommon($contentType, $condn);
                    $result  = $db->sql_query($SQL);
                    $numRows = $db->sql_numrows($result);
                }

                if ($numRows == 0 && $tv['room'] != "" && is_numeric($tv['room'])){
                    $condn = "
                    AND c.section_id = {$tv['room']}
                    AND (c.category_id = '' OR c.category_id is null)
                    ";
                    $SQL     = $this->getSQLCommon($contentType, $condn);
                    $result  = $db->sql_query($SQL);
                    $numRows = $db->sql_numrows($result);
                }

                if ($numRows == 0 && $contentType != "Record"){
                    $condn = "
                    AND (c.section_id  = '' OR c.section_id IS NULL)
                    ";
                    $SQL     = $this->getSQLCommon($contentType, $condn);
                    $result  = $db->sql_query($SQL);
                    $numRows = $db->sql_numrows($result);
                }
            }
        }
        //fb::log($SQL);
        //print $SQL;
        return $SQL;
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getSQLCommon($content_type, $condn = '', $exp = array()){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $useMultiUniqueSiteId = $fn->getIssetParam($exp, 'useMultiUniqueSiteId', true);

        $condArr = array($condn);
        $c = &$this->controller;
        $specialFilter = $c->specialFilter;
        $displayLimit = $c->displayLimit;
        $orderBy = $c->orderBy;
        $addSearchCond = $c->addSearchCond;

        if ($c->extraSqlCondn != ''){
            $condArr[] = $c->extraSqlCondn;
        }

        if ($specialFilter == 'Latest'){
            $condArr[] = "c.latest = 1";
        }

        if ($specialFilter == 'Favourite'){
            $condArr[] = "c.favourite = 1";
        }


        if ($c->sectionId != ''){
            $condArr[] = "c.section_id = '{$c->sectionId}'";
        }

        if ($c->categoryId != ''){
            $condArr[] = "c.category_id = '{$c->categoryId}'";
        }

        if ($c->contentId != ''){
            $condArr[] = "c.content_id = '{$c->contentId}'";
        }

        if ($specialFilter == 'By Section Type' || $c->sectionType != ''){
            $secRec = getCPModelObj('webBasic_section')->getRecordByType($c->sectionType);
            if(is_array($secRec)){
                $condArr[] = "c.section_id = {$secRec['section_id']}";
            } else {
                $condArr[] = "s.section_type = '{$c->sectionType}'";
            }
        }

        if ($specialFilter == 'By Category Type' || $c->categoryType != ''){
            $catRec = getCPModelObj('webBasic_category')->getRecordByType($c->categoryType, $c->sectionType);
            if(is_array($catRec)){
                $condArr[] = "c.category_id = {$catRec['category_id']}";
            }
        }
        if (!$cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasWebOnlyContent']){
            $condArr[] = "c.ok_for_web = 1";
        }

        if ($cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasMobileOnlyContent']){
            $condArr[] = "c.ok_for_mobile = 1";
        }

        if ($cpCfg['cp.hasMultiSites']){
            $condArr[] = "
            c.content_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'webBasic_content'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
            )";
        }

        if ($cpCfg['cp.hasMultiYears']) {
            $condArr[] = "c.cp_year = '{$tv['cp_year']}'";
        }

        if ($cpCfg['cp.hasMultiUniqueSites'] && !$c->globalForAllSites && $useMultiUniqueSiteId) {
            $condArr[] = "c.site_id = {$cpCfg['cp.site_id']}";
        }

        $condn = join(' AND ', $condArr);
        $commentCountSQL = '';
        if ($cpCfg['m.webBasic.content.includeCommentCount']){
            $commentCountSQL = "(
            SELECT count(*)
            FROM comment
            WHERE room_name = 'webBasic_content'
              AND record_id = c.content_id
              AND published = 1
            ) AS comments_count,
            ";
        }

        $SQL = "
        SELECT {$commentCountSQL}
               c.*
              ,s.title as section_title
              ,ct.title as category_title
              ,sc.title as sub_category_title
        FROM content c
        LEFT JOIN section s ON (s.section_id = c.section_id)
        LEFT JOIN category ct ON (ct.category_id = c.category_id)
        LEFT JOIN sub_category sc ON (sc.sub_category_id = c.sub_category_id)
        WHERE c.published = 1
          AND c.content_type = '{$content_type}'
          {$condn}
          {$addSearchCond}
        ORDER BY {$orderBy}
        LIMIT 0, {$displayLimit}
        ";
        //fb::log($SQL);
        //print $SQL . "<hr>";

        return $SQL;
    }

    //========================================================//
    function getDataArray() {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $media = Zend_Registry::get('media');

        $c = $this->controller;

        $SQL = $this->getSQL();
        //print "<pre class='sql'>" . $SQL . '</pre>';
        $result = $db->sql_query($SQL);

        $dataArray = array();
        $counter = 0;

        while ($row = $db->sql_fetchrow($result, MYSQL_ASSOC)) {
            $arrTemp = &$dataArray[$counter];

            foreach($row as $key => $value){
               $arrTemp[$key] = $value;
            }

            $arrTemp['title']          = $ln->gfv($row, 'title');
            $arrTemp['content_id']     = $row['content_id'];
            $arrTemp['show_title']     = $row['show_title'];

            if ($c->includeUrlFld){
                $arrTemp['url'] = $cpUrl->getUrlByRecord($row, 'content_id');
            }

            $arrTemp['external_link']  = $row['external_link'];
            $arrTemp['internal_link']  = $row['internal_link'];
            $arrTemp['desc_short']     = $ln->gfv($row, 'description_short');
            $arrTemp['description']    = $ln->gfv($row, 'description');
            $arrTemp['content_date']   = $row['content_date'];

            $arrTemp['section_id']     = $row['section_id'];
            $arrTemp['category_id']    = $row['category_id'];

            if($row['section_title'] != ''){
                $secRec = $fn->getRecordRowByID('section', 'section_id', $row['section_id']);
                if(is_array($secRec)){
                    $arrTemp['section_title_lang']  = $ln->gfv($secRec, 'title');
                }
            }

            if($row['category_title'] != ''){
                $catRec = $fn->getRecordRowByID('category', 'category_id', $row['category_id']);
                if(is_array($secRec)){
                    $arrTemp['category_title_lang']  = $ln->gfv($catRec, 'title');
                }
            }

            if($row['sub_category_title'] != ''){
                $subCatRec = $fn->getRecordRowByID('sub_category', 'sub_category_id', $row['sub_category_id']);
                if(is_array($subCatRec)){
                    $arrTemp['sub_category_title_lang']  = $ln->gfv($subCatRec, 'title');
                }
            }

            $arrTemp['mediaPicArray']  = array();
            $arrTemp['mediaAttArray']  = array();
            if ($c->includeMediaRec){
                $arrTemp['mediaPicArray'] = $media->getMediaFilesArray('webBasic_content', 'picture', $row['content_id']);
                $arrTemp['mediaAttArray'] = $media->getMediaFilesArray('webBasic_content', 'attachment', $row['content_id']);
            }

            if ($c->additionalMediaRecType != ''){
                $arrTemp['mediaOtherArray'] = $media->getMediaFilesArray('webBasic_content', $c->additionalMediaRecType, $row['content_id']);
            }

            $counter++;
        }
        if ($c->dataArrayPostCallback) {
            //call_user_func($c->dataArrayPostCallback, &$dataArray, $c->dataArrayPostCallbackParamArr);
            // http://stackoverflow.com/questions/8971261/php-5-4-call-time-pass-by-reference-easy-fix-available
            call_user_func($c->dataArrayPostCallback, $dataArray, $c->dataArrayPostCallbackParamArr);
        }
        return $dataArray;
    }
}
