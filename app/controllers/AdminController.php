<?php
class AdminController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['main_admin']);
        $this->view('dashboards/main_admin', ['title' => 'Admin Panel']);
    }
}
