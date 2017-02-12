<?php

    /*   определённые константы:
     *   define('DS', DIRECTORY_SEPARATOR);
     *   define('ROOT', dirname(__FILE__));
     */

    class Router
    {
        private $routes; // массив в котором будут хранится маршруты (роуты)

        public function __construct()
        {
            $routesPath = ROOT.DS.'config'.DS.'routes.php';
            $this->routes = include($routesPath);
        }

        private function getUri()
        {
            // получить строку запроса
            if (!empty($_SERVER['REQUEST_URI'])){
                return trim($_SERVER['REQUEST_URI'], '/');
            }
        }

        public function run() // будет принимать управление от front controller'а
                              // отвечает за анализ запроса и передачу управления
        {
            $uri = $this->getUri();

            foreach ($this->routes as $uriPattern => $path){
                if(preg_match("~$uriPattern~", $uri)){ // проверяем наличие РОУТОВ

                    // Определить controller action параметры

                    $internalRoute = preg_replace("~$uriPattern~", $path, $uri);
                    // Если есть совпадение, определить какой контроллер
                    // и action обрабатывают запрос

                    $segments = explode('/', $internalRoute); // разбиваем на контроллер и экшен

                    $controllerName = array_shift($segments).'Controller'; // в соответствии с названием файла
                    $controllerName = ucfirst($controllerName); // получили имя контроллера типа NewsController
                    $actionName = 'action'.ucfirst(array_shift($segments)); // в соответствии с названием экшена типа actionIndex

                    $parameters = $segments; // массив с параметрами

                    // Подключаем файл класса-контроллера
                    $controllerFile = ROOT.DS.'controllers'.DS.$controllerName.'.php';

                    if (file_exists($controllerFile)) {
                        include_once ($controllerFile);

                    }

                    // Создать объект, вызвать action
                    $controllerObject = new $controllerName;

                    $result = call_user_func_array(array($controllerObject, $actionName), $parameters);

                    if($result != null) {
                        break;
                    }
                }
            }

        }

    }