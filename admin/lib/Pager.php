<?
class CPL_Admin_Lib_Pager extends CP_Common_Lib_Pager
{
    /**
     *
     */
    var $nextRecordsAvailable = false;
    var $prevRecordsAvailable = false;
    var $searchQueryString;
    var $urlStringOnly;
    var $queryStringOnly;

    var $recordOffset = 0;
    var $numRecordsPerPage;
    var $page;
    var $totalPages;
    var $totalRecords;
    var $startRecordNo;
    var $endRecordNo;
    var $searchDone = 0;

    /**
     *
     * @param <type> $rowCounter
     * @return <type>
     */
    function getGoToEditLink($rowCounter) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $cpCfg['cp.useSEOUrl']    = isset($cpCfg['cp.useSEOUrl'])  ? $cpCfg['cp.useSEOUrl']   : 0;

        $useSEOUrl = 0;
        if ($tv['disableSEOUrlTemporarily'] == 0 && $cpCfg['cp.useSEOUrl'] == 1){
            $useSEOUrl = 1;
        }

        $page        = $rowCounter + $this->startRecordNo;
        $linkString  = str_replace("&_action=list", "", $this->searchQueryString);
        $linkString  = @preg_replace("~&_page=[0-9]+~", "", $linkString);
        $linkString  = @preg_replace("~&currentId=[0-9]+~", "", $linkString);

        if ($useSEOUrl == 1){
           $linkString  = $linkString  . "edit/" . $page . "/";
        } else {
           $linkString  = $linkString  . "&_action=edit&_page=" . $page;
        }

        return $linkString;
    }
}