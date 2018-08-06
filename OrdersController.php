<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contracts\User\OrdersServiceInterface;

class OrdersController extends Controller
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
    public function index(OrdersServiceInterface $ordersService)
    {
        $data = $ordersService->index();

        $orders = $data[0];
        $products = $data[1];
        $deliveries = $data[2];

        return view('user.order',
            [
                'orders' => $orders,
                'products' => $products,
                'deliveries' => $deliveries,
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(OrdersServiceInterface $ordersService,$id)
    {
        $data = $ordersService->show($id);

        $orders = $data[0];
        $products = $data[1];

        return view('user.orderby',
            [
                'orders' => $orders,
                'products' => $products,
            ]);
    }
}
