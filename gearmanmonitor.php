<?php
class GearmanMonitor
{

  public static $commands = array('status','workers');

  public function __construct($server = 'localhost', $port = 4730)
  {
    $errno = 0;
    $errstr = '';
    $this->fd = fsockopen($server, $port, $errno, $errstr);
    if ( ! $this->fd )
    {
      throw new Exception('Failed to connect to socket: '.$errno.' ('.$errstr.')');
    }
  }

  public function __destruct()
  {
    fclose($this->fd);
  }

  /**
   * handle the command and return the response
   * @param string $cmd
   * @return array $response
   */
  public function cmd($cmd)
  {
    $response = array();
    if (in_array($cmd, static::$commands)) {
      fwrite($this->fd, "{$cmd}\n");
      while(!feof($this->fd)) {
        $tmp = trim(fgets($this->fd,4096));
        // lonely dot means done.
        if ($tmp === "."){
          break;
        } else if (!empty($tmp)) {
          $response[] = static::parseResponse($cmd, $tmp);
        }
      }
    } else if ( $cmd == 'help' ) {
      $response[] = "commands: ".implode(', ', static::$commands);


    } else {
      echo "unknown command!{$cmd}\n";
    }
    return $response;
  }


  /**
   * @param string $cmd
   * @param string $response
   * @return array
   */
  public static function parseResponse($cmd, $response)
  {
    if ($cmd === 'workers') {
      return static::parseWorkers($response);
    } else if ($cmd === 'status') {
      return static::parseStatus($response);
    }
  }

  /**
   * parse the workers response into an $data array
   * @param string $response from gearman
   * @return array
   */
  public static function parseWorkers($response)
  {
    $data = array('jobs' => array());
    $response = explode(" ",$response);
    $data['worker_id'] = array_shift($response);
    $data['host']      = array_shift($response);
    $_ = array_shift($response); $_ = array_shift($response); // remove the two separators(?)
    foreach($response as $job)
    {
      $data['jobs'][] = $job;
    }
    return $data;
  }

  /**
   * parse the status response into an $data array
   * @param string $response from gearman
   * @return array
   */
  public static function parseStatus($response)
  {
    $data = array('job' => '', 'total' => 0, 'running' => 0, 'available' => 0);
    $response = explode("\t",$response);
    foreach(array_keys($data) as $key) {
      $data[$key] = array_shift($response);
    }
    return $data;
  }

  /**
   * @todo make this nicer
   * echo the response in a readable format
   * @param array $response from gearman
   * @param string $cmd the command executed
   * @return void
   */
  public static function showResponse($response, $cmd)
  {
    if ($cmd == 'status')
    {
      echo "total : running: available : job\n";
      foreach($response as $row)
      {
        echo "{$row['total']} : {$row['running']} : {$row['available']} : {$row['job']} \n";
      }
    }
    else if ($cmd == 'workers')
    {
      echo "host : jobs\n";
      foreach($response as $row)
      {
        echo "{$row['host']} : ".implode(', ',$row['jobs'])."\n";
      }
    }
    else if ($cmd == 'help')
    {
      foreach($response as $row)
      {
        echo $row."\n";
      }
    }
    else
    {
      var_dump($response);
    }
  }

}
