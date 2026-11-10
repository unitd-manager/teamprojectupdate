<?
/**
 *
 */
class CPL_Admin_Lib_ListObj extends CP_Common_Lib_ListObj
{

    function getListHeader($extraParam = array()) {
        $tv = Zend_Registry::get('tv');
        $pager = Zend_Registry::get('pager');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $hasFlagInList = $modulesArr[$tv['module']]['hasFlagInList'];

        $hasRowNumber  = isset($extraParam['hasRowNumber'])  ? $extraParam['hasRowNumber']  : true;
        $hasEditInList = isset($extraParam['hasEditInList']) ? $extraParam['hasEditInList'] : true;
        $noScrollableTable = isset($extraParam['noScrollableTable']) ? $extraParam['noScrollableTable'] : false;

        $hasFlagInList = isset($extraParam['hasFlagInList']) ? $extraParam['hasFlagInList'] : $hasFlagInList;
        $hasFlagInListBlue = isset($extraParam['hasFlagInListBlue']) ? $extraParam['hasFlagInListBlue'] : 0;
        $hasFlagInListGreen = isset($extraParam['hasFlagInListGreen']) ? $extraParam['hasFlagInListGreen'] : 0;

        $scrollableTableText = "scrollabletable='1'";
        if ($noScrollableTable) {
            $scrollableTableText = '';
        }

        if ($tv['action'] == "list") {
            $searchQueryString = $pager->removeQueryString(array("_action"));
            $formAction = $searchQueryString . "&_spAction=saveList&showHTML=0";

            $cbText        = '';
            $rowNumberText = '';
            $editRowText   = '';
            $flagCell      = '';

            if ($modulesArr[$tv['module']]['hasCheckboxInList'] == 1 && $cpCfg['cp.hasCheckboxInList']) {
                $cbText = "
                <th class='header' align='center' width='10'>
                   <input class='check' type='checkbox' name='toggle' value='' />
                </th>
                ";
            }

            if ($hasRowNumber) {
                $rowNumberText = "
                <th width='5'> # </th>
                ";
            }

            $hasEditAccess = true;
            if (CP_SCOPE == 'admin' && $cpCfg['cp.hasAccessModule']){
                $hasEditAccess = getCPModuleObj('core_userGroup')->model->hasAccessToAction($tv['module'], 'edit');
            }

            if ($hasEditInList && $hasEditAccess) {
                $editRowText = "
                <th align='center' width='10'></th>
                ";
            }

            if ($hasFlagInList) {
                $color = 'red';
                $flagCell .= "
                <th align='center' width='25'>
                <a href='#' class='list-flag-all' color='{$color}'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/flag_on_{$color}.png' border='0'>
                </a>
                <a href='#' class='list-unflag-all' color='{$color}'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/flag_off.png' border='0'>
                </a>
                </th>
                ";
            }
            if ($hasFlagInListBlue) {
                $color = 'blue';
                $flagCell .= "
                <th align='center' width='25'>
                <a href='#' class='list-flag-all' color='{$color}'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/flag_on_{$color}.png' border='0'>
                </a>
                <a href='#' class='list-unflag-all' color='{$color}'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/flag_off.png' border='0'>
                </a>
                </th>
                ";
            }
            if ($hasFlagInListGreen) {
                $color = 'green';
                $flagCell .= "
                <th align='center' width='25'>
                <a href='#' class='list-flag-all' color='{$color}'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/flag_on_{$color}.png' border='0'>
                </a>
                <a href='#' class='list-unflag-all' color='{$color}'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/flag_off.png' border='0'>
                </a>
                </th>
                ";
            }

            $text = "
            <form name='list' class='cpJqForm' id='listForm' action='{$formAction}' method='post' >
            <table class='thinlist' {$scrollableTableText} id='bodyList' cellspacing='1'>
                <thead>
                <tr>
                {$rowNumberText}
                {$cbText}
                {$editRowText}
                {$flagCell}
            ";

        } else {

            $rowNumberText = '';

            if ($hasRowNumber) {
                $rowNumberText = "
                <th width='5'> # </th>
                ";
            }
            $text = "
            <table class='list' {$scrollableTableText} id='bodyList' cellspacing='1'>
                <thead>
                <tr>
                {$rowNumberText}
            ";
        }

        return $text;
    }

     //==================================================================//
    function getListQuotePublishedImage($value, $id) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $publishFromList   = $modulesArr[$tv['module']]['publishFromList'];

        $img            = ($value == 1) ? "published" : "not_published";
        $publishedIcons = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/{$img}.png' title='upload' border='0'>";
        $publishedIcons = $this->getListQuotePublishedImageIcon($tv['module'], $id, $value, $publishFromList);

        $text = "
        <td width='60'>
            <div align='center' id='txt__general_quote__{$id}'>
               {$publishedIcons}
            </div>
        </td>
        ";

        return $text;
    }

    //==================================================================//
    function getListQuotePublishedImageIcon($module, $id, $value, $editable = 1) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $imgReload = "";

        if ($value == 1) {
            $imgSrc = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/published.png' title='upload' border='0'>";
        } else {
            $imgSrc = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/not_published.png' border='0'>";
        }

        if ($editable == 1) {
            $text = "
            <a style='text-decoration:none;'
                href=\"javascript:cpm.hms.product.publishQuoteRecordFromList('{$module}', '{$id}', '{$value}') \">{$imgSrc}
            </a>
            {$imgReload}
            ";
        } else {
            $text =  $imgSrc;
        }

        return $text;
    }
}