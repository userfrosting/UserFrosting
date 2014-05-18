<?php
//===============================================================
// Controller
// Parses the HTTP request and routes to the appropriate function
//===============================================================
abstract class UF_Controller {
  protected $controller_path='../app/controllers/'; //with trailing slash
  protected $web_folder='/'; //with trailing slash
  protected $request_uri_parts=array();
  protected $controller;
  protected $action;
  protected $params=array();

  function __construct($controller_path,$web_folder,$default_controller,$default_action)  {
    $this->controller_path=$controller_path;
    $this->web_folder=$web_folder;
    $this->controller=$default_controller;
    $this->action=$default_action;
    $this->explode_http_request()->parse_http_request()->route_request();
  }

  function explode_http_request() {
    $requri = $_SERVER['REQUEST_URI'];
    if (strpos($requri,$this->web_folder)===0)
      $requri=substr($requri,strlen($this->web_folder));
    $this->request_uri_parts = $requri ? explode('/',$requri) : array();
    return $this;
  }

  //This function parses the HTTP request to get the controller name, action name and parameter array.
  function parse_http_request() {
    $this->params = array();
    $p = $this->request_uri_parts;
    if (isset($p[0]) && $p[0] && $p[0][0]!='?')
      $this->controller=$p[0];
    if (isset($p[1]) && $p[1] && $p[1][0]!='?')
      $this->action=$p[1];
    if (isset($p[2]))
      $this->params=array_slice($p,2);
    return $this;
  }

  //This function maps the controller name and action name to the file location of the .php file to include
  function route_request() {
    $controllerfile=$this->controller_path.$this->controller.'/'.$this->action.'.php';
    if (!preg_match('#^[A-Za-z0-9_-]+$#',$this->controller) || !file_exists($controllerfile))
      $this->request_not_found('Controller file not found: '.$controllerfile);
    $function='_'.$this->action;
    if (!preg_match('#^[A-Za-z_][A-Za-z0-9_-]*$#',$function) || function_exists($function))
      $this->request_not_found('Invalid function name: '.$function);
    require($controllerfile);
    if (!function_exists($function))
      $this->request_not_found('Function not found: '.$function);
    call_user_func_array($function,$this->params);
    return $this;
  }

  //Override this function for your own custom 404 page
  function request_not_found($msg='') {
    header("HTTP/1.0 404 Not Found");
    die('<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>'.$msg.'<p>The requested URL was not found on this server.</p><p>Please go <a href="javascript: history.back(1)">back</a> and try again.</p><hr /><p>Powered By: <a href="http://kissmvc.com">KISSMVC</a></p></body></html>');
  }
}

//===============================================================
// View
// For plain .php templates
//===============================================================
abstract class UF_View {
  protected $file='';
  protected $vars=array();

  function __construct($file='',$vars='')  {
    if ($file)
      $this->file = $file;
    if (is_array($vars))
      $this->vars=$vars;
    return $this;
  }

  function __set($key,$var) {
    return $this->set($key,$var);
  }

  function set($key,$var) {
    $this->vars[$key]=$var;
    return $this;
  }

  //for adding to an array
  function add($key,$var) {
    $this->vars[$key][]=$var;
  }

  function fetch($vars='') {
    if (is_array($vars))
      $this->vars=array_merge($this->vars,$vars);
    extract($this->vars);
    ob_start();
    require($this->file);
    return ob_get_clean();
  }

  function dump($vars='') {
    if (is_array($vars))
      $this->vars=array_merge($this->vars,$vars);
    extract($this->vars);
    require($this->file);
  }

  static function do_fetch($file='',$vars='') {
    if (is_array($vars))
      extract($vars);
    ob_start();
    require($file);
    return ob_get_clean();
  }

  static function do_dump($file='',$vars='') {
    if (is_array($vars))
      extract($vars);
    require($file);
  }

  static function do_fetch_str($str,$vars='') {
    if (is_array($vars))
      extract($vars);
    ob_start();
    eval('?>'.$str);
    return ob_get_clean();
  }

  static function do_dump_str($str,$vars='') {
    if (is_array($vars))
      extract($vars);
    eval('?>'.$str);
  }
}

//===============================================================
// Model/ORM
// Requires a function getdbh() which will return a PDO handler
/*
function getdbh() {
  if (!isset($GLOBALS['dbh']))
    try {
      //$GLOBALS['dbh'] = new PDO('sqlite:'.APP_PATH.'db/dbname.sqlite');
      $GLOBALS['dbh'] = new PDO('mysql:host=localhost;dbname=dbname', 'username', 'password');
    } catch (PDOException $e) {
      die('Connection failed: '.$e->getMessage());
    }
  return $GLOBALS['dbh'];
}
*/
//===============================================================
abstract class UF_Model  {

  protected $pkname;
  protected $tablename;
  protected $dbhfnname;
  protected $QUOTE_STYLE='MYSQL'; // valid types are MYSQL,MSSQL,ANSI
  protected $COMPRESS_ARRAY=true;
  public $rs = array(); // for holding all object property variables

  function __construct($pkname='',$tablename='',$dbhfnname='getdbh',$quote_style='MYSQL',$compress_array=true) {
    $this->pkname=$pkname; //Name of auto-incremented Primary Key
    $this->tablename=$tablename; //Corresponding table in database
    $this->dbhfnname=$dbhfnname; //dbh function name
    $this->QUOTE_STYLE=$quote_style;
    $this->COMPRESS_ARRAY=$compress_array;
  }

  function get($key) {
    return $this->rs[$key];
  }

  function set($key, $val) {
    if (isset($this->rs[$key]))
      $this->rs[$key] = $val;
    return $this;
  }

  function __get($key) {
    return $this->get($key);
  }

  function __set($key, $val) {
    return $this->set($key,$val);
  }

  protected function getdbh() {
    return call_user_func($this->dbhfnname);
  }

  protected function enquote($name) {
    if ($this->QUOTE_STYLE=='MYSQL')
      return '`'.$name.'`';
    elseif ($this->QUOTE_STYLE=='MSSQL')
      return '['.$name.']';
    else
      return '"'.$name.'"';
  }

  //Inserts record into database with a new auto-incremented primary key
  //If the primary key is empty, then the PK column should have been set to auto increment
  function create() {
    $dbh=$this->getdbh();
    $pkname=$this->pkname;
    $s1=$s2='';
    foreach ($this->rs as $k => $v)
      if ($k!=$pkname || $v) {
        $s1 .= ','.$this->enquote($k);
        $s2 .= ',?';
      }
    $sql = 'INSERT INTO '.$this->enquote($this->tablename).' ('.substr($s1,1).') VALUES ('.substr($s2,1).')';
    $stmt = $dbh->prepare($sql);
    $i=0;
    foreach ($this->rs as $k => $v)
      if ($k!=$pkname || $v)
        $stmt->bindValue(++$i,is_scalar($v) ? $v : ($this->COMPRESS_ARRAY ? gzdeflate(serialize($v)) : serialize($v)) );
    $stmt->execute();
    if (!$stmt->rowCount())
      return false;
    $this->set($pkname,$dbh->lastInsertId());
    return $this;
  }

  function retrieve($pkvalue) {
    $dbh=$this->getdbh();
    $sql = 'SELECT * FROM '.$this->enquote($this->tablename).' WHERE '.$this->enquote($this->pkname).'=?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1,(int)$pkvalue);
    $stmt->execute();
    $rs = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($rs)
      foreach ($rs as $key => $val)
        if (isset($this->rs[$key]))
          $this->rs[$key] = is_scalar($this->rs[$key]) ? $val : unserialize($this->COMPRESS_ARRAY ? gzinflate($val) : $val);
    return $this;
  }

  function update() {
    $dbh=$this->getdbh();
    $s='';
    foreach ($this->rs as $k => $v)
      $s .= ','.$this->enquote($k).'=?';
    $s = substr($s,1);
    $sql = 'UPDATE '.$this->enquote($this->tablename).' SET '.$s.' WHERE '.$this->enquote($this->pkname).'=?';
    $stmt = $dbh->prepare($sql);
    $i=0;
    foreach ($this->rs as $k => $v)
      $stmt->bindValue(++$i,is_scalar($v) ? $v : ($this->COMPRESS_ARRAY ? gzdeflate(serialize($v)) : serialize($v)) );
    $stmt->bindValue(++$i,$this->rs[$this->pkname]);
    return $stmt->execute();
  }

  function delete() {
    $dbh=$this->getdbh();
    $sql = 'DELETE FROM '.$this->enquote($this->tablename).' WHERE '.$this->enquote($this->pkname).'=?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1,$this->rs[$this->pkname]);
    return $stmt->execute();
  }

  //returns true if primary key is a positive integer
  //if checkdb is set to true, this function will return true if there exists such a record in the database
  function exists($checkdb=false) {
    if ((int)$this->rs[$this->pkname] < 1)
      return false;
    if (!$checkdb)
      return true;
    $dbh=$this->getdbh();
    $sql = 'SELECT 1 FROM '.$this->enquote($this->tablename).' WHERE '.$this->enquote($this->pkname)."='".$this->rs[$this->pkname]."'";
    $result = $dbh->query($sql)->fetchAll();
    return count($result);
  }

  function merge($arr) {
    if (!is_array($arr))
      return $this;
    foreach ($arr as $key => $val)
      if (isset($this->rs[$key]))
        $this->rs[$key] = $val;
    return $this;
  }

  function retrieve_one($wherewhat='',$bindings='') {
    $dbh=$this->getdbh();
    if (is_scalar($bindings))
      $bindings= trim($bindings) ? array($bindings) : array();
    $sql = 'SELECT * FROM '.$this->enquote($this->tablename);
    if ($wherewhat)
      $sql .= ' WHERE '.$wherewhat;
    $sql .= ' LIMIT 1';
    $stmt = $dbh->prepare($sql);
    $i=0;
    foreach($bindings as $v)
      $stmt->bindValue(++$i,$v);
    $stmt->execute();
    $rs = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$rs)
      return false;
    foreach ($rs as $key => $val)
      if (isset($this->rs[$key]))
        $this->rs[$key] = is_scalar($this->rs[$key]) ? $val : unserialize($this->COMPRESS_ARRAY ? gzinflate($val) : $val);
    return $this;
  }

  function retrieve_many($wherewhat='',$bindings='') {
    $dbh=$this->getdbh();
    if (is_scalar($bindings))
      $bindings=trim($bindings) ? array($bindings) : array();
    $sql = 'SELECT * FROM '.$this->tablename;
    if ($wherewhat)
      $sql .= ' WHERE '.$wherewhat;
    $stmt = $dbh->prepare($sql);
    $i=0;
    foreach($bindings as $v)
      $stmt->bindValue(++$i,$v);
    $stmt->execute();
    $arr=array();
    $class=get_class($this);
    while ($rs = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $myclass = new $class();
      foreach ($rs as $key => $val)
        if (isset($myclass->rs[$key]))
          $myclass->rs[$key] = is_scalar($myclass->rs[$key]) ? $val : unserialize($this->COMPRESS_ARRAY ? gzinflate($val) : $val);
      $arr[]=$myclass;
    }
    return $arr;
  }

  function select($selectwhat='*',$wherewhat='',$bindings='',$pdo_fetch_mode=PDO::FETCH_ASSOC) {
    $dbh=$this->getdbh();
    if (is_scalar($bindings))
      $bindings=trim($bindings) ? array($bindings) : array();
    $sql = 'SELECT '.$selectwhat.' FROM '.$this->tablename;
    if ($wherewhat)
      $sql .= ' WHERE '.$wherewhat;
    $stmt = $dbh->prepare($sql);
    $i=0;
    foreach($bindings as $v)
      $stmt->bindValue(++$i,$v);
    $stmt->execute();
    return $stmt->fetchAll($pdo_fetch_mode);
  }
}