<?php
class HomeController extends Controller
{
    private Property $propertyModel;

    public function __construct()
    {
        $this->propertyModel = $this->model('Property');
    }

    public function index(): void
    {
        $this->view('home/index', [
            'title' => 'Find your next stay with StayNest',
            'featuredProperties' => $this->propertyModel->recentApproved(3),
        ]);
    }
}
