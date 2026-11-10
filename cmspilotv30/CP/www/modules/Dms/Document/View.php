<?
class CP_Www_Modules_Dms_Document_View extends CP_Common_Lib_ModuleViewAbstract
{

    /**
     *
     */
    function getList($dataArray) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        $count = 1;

        foreach ($dataArray as $row){
            $attArr = $media->model->getFirstMediaRecord('dms_document', 'attachment', $row['document_id'] );

            $att = '';
            if (count($attArr) > 0){
                $saveUrl = "/index.php?plugin=common_media&_spAction=saveMedia&media_id={$attArr['media_id']}&showHTML=0";
                $att = " <a href='{$saveUrl}'>{$attArr['actual_file_name']}</a>";
            }

            $rows .= "
            <tr>
                <td class='curve'></td>
                <td>{$row['category_title']}</td>
                <td>{$row['title']}</td>
                <td>{$row['creation_date']}</td>
                <td>{$att}</td>
            </tr>
            ";
            $count++;
        }


        $country_id = $fn->getReqParam('country_id');
        $logged_in_country_id = $fn->getSessionParam('country_id');

        $upload = "";
        if ($logged_in_country_id == $country_id){
            $uploadUrl = $cpUrl->getUrlBySecType('Upload');
            $upload = "
            <div class='float_left'>
                <div class='upload'><a href='{$uploadUrl}'>{$ln->gd('w.dms.documentUpload.form.lbl.uploadFiles')}</a></div>
            </div>
            ";
        }

        $bannerArr = $media->getMediaFilesArray('webBasic_section', 'banner', $tv['room']);
        $fileLarge = count($bannerArr) > 0 ? $bannerArr[0]['file_large'] : '';
        $theme = getCPThemeObj($cpCfg['cp.theme']);
        $text = "
        <div class='floatbox'>
            {$upload}
            <div class='float_left'>
                {$this->getQuickSearch()}
            </div>
            <div class='float_right'>
                {$theme->view->getPagerPanel()}
            </div>
        </div>
        <div class='documentList'>
            <table>
                <thead>
                    <tr>
                        <th class='curve'></th>
                        <th>{$ln->gd('m.dms.document.lbl.category')}</th>
                        <th>{$ln->gd('cp.form.fld.title.lbl')}</th>
                        <th>{$ln->gd('m.dms.document.lbl.creationDate')}</th>
                        <th>{$ln->gd('m.dms.document.lbl.file')}</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
        </div>
        <script>
            $(function(){
                $('.page').css('background', 'url(\"{$fileLarge}\") no-repeat');
                $('.documentList table tbody tr:first td:first').addClass('first');
                $('.documentList table tbody tr:last td:first').addClass('last');
                $('.documentList table tbody tr:last').addClass('last');
            });
        </script>
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $category_id = $fn->getReqParam('category_id');

        $keyword = $fn->getReqParam('keyword');

        $SQLCategory = "
        SELECT DISTINCT c.category_id
        	   ,c.title
        FROM category c
        JOIN section s ON (c.section_id = s.section_id)
        WHERE c.published= 1
          AND s.section_type = 'Document'
        ORDER BY c.title
        ";
        $categoryOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLCategory, $category_id);

        $text = "
        <div class='quickSearch floatbox'>
            <form name='quickSearch' id='quickSearch' method='post' action=''>
                    <div class='float_left'>
                        <select name='category_id' class='category'>
                            <option value=''>{$ln->gd('m.dms.document.lbl.category')}</option>
                            {$categoryOptions}
                        </select>
                    </div>
                    <div class='float_left'>
                        <input type='text' class='searchbtn' name='keyword' value='{$keyword}' rel='pptxt:{$ln->gd('m.dms.document.search.lbl.keywordSearch')}'/>
                        <a href='javascript:void(0);' onclick=\"$('#quickSearch').submit()\" class='submitbtn'><img src='/www/images/spacer.gif' /></a>
                    </div>
            </form>
        </div>
        ";

        return $text;
    }
}
