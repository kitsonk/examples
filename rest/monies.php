<?php

/**
 * Declares and executes a RESTful processor for monies
 * 
 * @author kitson.kelly (at) bskyb.com
 */

date_default_timezone_set('Europe/London');

require_once('../includes/logging.inc.php');
require_once('../includes/db.inc.php');
require_once('../includes/restful.inc.php');

$thisfile = basename(__FILE__);
$applog->FileName = '../logs/restful';
$applog->LogLevelFile = \kpk\core\logging\Log::EVENT_DEBUG;
$applog->FileEnabled = true;
$applog->ConsoleEnabled = false;

/* Setting Default Code if Processor Doesn't Work */
$responseCode = 500;
$responseData = '';
$responseType = 'text/html';

class incidentProcessor extends \kpk\core\RESTful\processor {
  private $db;
  private $moniesId;
  
  function __construct() {
    parent::__construct();
    $this->db = new \kpk\core\db\Database('../db/monies.sqlite',true,true);
  }
  
  private function getMonies(){
    $this->moniesId = intval($this->request->path[0]);
    if ($this->request->pathCount > 1) { //Trying to GET something specific
      $this->responseCode = 501;
      $this->sendResponse();
      return true;
    } else {
      $results = $this->db->retrieveColumns('monies', array('id','fname','lname','bday','banksort','bankacct','amount'),'id = '.$this->moniesId);
      if (count($results)) {
        $this->responseCode = 200;
        $this->sendResults($results[0]);
      } else {
        $this->responseCode = 404;
        $this->sendResponse(); 
      }
      return true;
    }
  }
  
  protected function get() {
    if ($this->request->pathCount) { //Trying to GET a specific record
      $this->getMonies();
      return true;
    } else {  //Trying to GET all records
      if ($this->request->rangeCount) {
        if ($this->request->rangeOffset) {
          $range = $this->request->rangeCount.' OFFSET '.$this->request->rangeOffset;
        } else {
          $range = $this->request->rangeCount;
        }
        $results = $this->db->retrieveColumns('monies', array('id','fname','lname','bday','banksort','bankacct','amount'),$this->getFilter(),'','id ASC',$range);
        $count = $this->db->retrieveValue('monies','count(*)');
        $this->setResponseRange($this->request->rangeOffset,count($results),$count);
      } else {
        $results = $this->db->retrieveColumns('monies', array('id','fname','lname','bday','banksort','bankacct','amount'),$this->getFilter(),'','id ASC');
      }
      foreach ($results as &$result) {
      }
      $this->responseCode = 200;
      $this->sendResults($results);
      return true;
    }
  }
  
  protected function put() {
    $this->responseCode = 501;  //Not Implemented
    $this->sendResponse();
    return true;
  }
  
  protected function post() {
    if ($this->request->pathCount) { //Trying to POST a specific record
      $this->responseCode = 501;
      $this->sendResponse();
      return true;
    } else { //Trying to POST a record
      $this->db->prepareInsert('monies', array('fname','lname','bday','banksort','bankacct','amount'));
      $this->moniesId = $this->db->insertPrepared('monies', $this->request->data, true, true);
      $this->db->commit();
      $this->responseCode = 302;
      $this->responseLocation = kpk\core\RESTful\getLocation('/'.$this->moniesId);
      $this->sendResponse();
      return true;
    }
  }
  
  protected function delete() {
    $this->responseCode = 501;  //Not Implemented
    $this->sendResponse();
    return true;
  }
  
  protected function options() {
    $this->responseCode = 501;  //Not Implemented
    $this->sendResponse();
    return true;
  }
  
}

$incidentProcessor = new incidentProcessor();

if ($incidentProcessor->process()) {
  exit;  
} else {
  $applog->logEvent($thisfile,'Processor did not properly handle request',\kpk\core\logging\Log::EVENT_ERROR);
  \kpk\core\RESTful\sendResponse($responseCode,$responseData,$responseType);
}

?>