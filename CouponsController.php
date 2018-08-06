<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contracts\User\CouponsServiceInterface;

class CouponsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('user:wholesaler');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( CouponsServiceInterface $couponsService )
    {
        $data = $couponsService->index();

        return view('user.coupon',
            [
                'coupons' => $data,
            ]);
    }
}
