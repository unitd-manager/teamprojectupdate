<?
class CP_Www_Modules_Museum_Library_Model extends CP_Common_Modules_Museum_Library_Model
{

    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT l.*
        FROM library l
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');

        $author     = $fn->getReqParam('author', '', true);
        $title      = $fn->getReqParam('title', '', true);
        $keyword    = $fn->getReqParam('keyword', '', true);
        $subject    = $fn->getReqParam('subject', '', true);
        $item_type  = $fn->getReqParam('item_type', '', true);
        $language   = $fn->getReqParam('language', '', true);
        $call_no    = $fn->getReqParam('call_no', '', true);
        $isbn_issn  = $fn->getReqParam('isbn_issn', '', true);

        $added_series_title  = $fn->getReqParam('added_series_title', '', true);
        $people    = $fn->getReqParam('people', '', true);
        $subjects  = $fn->getReqParam('subjects', '', true);
        $series    = $fn->getReqParam('series', '', true);

        $searchVar->sqlSearchVar['published'] = "l.published = 1";
        if ($tv['record_id'] != ''){
            $searchVar->sqlSearchVar['library_id'] = "l.library_id  = {$tv['record_id']}";
        }

        if ($tv['record_id'] == ''){

            if($author != ''){
                $searchVar->sqlSearchVar[] = "(
                    ({$this->getBooleanSearch('l.author', $author)}) OR
                    ({$this->getBooleanSearch('l.added_author', $author)}) OR
                    ({$this->getBooleanSearch('l.added_name', $author)})
                )";

//                $searchVar->sqlSearchVar[] = "(
//                    l.author        LIKE '%{$author}%' OR
//                    l.added_author  LIKE '%{$author}%' OR
//                    l.added_name    LIKE '%{$author}%'
//                )";
            }

            if($title != ''){
                $searchVar->sqlSearchVar[] = "(
                    ({$this->getBooleanSearch('l.series', $title)}) OR
                    ({$this->getBooleanSearch('l.added_series_title', $title)}) OR
                    ({$this->getBooleanSearch('l.title', $title)}) OR
                    ({$this->getBooleanSearch('l.added_title', $title)})
                )";
//                $searchVar->sqlSearchVar[] = "(
//                    l.series                LIKE '%{$title}%' OR
//                    l.added_series_title    LIKE '%{$title}%' OR
//                    l.title                 LIKE '%{$title}%' OR
//                    l.added_title           LIKE '%{$title}%'
//                )";
            }

            if($keyword != ''){
                $searchVar->sqlSearchVar[] = "(
                    ({$this->getBooleanSearch('l.added_name', $keyword)}) OR
                    ({$this->getBooleanSearch('l.author', $keyword)}) OR
                    ({$this->getBooleanSearch('l.added_author', $keyword)}) OR
                    ({$this->getBooleanSearch('l.note', $keyword)}) OR
                    ({$this->getBooleanSearch('l.people', $keyword)}) OR
                    ({$this->getBooleanSearch('l.search_terms', $keyword)}) OR
                    ({$this->getBooleanSearch('l.series', $keyword)}) OR
                    ({$this->getBooleanSearch('l.added_series_title', $keyword)}) OR
                    ({$this->getBooleanSearch('l.subjects', $keyword)}) OR
                    ({$this->getBooleanSearch('l.summary', $keyword)}) OR
                    ({$this->getBooleanSearch('l.title', $keyword)}) OR
                    ({$this->getBooleanSearch('l.added_title', $keyword)})
                )";
//                $searchVar->sqlSearchVar[] = "(
//                    l.added_name            LIKE '%{$keyword}%' OR
//                    l.author                LIKE '%{$keyword}%' OR
//                    l.added_author          LIKE '%{$keyword}%' OR
//                    l.note                  LIKE '%{$keyword}%' OR
//                    l.people                LIKE '%{$keyword}%' OR
//                    l.search_terms          LIKE '%{$keyword}%' OR
//                    l.series                LIKE '%{$keyword}%' OR
//                    l.added_series_title    LIKE '%{$keyword}%' OR
//                    l.subjects              LIKE '%{$keyword}%' OR
//                    l.summary               LIKE '%{$keyword}%' OR
//                    l.title                 LIKE '%{$keyword}%' OR
//                    l.added_title           LIKE '%{$keyword}%'
//                )";
            }

            if($subject != ''){
                $searchVar->sqlSearchVar[] = "(
                    ({$this->getBooleanSearch('l.people', $subject)}) OR
                    ({$this->getBooleanSearch('l.subjects', $subject)})
                )";      
//                $searchVar->sqlSearchVar[] = "(
//                    l.people    LIKE '%{$subject}%'   OR
//                    l.subjects  LIKE '%{$subject}%'
//                )";
            }

            if($item_type != ''){
                $searchVar->sqlSearchVar[] = "l.item_type  = '{$item_type}'";
            }

            if($language != ''){
                $searchVar->sqlSearchVar[] = "l.language  = '{$language}'";
            }

            if($call_no != ''){
                $searchVar->sqlSearchVar[] = "l.call_no LIKE '%{$call_no}%'";
            }

            if($isbn_issn != ''){
                $searchVar->sqlSearchVar[] = "(
                    l.isbn  LIKE '%{$isbn_issn}%' OR
                    l.issn  LIKE '%{$isbn_issn}%'
                )";
            }

            if($added_series_title != ''){
                $searchVar->sqlSearchVar[] = "l.added_series_title = '{$added_series_title}'";
            }

            if($people != ''){
                $searchVar->sqlSearchVar[] = "l.people = '{$people}'";
            }

            if($subjects != ''){
                $searchVar->sqlSearchVar[] = "l.subjects LIKE '%{$subjects}%'";
            }

            if($series != ''){
                $searchVar->sqlSearchVar[] = "l.series = '{$series}'";
            }
        }

        $searchVar->sortOrder = "l.title ASC, l.author ASC";
    }

    /**
     *
     * @param type $searchTerm
     */
    function getBooleanSearch($searchField, $searchTerm){
        $text = '';

        $termArrAND = explode('and', strtolower($searchTerm));
        $termArrOR  = explode('or' , strtolower($searchTerm));

        if(count($termArrAND) == 1 && count($termArrOR) == 1){
            $text .= "{$searchField} LIKE '%{$searchTerm}%'";
        } else if(count($termArrAND) > 1) {
            $termArr = $termArrAND;
            $counter = 1;
            foreach ($termArr as $item) {
                $text .= "{$searchField} LIKE '%".trim($item)."%'\n";
                if(count($termArr) > $counter ){
                    $text .= 'AND ';
                }
                $counter++;
            }
        } else if(count($termArrOR) > 1) {
            $termArr = $termArrOR;
            $counter = 1;
            foreach ($termArr as $item) {
                $text .= "{$searchField} LIKE '%".trim($item)."%'\n";
                if(count($termArr) > $counter ){
                    $text .= 'OR ';
                }
                $counter++;
            }
        }
        return $text;
    }
}