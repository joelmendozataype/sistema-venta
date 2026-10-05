<?php
class Home extends Controller
{
    public function __construct()
    {
        if (Auth::logueado()) {
            header("location: " . BASE_URL . "administracion/home");
            exit;
        }
        parent::__construct();
    }
    public function index()
    {
        $this->views->getView('home',  "index");
    }
}
