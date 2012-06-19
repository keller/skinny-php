<?php

class skinnyApp {
  private $get_routes = array();
  private $post_routes = array();

  public function get($path, $action, $version = null) {
    $route = array('path' => $path, 'action' => $action);
    if (isset($version)) {
      $route['version'] = $version;
    }
    $this->get_routes[] = $route;
  }

  public function post($path, $action, $version = null) {
    $route = array('path' => $path, 'action' => $action);
    if (isset($version)) {
      $route['version'] = $version;
    }
    $this->post_routes[] = $route;
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
    // get api version
    if (preg_match('/^\/(\d+)\//', $url, $matches)) {
      $url = preg_replace('/^\/'.$matches[1].'/', '', $url);
      $api_version = (int) $matches[1];
    }

    $routes = ($_SERVER['REQUEST_METHOD'] === 'GET') ? $this->get_routes : $this->post_routes;
    // filter out any routes for other versions
    if (isset($api_version)) {
      $routes = array_filter($routes, function($route) use ($api_version) {
        if (isset($route['version'])) {
          if (is_array($route['version'])) {
            return (in_array($api_version, $route['version']));
          }
          else {
            return ($route['version'] === $api_version);
          }
        }
        else {
          return true;
        }
      });
    }

    foreach($routes as $route) {
      $path = $route['path'];
      $path = str_replace('/', '\/', $path);
      $path = '^' . $path . '\/?$';

      if (preg_match("/$path/i", $url, $matches)) {
        // remove full match
        array_shift($matches);

        $action = $route['action'];
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