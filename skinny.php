<?php

class skinnyApp {
  private $get_requests = array();
  private $post_requests = array();

  public function get($route, $action) {
    $this->get_requests[] = array('route' => $route, 'action' => $action);
  }

  public function post($route, $action) {
    $this->post_requests[] = array('route' => $route, 'action' => $action);
  }

  public function not_found() {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    header("Status: 404 Not Found");
    echo "Not Found";
  }

  public function render() {
    $location = preg_replace('/\/index\.php$/', '', $_SERVER['SCRIPT_NAME']);
    $location = str_replace('/', '\/', $location);
    $url = preg_replace("/^$location/", '', $_SERVER['REQUEST_URI']);
    if (strpos($url, '?') !== false) {
      $url = substr($url, 0, strpos($url, '?'));
    }

    $requests = ($_SERVER['REQUEST_METHOD'] === 'GET') ? $this->get_requests : $this->post_requests;

    foreach($requests as $request) {
      $route = $request['route'];
      $route = str_replace('/', '\/', $route);
      $route = '^' . $route . '\/?$';

      if (preg_match("/$route/i", $url, $matches)) {
        // remove full match
        array_shift($matches);

        $action = $request['action'];
        $return = call_user_func_array($action, $matches);

        if (isset($return)) {

          // return from route is outputted as json, with an optional callback
          $return = json_encode($return);
          if (isset($_GET['callback'])) {
            $content_type = "application/javascript";
            $return = "{$_GET['callback']}($return);";
          }
          else {
            $content_type = "application/json";
          }
          header("Content-Type: $content_type");
          echo $return;
        }
        exit;
      }
    }

    $this->not_found();
  }
}