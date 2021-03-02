<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'mockup/index';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['minify/(.*?)'] = 'minify/$1';
// $route['single/(.*?)'] = 'pages/single/$1';
// $route['script/(:any)'] = '$1/script/index';
// $route['script/(:any)/(.*?)'] = '$1/script/$2';
// $route['script/(.*?)'] = function ($a) {
//     $method = null;
//     $segment = explode('/', $a);
//     $path = $segment;
//     $keys = array_keys($segment);
//     $last = $keys[count($keys) - 1];
//     $method = $segment[$last];
//     unset($path[$last]);
//     if (is_file(APPPATH . 'controllers/' . implode('/', $path) . '/' . ucfirst($method) . '.php')) {
//         return implode('/', $path) . '/' . $method . '/script';
//     }else {
//         return  implode('/', $path) . '/script/' . $method;
//     }
// };

$route['(api|script)/(.*?)'] = function ($type, $uri_string) {
    $segment = explode('/', $uri_string);
    $uri = array_slice($segment, 1);

    return $segment[0] . '/' . $type . ((count($uri) > 0) ?  '/' . implode('/', $uri) : null);
};

$route['(.*?)'] = function ($uri) {
    $segment = explode('/', $uri);
    $path = '';
    $is_controller = false;
    if($segment[0] == 'mockup') {
        $is_controller = true;
        $path = $uri;
    }
    else {
        foreach ($segment as $step) {
            if (!in_array($step, ['admin'])) {
                switch ($step) {
                    case 'enquiry':
                    case 'portfolio':
                        $path .= '/board/' . $step;
                        $is_controller = true;
                        break;
                    case 'mockup':
                    default:
                        $path .= '/' . ucfirst($step);
                        if (is_file(APPPATH . 'controllers' . $path . '.php')) {
                            $is_controller = true;
                        }
                        $path = strtolower($path);
                        break;
                }
            }
        }
    }
    if ($is_controller) {
        return $path;
    } else {
        return 'mockup/' . $uri;
    }
};
