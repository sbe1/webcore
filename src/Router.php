<?php
namespace Sbe1\Webcore;

/**
 * A very simple convenience class for routing requests to controllers.
 * 
 * Router file format: simple text file with a route on each line.
 * Example routes (URI '/' is assumed and does not need to be defined):
 * 
controlername
controllername/actionname
controllername/actionname/[param]/
controllername/actionname/[param]/[param]
 * 
 * @author Shawn Ewald <shawn.ewald@gmail.com>
 */
class Router {
    private string $_routefile;
    private array $_routes;
    private array $_path_array;
    private string $_method;
    private string $_default_controller;
    private string $_default_action;
    private string $_controller;
    private string $_action;
    private array $_params = [];

    /**
     * Constructor
     * 
     * @param string $routefile (format: /path/to/routefile.inc)
     * @param string $method - HTTP request method
     * @param string $defaultcontroller
     * @param string $defaultaction
     * 
     * @return self
     */
    public function __construct(string $routefile, string $defaultcontroller=null,
        string $defaultaction=null){
        $this->_default_controller = empty($defaultcontroller) ? 'Home' : $defaultcontroller;
        $this->_default_action = empty($defaultaction) ? 'index' : $defaultaction;
        $tmp = explode('?', $_SERVER['REQUEST_URI'], 2);
        $this->_path_array = array_values( array_filter( explode('/', $tmp[0])  ));
        $this->_method = $_SERVER['REQUEST_METHOD'];
        $this->_routefile = $routefile;
        $contents = file_get_contents($this->_routefile);
        $this->_routes = array_values( array_filter( explode("\n", $contents) ) );
        $this->processURI();
    }

    /**
     * Process request URI and set controller, action and parameters.
     * 
     * @return void
     */
    private function processURI () {
        $plen = count($this->_path_array);
        switch ($plen) {
            case 0:
                $this->_controller = $this->_default_controller;
                $this->_action = $this->_default_action;
                break;
            case 1:
                if (empty($this->_path_array[0])) {
                    $this->_controller = $this->_default_controller;
                    $this->_action = $this->_default_action;
                }
                else {
                    $this->_controller = ucfirst($this->_path_array[0]);
                    $this->_action = $this->_default_action;
                }
                break;
            case 2:
                $this->_controller = $this->_path_array[0];
                $this->_action = $this->_path_array[1];
                break;
            case 3:
                $this->_controller = $this->_path_array[0];
                $this->_action = $this->_path_array[1];
                $this->_params = [$this->_path_array[2]];
                break;
            case 4:
                $this->_controller = $this->_path_array[0];
                $this->_action = $this->_path_array[1];
                $this->_params = [$this->_path_array[2],$this->_path_array[3]];
                break;
        }
    }

    /**
     * Parse the request URI to determine the route.
     * 
     * @return string
     */
    private function getRouteNameFromPath () {
        $pa = $this->_path_array;
        $plen = count($pa);
        $result = null;
        switch ($plen) {
            case 0:
                $result = '/';
                break;
            case 1:
                $result = $pa[0];
                break;
            case 2:
                $result = $pa[0].'/'.$pa[1];
                break;
            case 3:
                $result = $pa[0].'/'.$pa[1].'/[param]/';
                break;
            case 4:
                $result = $pa[0].'/'.$pa[1].'/[param]/[param]';
                break;
        }
        return $result;
    }

    /**
     * Return array of routes.
     * 
     * @return array
     */
    public function getRoutes () {
        return $this->_routes;
    }

    /**
     * Return a route object for use by a controller.
     * 
     * @return object
     */
    public function getRoute () {
        $r = new Route();
        $r->setRoute($this->getRouteNameFromPath());
        $r->setURI('/'.join('/', $this->_path_array));
        $r->setMethod($this->_method);
        $r->setController($this->_controller);
        $r->setAction($this->_action);
        $r->setParams($this->_params);
        return $r;
    }

    /**
     * Return controller name.
     * 
     * @return string
     */
    public function getController () {
        return $this->_controller;
    }

    /**
     * Return action name.
     * 
     * @return string
     */
    public function getAction () {
        return $this->_action;
    }

    /**
     * Return paramters from request URI
     * 
     * @return array
     */
    public function getParams () {
        return $this->_params;
    }

    /**
     * Return test result for test checking that the controller name was set.
     * 
     * 
     */
    public function getRouteStatus () {
        return !empty($this->_controller);
    }
}

class Route {
    private string $_route;
    private string $_uri;
    private string $_method;
    private string $_controller;
    private string $_action;
    private array $_params;

    /**
     * @param string $route
     * 
     * @return void
     */
    public function setRoute (string $route) {
        $this->_route = $route;
    }

    /**
     * 
     * @return string
     */
    public function getRoute () {
        return $this->_route;
    }

    /**
     * @param string $uri
     * 
     * @return void
     */
    public function setURI (string $uri) {
        $this->_uri = $uri;
    }

    /**
     * 
     * @return string
     */
    public function getURI () {
        return $this->_uri;
    }

    /**
     * @param string $method
     * 
     * @return void
     */
    public function setMethod (string $method) {
        $this->_method = $method;
    }

    /**
     * 
     * @return string
     */
    public function getMethod () {
        return $this->_method;
    }

    /**
     * @param string $controller
     * 
     * @return void
     */
    public function setController (string $controller) {
        $this->_controller = $controller;
    }

    /**
     * 
     * @return string
     */
    public function getController () {
        return $this->_controller;
    }

    /**
     * @param string $action
     * 
     * @return void
     */
    public function setAction (string $action) {
        $this->_action = $action;
    }

    /**
     * 
     * @return string
     */
    public function getAction () {
        return $this->_action;
    }

    /**
     * @param array $params
     * 
     * @return void
     */
    public function setParams (array $params) {
        $this->_params = $params;
    }

    /**
     * 
     * @return array
     */
    public function getParams () {
        return $this->_params;
    }
}