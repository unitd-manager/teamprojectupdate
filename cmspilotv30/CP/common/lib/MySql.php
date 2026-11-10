<?php
if (!defined('MYSQL_BOTH')) {
    define('MYSQL_BOTH', MYSQLI_BOTH);
}
if (!defined('MYSQL_ASSOC')) {
    define('MYSQL_ASSOC', MYSQLI_ASSOC);
}
if (!defined('MYSQL_NUM')) {
    define('MYSQL_NUM', MYSQLI_NUM);
}

class CP_Common_Lib_MySql
{
    var $db_connect_id = false;
    var $query_result = false;
    var $row = array();
    var $rowset = array();
    var $num_queries = 0;
    var $overallQueryTime = 0;
    var $server;
    var $user;
    var $password;
    var $dbname;
    var $persistency;
    var $last_error = '';
    var $last_errno = 0;

    function __construct($params) {
        $this->server = $params[0];
        $this->user = $params[1];
        $this->password = $params[2];
        $this->dbname = $params[3];
        $this->persistency = isset($params[4]) ? $params[4] : false;

        $host = $this->server;
        if ($this->persistency && strpos($host, 'p:') !== 0) {
            $host = 'p:' . $host;
        }

        try {
            $this->db_connect_id = new mysqli($host, $this->user, $this->password, $this->dbname);
            if ($this->db_connect_id->connect_errno !== 0) {
                $this->last_error = $this->db_connect_id->connect_error;
                $this->last_errno = $this->db_connect_id->connect_errno;
                $this->db_connect_id = false;
            }
        } catch (mysqli_sql_exception $exception) {
            $this->last_error = $exception->getMessage();
            $this->last_errno = $exception->getCode();
            $this->db_connect_id = false;
        }
    }

    function sql_close() {
        if (!$this->db_connect_id) {
            return false;
        }

        if ($this->query_result instanceof mysqli_result) {
            $this->query_result->free();
        }

        $result = $this->db_connect_id->close();
        $this->db_connect_id = false;
        $this->query_result = false;
        return $result;
    }

    function sql_query($query = "", $transaction = false) {
        $start = (float) array_sum(explode(' ', microtime()));
        $this->query_result = false;
        $this->last_error = '';
        $this->last_errno = 0;

        if ($query !== '' && $this->db_connect_id) {
            $this->num_queries++;
            try {
                $this->query_result = $this->db_connect_id->query($query);
            } catch (mysqli_sql_exception $exception) {
                $this->last_error = $exception->getMessage();
                $this->last_errno = $exception->getCode();
            }

            if ($this->query_result === false) {
                if ($this->last_error === '') {
                    $this->last_error = $this->db_connect_id->error;
                    $this->last_errno = $this->db_connect_id->errno;
                }
                $this->sql_query_error_handler($query);
            }
        }

        $end = (float) array_sum(explode(' ', microtime()));
        $this->overallQueryTime += $end - $start;

        return $this->query_result;
    }

    function sql_query_error_handler($query) {
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg[CP_ENV]['display_errors']) {
            print trace();
            print "<hr>" . $this->last_error;
            print "<pre class='sql'><b>Actual Query</b><hr>" . $query . '</pre>';
        } else {
            print "
            <hr>
            Some database errors have occurred.<br /><br />
            Please contact the administrator..
            <hr>
            ";
        }
    }

    function sql_numrows($query_id = 0) {
        if (!$query_id) {
            $query_id = $this->query_result;
        }

        return $query_id instanceof mysqli_result ? $query_id->num_rows : false;
    }

    function sql_affectedrows() {
        return $this->db_connect_id ? $this->db_connect_id->affected_rows : false;
    }

    function sql_numfields($query_id = 0) {
        if (!$query_id) {
            $query_id = $this->query_result;
        }

        return $query_id instanceof mysqli_result ? $query_id->field_count : false;
    }

    function sql_fieldname($offset, $query_id = 0) {
        if (!$query_id) {
            $query_id = $this->query_result;
        }
        if (!($query_id instanceof mysqli_result)) {
            return false;
        }

        $field = $query_id->fetch_field_direct($offset);
        return $field ? $field->name : false;
    }

    function sql_fieldtype($offset, $query_id = 0) {
        if (!$query_id) {
            $query_id = $this->query_result;
        }
        if (!($query_id instanceof mysqli_result)) {
            return false;
        }

        $field = $query_id->fetch_field_direct($offset);
        return $field ? $field->type : false;
    }

    function sql_fetchrow($query_id = 0, $fetchType = MYSQL_BOTH) {
        if (!$query_id) {
            $query_id = $this->query_result;
        }
        if (!($query_id instanceof mysqli_result)) {
            return false;
        }

        $row = $query_id->fetch_array($fetchType);
        $this->row[spl_object_id($query_id)] = $row;
        return $row;
    }

    function sql_fetchrowset($query_id = 0) {
        if (!$query_id) {
            $query_id = $this->query_result;
        }
        if (!($query_id instanceof mysqli_result)) {
            return false;
        }

        $key = spl_object_id($query_id);
        $this->rowset[$key] = array();
        while ($row = $this->sql_fetchrow($query_id)) {
            $this->rowset[$key][] = $row;
        }

        return $this->rowset[$key];
    }

    function sql_fetch_object($query_id = 0) {
        if (!$query_id) {
            $query_id = $this->query_result;
        }

        return $query_id instanceof mysqli_result ? $query_id->fetch_object() : false;
    }

    function sql_fetchfield($field, $rownum = -1, $query_id = 0) {
        if (!$query_id) {
            $query_id = $this->query_result;
        }
        if (!($query_id instanceof mysqli_result)) {
            return false;
        }

        if ($rownum > -1 && !$query_id->data_seek($rownum)) {
            return false;
        }

        $row = $this->sql_fetchrow($query_id);
        return is_array($row) && array_key_exists($field, $row) ? $row[$field] : false;
    }

    function sql_rowseek($rownum, $query_id = 0) {
        if (!$query_id) {
            $query_id = $this->query_result;
        }

        return $query_id instanceof mysqli_result ? $query_id->data_seek($rownum) : false;
    }

    function sql_nextid() {
        return $this->db_connect_id ? $this->db_connect_id->insert_id : false;
    }

    function sql_freeresult($query_id = 0) {
        if (!$query_id) {
            $query_id = $this->query_result;
        }
        if (!($query_id instanceof mysqli_result)) {
            return false;
        }

        $key = spl_object_id($query_id);
        unset($this->row[$key], $this->rowset[$key]);
        $query_id->free();
        return true;
    }

    function sql_error($query_id = 0) {
        return array(
            'message' => $this->last_error,
            'code' => $this->last_errno
        );
    }
}
