<?php namespace Kernel;

use View;

/**
 * Setting and Dispatching routes
 * Class Engine
 *
 * @package Kernel
 */
class Engine
{
    /**
     * holds the routes assigned to get() method
     *
     * @var array
     */
    public static $routes = array();

    /**
     * If false, strict mode is off for URL matching
     * means /home is equal to /HOME
     *
     * @var bool
     */
    private static $strict = false;

    /**
     * default controller
     *
     * @var string
     */
    private static $controller = "HomeController";
    /**
     * default method
     *
     * @var string
     */
    private static $method = "index";
    /**
     * container for parameters.
     *
     * @var array
     */
    private static $parameters = array();
    /**
     * in case of controllers in subdirectories
     *
     * @var string
     */
    private static $subdirectory = null;
    /**
     * in case controllers are using namespace
     *
     * @var string
     */
    private static $requestMethod = null;
    /**
     * in case controllers are using namespace
     *
     * @var string
     */
    private static $namespace = null;


    /**
     * Parse and match the URL to each assigned routes and calls
     * the assigned controller and it's method along with the
     * parameters based on array given in the get() method.
     *
     * @var array $originalUrl
     * @var int $compare
     * @var array $path
     * @var array $url
     * @var array $params
     * @return mixed
     */
    public function __construct()
    {
        session_start();
        /**
         * Return a service unavailable page(defined in config/application.php)
         */
        if (MAINTENANCE_MODE === TRUE) {
            return View::render('errors/503');
        }

        /**
         * Get the sanitized url from private method getUrl()
         */
        $originalUrl = self::parseUrl();
        $compare = 0;

        /**
         * Return default controller and method if
         * URL is not set.
         */
        if (empty($originalUrl)) {
            return self::dispatch();
        } else {
            /**
             * Loop through route paths and compare to URL by checking
             * whether they match on count and that they match every values.
             */
            foreach (array_values(self::$routes) as $route) {
                $path = explode('/', trim($route['url'], '/'));
                $route['params'] = empty($route['params']) ? 0 : $route['params'];

                if (count($path) === count($originalUrl)) {
                    $path = array_slice($path, 0, count($path) - $route['params']);
                    $url = array_slice($originalUrl, 0, count($originalUrl) - $route['params']);

                    foreach ($path as $index => $u) {
                        /**
                         * if strict matching is on,
                         * perform a case sensitive matching
                         */
                        if (self::$strict == true) {
                            if ($u === $url[$index]) {
                                $compare++;
                                continue;
                            } else {
                                $compare = 0;
                                break;
                            }
                        } else {
                            /**
                             * if strict matching is off,
                             * perform regex matching ignoring character casing.
                             */

                            /** Escape regex keywords */
                            $key = preg_replace('/\[/', '\[', $url[$index]);
                            $key = preg_replace('/\]/', '\]', $key);
                            $key = preg_replace('/\(/', '\(', $key);
                            $key = preg_replace('/\)/', '\)', $key);
                            if (preg_match("/^{$key}$/i", $u)) {
                                $compare++;
                                continue;
                            } else {
                                $compare = 0;
                                break;
                            }
                        }
                    }
                    /**
                     * compare with the length of $url to identify
                     * if it matches all the keys of the route.
                     */
                    if ($compare === count($url)) {
                        $params = array_slice($originalUrl, count($originalUrl) - $route['params']);

                        /**
                         * if closure is set to a route, return a call
                         * to that closure. parameters are included if present.
                         */
                        if (isset($route['closure'])) {
                            /**
                             * block request if doesn't match with the request method.
                             */
                            if (!empty($route['requestMethod'])) {
                                if (strtoupper($route['requestMethod']) !== $_SERVER['REQUEST_METHOD']) {
                                    return self::error();
                                }
                            }
                            return call_user_func_array($route['closure'], $params);
                        }

                        self::$controller = $route['controller'];
                        self::$method = $route['method'];
                        self::$parameters = $params;
                        self::$subdirectory = $route['subdirectory'];
                        self::$namespace = $route['namespace'];
                        self::$requestMethod = $route['requestMethod'];

                        return self::dispatch();
                    }
                } else {
                    continue;
                }
            }

            /**
             * Return 404 error page
             * if all fails.
             */
            return self::error();
        }
    }


    /**
     * assign routes paths, names, controllers, etc.
     * dispatch() method relies on it.
     *
     * @param string $name
     * @param string $url
     * @param string $action
     * @param string $requestMethod
     * @var string $path
     * @var string sub
     * @var int $param
     * @return bool
     */
    public static function assign($name, $url, $action, $requestMethod = null, $namespace = null)
    {
        /**
         * get the path that will be used to compared
         * on the active URL.
         */
        self::$routes[$name]['url'] = $url;
        self::$routes[$name]['requestMethod'] = is_null($requestMethod) ? "" : $requestMethod;
        self::$routes[$name]['namespace'] = is_null($namespace) ? "" : trim($namespace, '\\') . '\\';

        /**
         * count the parameters defined in the route
         * path using colon ':' sign.
         */
        $params = 0;
        for ($i = 0; $i < strlen($url); $i++) {
            if ($url[$i] == ":") {
                $params++;
            }
        }
        self::$routes[$name]['params'] = (empty($params)) ? 0 : $params;

        /**
         * if route uses function closure
         * do not set the action parameters.
         */
        if (is_callable($action)) {
            return self::$routes[$name]['closure'] = $action;
        }

        if (!preg_match('/\:\:/', $action)) {
            return trigger_error("Argument passed via assign() doesn't have a valid separator '::'", E_USER_ERROR);
        }

        $path = explode('/', trim($action, '/'));

        /**
         * Find the controller and method if a subdirectory
         * is present in $action.
         */
        self::$routes[$name]['controller'] = explode('::', $path[count($path) - 1])[0];
        self::$routes[$name]['method'] = explode('::', trim($path[count($path) - 1], '()'))[1];

        /**
         * Get the controller's subdirectory if specified
         * on action parameter along with the controller and index itself
         * to still call the controller class.
         */
        if (preg_match('/\//', $action)) {
            $sub = "";
            for ($i = 0; $i < count($path) - 1; $i++) {
                $sub = $sub . $path[$i] . '/';
            }
            self::$routes[$name]['subdirectory'] = trim($sub, '/') . '/';
        } else {
            self::$routes[$name]['subdirectory'] = "";
        }

        return true;
    }


    /**
     * Replication of assign() method but
     * sets route as a post request
     *
     * @param string $name
     * @param string $url
     * @param string $action
     * @param string $requestMethod
     * @param string $namespace
     * @return mixed
     */
    public static function post($name, $url, $action, $requestMethod = 'POST', $namespace = null)
    {
        return self::assign($name, $url, $action, $requestMethod, $namespace);
    }


    /**
     * Determine whether route matching should
     * strict URL matching on character casing.
     *
     * @param $bool
     * @return bool
     */
    public function strict($bool)
    {
        if ($bool == true) {
            self::$strict = true;
        } else {
            self::$strict = false;
        }
        return (true);
    }


    /**
     * Return array containing all the routes and can be
     * dumped to view those values.
     *
     * @var array $list
     * @return array
     */
    public static function getList()
    {
        return (self::$routes);
    }


    /**
     * Instantiate the controller and call the method along
     * with the parameters if present.
     *
     * @return mixed
     */
    private static function dispatch()
    {
        /**
         * if request method is defined in a route config
         * and is not equal to the current request method,
         * return an error page.
         */
        if (!empty(self::$requestMethod)) {
            if (strtoupper(self::$requestMethod) !== $_SERVER['REQUEST_METHOD']) {
                return self::error();
            }
        }

        /**
         * Dispatch the controller class, prepend namespace if specified and pass
         * it all possible arguments.
         */
        require_once '../app/controllers/' . self::$subdirectory . self::$controller . '.php';
        $controller = self::$namespace . self::$controller;

        return (call_user_func_array([new $controller(), self::$method], self::$parameters));
    }


    /**
     * If non of the routes matched on the URL, return a 404 page.
     *
     * @return mixed
     */
    private static function error()
    {
        require_once '../views/errors/404.php';
        return exit();
    }


    /**
     * Parse the URL if present.
     *
     * @return array
     */
    private static function parseUrl()
    {
        if (isset($_GET['url'])) {
            $url = filter_var($_GET['url'], FILTER_SANITIZE_URL);
            $url = explode('/', trim($url, '/'));
        } else {
            $url = array();
        }

        return ($url);
    }
}