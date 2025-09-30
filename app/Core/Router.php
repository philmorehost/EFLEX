<?php
namespace Core;

/**
 * Router Class
 * Parses the URL and dispatches the request to the correct controller and method.
 * URL format: /controller/method/param1/param2/...
 */
class Router {
    protected $currentController = 'Pages';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->getUrl();

        // Look for the controller in the app/Controllers folder
        $controllerName = isset($url[0]) ? ucwords($url[0]) : $this->currentController;
        $controllerFile = 'app/Controllers/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            $this->currentController = $controllerName;
            unset($url[0]);
        }

        // Require the controller
        require_once 'app/Controllers/' . $this->currentController . '.php';

        // Instantiate the controller class
        $controllerClassName = 'Controllers\\' . $this->currentController;
        $this->currentController = new $controllerClassName;

        // Check for the method part of the URL
        if (isset($url[1])) {
            // Check to see if method exists in controller
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        // Get params - the rest of the URL parts
        $this->params = $url ? array_values($url) : [];

        // Call a callback with array of params
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    /**
     * Gets the URL and splits it into an array.
     * @return array The URL parts.
     */
    public function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }
}