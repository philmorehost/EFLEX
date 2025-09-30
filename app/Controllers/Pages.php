<?php
namespace Controllers;

use Core\Controller;

class Pages extends Controller {
    public function __construct() {
        // This is where you would load a model if needed
        // $this->pageModel = $this->model('Page');
    }

    public function index() {
        $data = [
            'title' => 'Welcome',
            'description' => 'This is the homepage of the VTU application.'
        ];

        $this->view('pages/index', $data);
    }

    public function about() {
        $data = [
            'title' => 'About Us',
            'description' => 'This is the about page.'
        ];

        $this->view('pages/about', $data);
    }
}