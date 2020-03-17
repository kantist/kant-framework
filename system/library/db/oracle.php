<?php
  define('ORA_CHARSET_DEFAULT', 'SPANISH_SPAIN.AL32UTF8');
  define('ORA_CONNECTION_TYPE_DEFAULT', 1);
  define('ORA_CONNECTION_TYPE_PERSISTENT', 2);
  define('ORA_CONNECTION_TYPE_NEW', 3);
  define('ORA_MESSAGES_NOT_CONNECTED', 'Not connected to Oracle instance');
  
  class ORACLE {
    private static $_instance;
    private $conn_handle;
    private $conn_data;
    private $errors_pool;
    private $statements = array();
    private $autocommit = false;
    private $fetch_mode = OCI_BOTH;
    private $last_query;
    private $var_max_size = 1000;
    private $execute_status = false;
    private $charset;
    private $session_mode = OCI_DEFAULT;

    public function SetFetchMode($mode = OCI_BOTH){
        $this->fetch_mode = $mode;
    }

    public function SetAutoCommit($mode = true){
        $this->autocommit = $mode;
    }

    public function SetVarMaxSize($size){
        $this->var_max_size = $size;
    }

    public function GetError(){
        return @oci_error($this->conn_handle);
    }    

    public function SetNlsLang($charset = ORA_CHARSET_DEFAULT){
        $this->charset = $charset;
    }

    public function __construct(){
        $this->SetNlsLang('CL8MSWIN1251');
        $this->SetFetchMode(OCI_ASSOC);
        $this->SetAutoCommit(false);
    }

    public function Connect($host = 'localhost', $user='', $pass='', $mode = OCI_DEFAULT, $type = ORA_CONNECTION_TYPE_DEFAULT){
      switch ($type) {
          case ORA_CONNECTION_TYPE_PERSISTENT: {
              $this->conn_handle = oci_pconnect($user, $pass, $host, $this->charset, $mode);
          }; break;
          case ORA_CONNECTION_TYPE_NEW: {
              $this->conn_handle = oci_new_connect($user, $pass, $host, $this->charset, $mode);
          }; break;
          default: 
              $this->conn_handle = oci_connect($user, $pass, $host, $this->charset, $mode);
      }        
      return is_resource($this->conn_handle) ? true : false;
    }

    public function __destruct(){
      if (is_resource($this->conn_handle)) {
        @oci_close($this->conn_handle);
      }
    }

    public function GetExecuteStatus(){
        return $this->execute_status;
    }
    
    private function GetBindingType($var){
        if (is_a($var, "OCI-Collection")) {
          $bind_type = SQLT_NTY;
          $this->SetVarMaxSize(-1);
        } elseif (is_a($var, "OCI-Lob")) {
          $bind_type = SQLT_CLOB;
          $this->SetVarMaxSize(-1);
        } else {
          $bind_type = SQLT_CHR;
        }
        return $bind_type;
    }

    private function Execute($sql_text, &$bind = false){
        if (!is_resource($this->conn_handle)) return false;
        $this->last_query = $sql_text;
        
        $stid = @oci_parse($this->conn_handle, $sql_text);
        
        $this->statements[$stid]['text'] = $sql_text;
        $this->statements[$stid]['bind'] = $bind;
        
        if ($bind && is_array($bind)) {
            foreach($bind as $k=>$v){
                oci_bind_by_name($stid, $k, $bind[$k], $this->var_max_size, $this->GetBindingType($bind[$k]));
            }
        }
        $com_mode = $this->autocommit ? OCI_COMMIT_ON_SUCCESS : OCI_DEFAULT;
        $this->execute_status = oci_execute($stid, $com_mode);
        return $this->execute_status ? $stid : false;
    }

    public function Select($sql, $bind = false){
        return $this->Execute($sql, $bind);
    }

    public function FetchArray($statement){
        return oci_fetch_array($statement, $this->fetch_mode);
    }

    public function FetchRow($statement){
        return oci_fetch_row($statement);
    }

    public function FetchAll($statement, $skip = 0, $maxrows = -1){
        $rows = array();
        oci_fetch_all($statement, $rows, $skip, $maxrows, OCI_FETCHSTATEMENT_BY_ROW);
        return $rows;
    }

    public function FetchObject($statement){
        return oci_fetch_object($statement);
    }

    public function Fetch($statement){
        return oci_fetch($statement);
    }

    public function Result($statement, $field){
        return oci_result($statement, $field);
    }

    public function DefineByName($statement , $column_name , &$variable, $type = SQLT_CHR){
        return oci_define_by_name($statement, $column_name, $variable, $type);
    }
    
    public function FieldIsNull($statement, $field){
        return oci_field_is_null($statement, $field);
    }
    
    public function FieldName($statement, int $field){
        return oci_field_name($statement, $field);
    }
    
    public function FieldPrecition($statement, int $field){
        return oci_field_precision($statement, $field);
    }
    
    public function FieldScale($statement, int $field){
        return oci_field_scale($statement, $field);
    }
    
    public function FieldSize($statement, $field){
        return oci_field_size($statement, $field);
    }
    
    public function FieldTypeRaw($statement, int $field){
        return oci_field_type_raw($statement, $field);
    }
    
    public function FieldType($statement, int $field){
        return oci_field_type($statement, $field);
    }       

    public function Insert($table, $arrayFieldsValues, &$bind = false, $returning = false){
        if (empty($arrayFieldsValues)) return false;
        $fields = array();
        $values = array();
        foreach($arrayFieldsValues as $f=>$v){
            $fields[] = $f;
            $values[] = $v;
        }
        $fields = implode(",", $fields);
        $values = implode(",", $values);
        $ret = "";
        if ($returning) {
            foreach($returning as $f=>$h){
                $ret_fields[] = $f;
                $ret_binds[] = ":$h";
                $bind[":$h"] = "";
            }
            $ret = " returning ".(implode(",", $ret_fields))." into ".(implode(",",$ret_binds));
        }
        $sql = "insert into $table ($fields) values($values) $ret";
        $result = $this->Execute($sql, $bind);
        if ($result === false) return false;
        if ($returning === false) {
            return $result;
        } else {
            $result = array();
            foreach($returning as $f=>$h){
                $result[$f] = $bind[":$h"];
            }
            return $result;
        }
    }

    public function Update($table, $arrayFieldsValues, $condition = false, &$bind = false, $returning = false){
        if (empty($arrayFieldsValues)) return false;
        $fields = array();
        $values = array();
        foreach($arrayFieldsValues as $f=>$v){
            $fields[] = "$f = $v";
        }
        $fields = implode(",", $fields);
        if ($condition === false) { $condition = "true";}
        $ret = "";
        if ($returning) {
            foreach($returning as $f=>$h){
                $ret_fields[] = $f;
                $ret_binds[] = ":$h";
                $bind[":$h"] = "";
            }
            $ret = " returning ".(implode(",", $ret_fields))." into ".(implode(",",$ret_binds));
        }
        $sql = "update $table set $fields where $condition $ret";
        $result = $this->Execute($sql, $bind);        
        if ($result === false) return false;
        if ($returning === false) {
            return $result;
        } else {
            $result = array();
            foreach($returning as $f=>$h){
                $result[$f] = $bind[":$h"];
            }
            return $result;
        }
    }

    public function Delete($table, $condition, &$bind = false, $returning = false){
        if ($condition === false) { $condition = "true";}
        $ret = "";
        if ($returning) {
            foreach($returning as $f=>$h){
                $ret_fields[] = $f;
                $ret_binds[] = ":$h";
                $bind[":$h"] = "";
            }
            $ret = " returning ".(implode(",", $ret_fields))." into ".(implode(",",$ret_binds));
        }
        $sql = "delete from $table where $condition $ret";
        $result = $this->Execute($sql, $bind);
        if ($result === false) return false;
        if ($returning === false) {
            return $result;
        } else {
            $result = array();
            foreach($returning as $f=>$h){
                $result[$f] = $bind[":$h"];
            }
            return $result;
        }
    }

    public function NumRows($statement){
        return oci_num_rows($statement);
    }

    public function RowsAffected($statement){
        return $this->NumRows($statement);
    }

    public function NumFields($statement){
        return oci_num_fields($statement);
    }

    public function FieldsCount($statement){
        return $this->NumFields($statement);
    }

    public function NewDescriptor($type = OCI_DTYPE_LOB){
        return oci_new_descriptor($this->conn_handle, $type);
    }

    public function NewCollection($typename, $schema = null){
        return oci_new_collection($this->conn_handle, $typename, $schema);
    }   

    public function StoredProc($name, $params = false, &$bind = false){
        if ($params) {
          if (is_array($params)) $params = implode(",", $params);
          $sql = "begin $name($params); end;";  
        } else {
          $sql = "begin $name; end;";
        }
        return $this->Execute($sql, $bind);
    }

    public function Func($name, $params = false, $bind = false){
        if ($params) {
          if (is_array($params)) $params = implode(",", $params);
          $sql = "select $name($params) as RESULT from dual";  
        } else {
          $sql = "select $name from dual";
        }
        $h = $this->Execute($sql, $bind);
        $r = $this->FetchArray($h);
        return $r['RESULT'];
    }

    public function Cursor($stored_proc, $bind){
        if (!is_resource($this->conn_handle)) return false;
        $sql = "begin $stored_proc(:$bind); end;";
        $curs = oci_new_cursor($this->conn_handle);
        $stmt = oci_parse($this->conn_handle, $sql);
        oci_bind_by_name($stmt, $bind, $curs, -1, OCI_B_CURSOR);
        oci_execute($stmt);
        oci_execute($curs);
        $this->FreeStatement($stmt);
        return $curs;
    }

    public function Cancel($statement){
        return oci_cancel($statement);
    }

    public function FreeStatement($stid){
        unset($this->statements[$stid]);
        return oci_free_statement($stid);
    }

    public function FreeStatements($array_stid){
        if (is_array($array_stid)) foreach($array_stid as $stid) {
          unset($this->statements[$stid]);
          oci_free_statement($stid);
        }
        return true;
    }

    public function Commit(){
        if (is_resource($this->conn_handle))
          return @oci_commit($this->conn_handle);
        else 
          return false;
    }  

    public function Rollback(){
        if (is_resource($this->conn_handle))
          return @oci_rollback($this->conn_handle);
        else 
          return false;
    }  

    public function InternalDebug($mode){
        oci_internal_debug($mode);
    }
    
    
    public function GetStatement($stid){
        return $this->statements[$stid] ? $this->statements[$stid] : false;
    }

    public function QuerySnapshot($stid = false){
        if ($stid) return $this->statements[$stid]['text']; else return $this->last_query;
    }

    public function ServerVer(){
        if (is_resource($this->conn_handle))
          return @oci_server_version($this->conn_handle);
        else 
          return false;
    }
    
    public function SetAction(string $action_name){
        return @oci_set_action($this->conn_handle, $action_name);
    }
    
    public function SetClientID(string $client_id){
        return @oci_set_client_identifier($this->conn_handle, $client_id);
    }
    
    public function SetClientInfo(string $client_info){
        return @oci_set_client_info($this->conn_handle, $client_info);
    }
    
    public function SepPrefetch(int $rows){
        return oci_set_prefetch($this->conn_handle, $rows);
    }

    public function StatementType($statement){
        return oci_statement_type($statement);
    }
    
    public function DumpQueriesStack(){
        var_dump($this->statements);
    }
    
    public function Bye(){
        $this->__destruct();
    } 
    
    public function get_handle(){
        return $this->conn_handle;
    }   
  }
?>