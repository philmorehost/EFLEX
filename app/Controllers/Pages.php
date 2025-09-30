<?php
namespace Controllers;

use Core\Controller;

class Pages extends Controller {
    private $landingPageModel;

    public function __construct() {
        $this->landingPageModel = $this->model('LandingPage');
    }

    public function index() {
        $content = $this->landingPageModel->getContent();

        $data = [
            'title' => $content['hero_title'] ?? 'Welcome',
            'content' => $content
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