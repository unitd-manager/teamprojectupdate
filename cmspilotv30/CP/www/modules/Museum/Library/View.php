<?

class CP_Www_Modules_Museum_Library_View extends CP_Common_Modules_Museum_Library_View {

    var $jssKeys = array('jqForm-2.69');

    /**
     *
     */
    function getList($dataArray) {
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = '';
        foreach ($dataArray as $row) {
            $expUrl = array(
                 'record_id' => $row['library_id']
                ,'record_title' => $row['title']
            );

            //$detailPageUrl = $cpUrl->getUrlBySubCatType('Library - Search Result', 'Library', '', $expUrl);
            $detailPageUrl = $cpUrl->getUrlBySubCatType('Library - Search Result', '', '', $expUrl);

            $rows .= "
            <tr>
                <td><a href='{$detailPageUrl}'>{$row['author']}</a></td>
                <td><a href='{$detailPageUrl}'>{$row['title']}</a></td>
                <td>{$row['call_no']}</td>
                <td>{$row['status']}</td>
            </tr>
            ";
        }

        //$searchUrl = $cpUrl->getUrlByCatType('Library');
        $searchUrl = $cpUrl->getUrlBySubCatType('Library');
        $buttons = "
        <div class='floatbox'>
            <div class='float_right'>
                <a href='{$searchUrl}' class='button'>
                    {$ln->gd('m.museum.library.btn.newSearch')}
                </a>
            </div>
        </div>
        ";

        $theme = getCPThemeObj($cpCfg['cp.theme']);

        $text = "
        <div class='libraryList'>
            {$buttons}
            {$theme->view->getPagerPanel()}
            <table class='tblLibraryList'>
                <colgroup>
                    <col class='col-author' />
                    <col class='col-title' />
                    <col class='col-callNumber' />
                    <col class='col-status' />
                </colgroup>
                <thead>
                    <th>{$ln->gd('m.museum.library.lbl.author')}</th>
                    <th>{$ln->gd('m.museum.library.lbl.title')}</th>
                    <th>{$ln->gd('m.museum.library.lbl.callNumber')}</th>
                    <th>{$ln->gd('m.museum.library.lbl.status')}</th>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
            {$theme->view->getPagerPanel()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getDetail($row) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $exp = array('style' => 'mb5');
        $pic = $media->getMediaPicture('museum_library', 'picture', $row['library_id'], $exp);

        if ($pic != '') {
            $pic = "<div class='float_right'>{$pic}</div>";
        }

        $listUrl = $cpUrl->getUrlBySubCatType('Library - Search Result', 'Library');
        $searchUrl = $cpUrl->getUrlByCatType('Library');
        $buttons = "
        <div class='floatbox'>
            <div class='float_right'>
                <a href='{$searchUrl}' class='button'>
                    {$ln->gd('m.museum.library.btn.newSearch')}
                </a>
            </div>
            <div class='float_right'>
                <a href='javascript:history.back();' class='button'>
                    {$ln->gd('m.museum.library.btn.backToList')}
                </a>
            </div>
        </div>
        ";

        $titleRow = '';
        if($row['title'] != ''){
            $title = $row['title'];
            $titleRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.title')}</div>
                <div class='float_left value'>{$title}</div>
            </div>
            ";
        }

        $authorRow = '';
        if($row['author'] != ''){
            $author = nl2br($row['author']);
            $authorRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.authorEditor')}</div>
                <div class='float_left value'><a href='{$listUrl}?author={$row['author']}'>{$author}</a></div>
            </div>
            ";
        }

        $publicationRow = '';
        if($row['published_place'] != '' || $row['publisher'] != '' || $row['published_date'] != ''){
            $publicationRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.publicationDetails')}</div>
                <div class='float_left value'>{$row['published_place']}: {$row['publisher']}, {$row['published_date']}.</div>
            </div>
            ";
        }

        $physicalDescRow = '';
        if($row['physical_description'] != ''){
            $physical_description = nl2br($row['physical_description']);
            $physicalDescRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.physicalDescription')}</div>
                <div class='float_left value'>{$physical_description}</div>
            </div>
            ";
        }

        $seriesRow = '';
        if($row['series'] != ''){
            $series = nl2br($row['series']);
            $seriesRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.series')}</div>
                <div class='float_left value'><a href='{$listUrl}?series={$row['series']}'>{$series}</a></div>
            </div>
            ";
        }

        $noteRow = '';
        if($row['note'] != ''){
            $note = nl2br($row['note']);
            $noteRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.note')}</div>
                <div class='float_left value'>{$note}</div>
            </div>
            ";
        }

        $summaryRow = '';
        if($row['summary'] != ''){
            $summary = nl2br($row['summary']);
            $summaryRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.summary')}</div>
                <div class='float_left value'>{$summary}</div>
            </div>
            ";
        }

        $subjectsRow = '';
        if($row['subjects'] != '' || $row['people'] != ''){
            $people = ($row['people'] != '') ? "<a href='{$listUrl}?people={$row['people']}'>{$row['people']}<br />" : '';

            $subjects = '';
            if($row['subjects'] != ''){
                $subjectArr = explode("\n", $row['subjects']);
                foreach($subjectArr As $value){
                    $subjects .= "<a href='{$listUrl}?subjects={$value}'> {$value}</a><br/>";
                }
            }
            $subjectsRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.subjects')}</div>
                <div class='float_left value'>
                    {$people}
                    {$subjects}
                </div>
            </div>
            ";
        }

        $addedNameRow = '';
        if($row['added_name'] != ''){
            $added_name = nl2br($row['added_name']);
            $addedNameRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.addedName')}</div>
                <div class='float_left value'>{$added_name}</div>
            </div>
            ";
        }

        $addedTitleRow = '';
        if($row['added_title'] != ''){
            $added_title = nl2br($row['added_title']);
            $addedTitleRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.addedTitle')}</div>
                <div class='float_left value'>{$added_title}</div>
            </div>
            ";
        }

        $addedAuthorRow = '';
        if($row['added_author'] != ''){
            $added_author = nl2br($row['added_author']);
            $addedAuthorRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.addedAuthor')}</div>
                <div class='float_left value'>{$added_author}</div>
            </div>
            ";
        }

        $addedSeriesTitleRow = '';
        if($row['added_series_title'] != ''){
            $added_series_title = nl2br($row['added_series_title']);
            $addedSeriesTitleRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.addedSeriesTitle')}</div>
                <div class='float_left value'><a href='{$listUrl}?added_series_title={$row['added_series_title']}'>{$added_series_title}</a></div>
            </div>
            ";
        }

        $isbnIssnRow = '';
        if($row['isbn'] != '' || $row['issn'] != ''){
            $isbnIssnRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.isbnIssn')}</div>
                <div class='float_left value'>{$row['isbn']} {$row['issn']}</div>
            </div>
            ";
        }

        $statusRow = '';
        if($row['status'] != ''){
            $statusRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.status')}</div>
                <div class='float_left value'>{$row['status']}</div>
            </div>
            ";
        }

        $callNoRow = '';
        if($row['call_no'] != ''){
            $callNoRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.callNumber')}</div>
                <div class='float_left value'>{$row['call_no']}</div>
            </div>
            ";
        }

        $noOfCopiesRow = '';
        if($row['additional_copies'] > 0){
            $noOfCopiesRow = "
            <div class='floatbox'>
                <div class='float_left key'>{$ln->gd('m.museum.library.lbl.noOfCopies')}</div>
                <div class='float_left value'>{$row['additional_copies']}</div>
            </div>
            ";
        }

        $text = "
        {$buttons}
        <article class='libraryDetail'>
            {$pic}
            {$titleRow}
            {$authorRow}
            {$publicationRow}
            {$physicalDescRow}
            {$seriesRow}
            {$isbnIssnRow}
            {$noteRow}
            {$summaryRow}
            {$subjectsRow}
            {$addedNameRow}
            {$addedTitleRow}
            {$addedAuthorRow}
            {$addedSeriesTitleRow}
            {$statusRow}
            {$callNoRow}
            {$noOfCopiesRow}
        </article>
        ";


        return $text;
    }

    function getSearch() {
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $formObj = Zend_Registry::get('formObj');

        $modVl = getCPModuleObj('core_valuelist');
        $sqlItemType = $modVl->model->getValuelistSQL('libraryItemType', array('useEngValueAsKey' => 1 ));
        $sqlLanguage = $modVl->model->getValuelistSQL('libraryLanguage', array('useEngValueAsKey' => 1 ));

        $extraHtml = "
        <a class='jqui-dialog' href='/index.php?module=webBasic_content&showHTML=0&_spAction=spContent&lang={$tv['lang']}&ct=[[content_type]]'>
            <img src='/www/images/search_tip.jpg' />
        </a>";

        $expAuthor = array(
            'extraHtml' => str_replace('[[content_type]]', 'Search Tips: Author', $extraHtml)
        );
        $expTitle = array(
            'extraHtml' => str_replace('[[content_type]]', 'Search Tips: Title', $extraHtml)
        );
        $expKeyword = array(
            'extraHtml' => str_replace('[[content_type]]', 'Search Tips: Keyword', $extraHtml)
        );
        $expSubject = array(
            'extraHtml' => str_replace('[[content_type]]', 'Search Tips: Subject', $extraHtml)
        );
        $fieldset1 = "
        <div class='basicSearch'>
            {$formObj->getTBRow($ln->gd('m.museum.library.form.fld.author.lbl'),  'author', '', $expAuthor)}
            {$formObj->getTBRow($ln->gd('m.museum.library.form.fld.title.lbl'),   'title', '', $expTitle)}
            {$formObj->getTBRow($ln->gd('m.museum.library.form.fld.keyword.lbl'), 'keyword', '', $expKeyword)}
            {$formObj->getTBRow($ln->gd('m.museum.library.form.fld.subject.lbl'), 'subject', '', $expSubject)}
        </div>
        ";

        $expItemType = array(
            'extraHtml' => str_replace('[[content_type]]', 'Search Tips: Item Type', $extraHtml)
           ,'sqlType' => 'TwoFields'
        );
        $expLanguage = array(
            'extraHtml' => str_replace('[[content_type]]', 'Search Tips: Language', $extraHtml)
           ,'sqlType' => 'TwoFields'
        );
        $expCallNo = array(
            'extraHtml' => str_replace('[[content_type]]', 'Search Tips: Call Number', $extraHtml)
        );
        $expIsbnIssn = array(
            'extraHtml' => str_replace('[[content_type]]', 'Search Tips: ISBN ISSN', $extraHtml)
        );

        $fieldset2 = "
        <div class='limitSearch'>
            {$formObj->getDDRowBySQL($ln->gd('m.museum.library.form.fld.itemType.lbl'), 'item_type', $sqlItemType, '', $expItemType)}
            {$formObj->getDDRowBySQL($ln->gd('m.museum.library.form.fld.language.lbl'), 'language', $sqlLanguage, '', $expLanguage)}
            {$formObj->getTBRow($ln->gd('m.museum.library.form.fld.callNumber.lbl'), 'call_no', '', $expCallNo)}
            {$formObj->getTBRow($ln->gd('m.museum.library.form.fld.IsbnIssn.lbl'),   'isbn_issn', '', $expIsbnIssn)}
        </div>
        ";

        //$formAction = $cpUrl->getUrlBySubCatType('Library - Search Result', 'Library');
        $formAction = $cpUrl->getUrlBySubCatType('Library - Search Result');

        $wRecord = getCPWidgetObj('content_record');
        $contentArr = $wRecord->getWidget(array(
                'returnDataOnly' => true
            ,'global' => false
            ,'strictToPage' => true
        ));
        $introText = getCPModuleObj('webBasic_content')->view->getList($contentArr);

        $buttons = "
        <div class='floatbox'>
            <div class='float_right'>
                <a href=\"javascript:$('#librarySearch')[0].reset();\" class='reset button'>
                    {$ln->gd('m.museum.library.form.btn.reset')}
                </a>
            </div>
            <div class='float_right submit'>
                <a href=\"javascript:$('#librarySearch').submit();\" class='submit button'>
                    {$ln->gd('m.museum.library.form.btn.search')}
                </a>
            </div>
        </div>
        ";

        $text = "
        {$introText}
        <form method='get' action='{$formAction}' class='yform columnar' name='librarySearch' id='librarySearch'>
            {$buttons}
            {$formObj->getFieldSetWrapped($ln->gd('m.museum.library.form.basicSearch.heading'), $fieldset1)}
            {$formObj->getFieldSetWrapped($ln->gd('m.museum.library.form.limitSearch.heading'), $fieldset2)}
            {$buttons}
            <input type='submit' class='submithidden' name='x_submit'>
        </form>
        ";
        return $text;
    }
}
