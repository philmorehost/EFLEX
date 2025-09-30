<?php
namespace Core;

/**
 * Base Controller
 * This class is extended by all other controllers.
 * It provides methods to load models and views.
 */
abstract class Controller {

    /**
     * Loads a model.
     * @param string $model The name of the model to load.
     * @return object The instantiated model.
     */
    public function model($model) {
        $modelFile = APP_ROOT . '/app/Models/' . ucwords($model) . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            $modelClassName = 'Models\\' . ucwords($model);
            return new $modelClassName();
        } else {
            // Model does not exist
            die('Model does not exist: ' . $model);
        }
    }

    /**
     * Loads a view.
     * @param string $view The name of the view file to load.
     * @param array $data Data to pass to the view.
     */
    public function view($view, $data = []) {
        // Construct the path to the view file
        $viewFile = APP_ROOT . '/app/views/' . $view . '.php';

        // Check if the view file exists
        if (file_exists($viewFile)) {
            // Extract data array into individual variables
            extract($data);

            // Require the view file
            require_once $viewFile;
        } else {
            // View does not exist
            die('View does not exist: ' . $view);
        }
    }
}