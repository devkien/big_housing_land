<?php

class PolicyController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireRole([ROLE_SUPER_ADMIN]);
    }

    public function index()
    {
        $this->view('superadmin/policy');
    }

    public function termsService()
    {
        $this->view('superadmin/terms-service');
    }
    public function privacyPolicy()
    {
        $this->view('superadmin/privacy-policy');
    }

    public function cookiePolicy()
    {
        $this->view('superadmin/cookie-policy');
    }
    public function paymentPolicy()
    {
        $this->view('superadmin/payment-policy');
    }
}
