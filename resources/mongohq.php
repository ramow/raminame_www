<?php

class MongoHQ {
  private $m;
  private $db;
  private $config = array(
    'username' => 'rami',
    'password' => 'rami.name',
    'host' => 'alex.mongohq.com', 'port' => '10091',
    //'host' => 'localhost', 'port' => '27017',
    'dbName' => 'Movies',
    'collectionName' => 'watched'
   );

  public function buildUrl () {
    $url = "mongodb://" . $this->config['username'] . ":" . $this->config['password'] . "@" . $this->config['host'] . ":" . $this->config['port'] . "/" . $this->config['dbName'];
    //$url = "mongodb://" . $this->config['host'] . ":" . $this->config['port'] . "/" . $this->config['dbName'];
    //echo $url;
    return $url;
  }
  
  function __construct($config) {
    if($config == null)
      $config = $this->config;
    foreach($config as $key => $val)
      if ($val != null)
        $this->config[$key] = $val;
    $this->m = new Mongo($this->buildUrl());
    $this->db = $this->m->selectDB($this->config['dbName']);
  }
  
  public function setCollectionName($collectionName) {
    $this->config['collectionName'] = $collectionName;
  }

  private function getCollection($collection=null) {
    if ($collection==null)
      $collection = $this->config['collectionName'];
    return $this->db->selectCollection($collection);
  }
  
  public function findOne($query=null, $collection=null) {
    $results = $this->find($query, $collection, 1);
    $one = null;
    if ($results != null && count($results) > 0)
      $one = $results[0];
    return $one;
  }
  
  public function aggregate($pipeline) {
    $results = array();
    try {
      $collection = $this->getCollection($collection);
      $cursor = $collection->aggregate($pipeline);
      foreach ($cursor as $obj)
        $results[] = $obj;
    } catch (Exception $e) {
      var_dump($e);
    }
    return $results;
  }
  
  public function find($query=null, $limit=-1, $skip=-1, $collection=null) {
    $results = array();
    if($query == null)
      $query = array();
    if (empty($limit))
      $limit = -1;
    if (empty($skip))
      $skip = -1;
    try {
      $time_start1 = microtime(true);
      $collection = $this->getCollection($collection);
      $cursor = $collection->find($query);
      
      if ($limit != null && $limit != -1)
        $cursor = $cursor->limit($limit);
      if ($skip != null && $skip != -1)
        $cursor = $cursor->skip($skip);
      $cursor->batchSize(4000);
      //foreach ($cursor as $obj) $results[] = $obj;
      $results = iterator_to_array($cursor, false);
      $time_start4 = microtime(true);
      $totalTime = $time_start4 - $time_start1;
      //print "find: {$totalTime}<br/>";
    } catch (Exception $e) {
      var_dump($e);
    }
    return $results;
  }

  public function all() {
    return $this->find();
  }
  
  public function count() {
    return $this->getCollection($collection)->count();
  }
  
  public function max($field, $collection=null) {
    $max = null;
    try {
      $project = array();
      $project[$field] = 1;
      $sort = array();
      $sort[$field] = -1;
      $cursor = $this->getCollection($collection)->find(array(), $project)->sort($sort)->limit(1);
      foreach ($cursor as $obj)
        $results[] = $obj;
      if (!empty($results) && isset($results[0][$field]))
        $max = $results[0][$field];
    } catch (Exception $e) {
      var_dump($e);
    }
    return $max;
  }
  
  public function save($row, $collection=null) {
    $this->saveMany(array($row), $collection);
  }
  
  public function saveMany($rows, $collection=null) {
    $collection = $this->getCollection($collection);
    foreach ($rows as $row) {
      try {
        $collection->save($row);
      } catch (Exception $e) {
        var_dump($e);
      }
    }
  }
}
/*
$mongoHq = new MongoHQ(array('collectionName'=>'watched'));
$results = $mongoHq->all();
$count = count($results);
print "count: {$count}";
foreach ($results as $movie) {
  echo "<p>movie title: " . $movie['title'] . "</p>";
}
*/
?>