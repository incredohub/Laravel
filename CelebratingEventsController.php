<?php

namespace App\Http\Controllers\User;

use App\Contracts\User\CelebratingEventsInterface;
use App\Contracts\ProductsServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CelebratingEventsController extends Controller
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
    public function index( CelebratingEventsInterface $celebratingEvents )
    {
        $celebrations = $celebratingEvents->index();

        return view('user.celebratingEvent',
            [
                'celebrations' => $celebrations,
            ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('user.celebratingEvent-create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store( CelebratingEventsInterface $celebratingEvents, Request $request )
    {
        $this->validate($request, [
            'event_name' => 'required|string|min:2|max:180',
            'event_close_date' => 'required|regex:/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/|after:today',
        ]);

        $data = $request->all();
        $result = $celebratingEvents->store( $data );

        if($result){
            return   \Redirect::intended('user/celebratevents');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show( CelebratingEventsInterface $celebratingEvents, ProductsServiceInterface $productsService, $id )
    {
        $result = $celebratingEvents->show( $id );
        $celebrations = $result[0];
        $productIds = $result[1];

        $data = $productsService->showSelectedProducts( $productIds );

        $products = $data[0];
        $options = $data[1];
        $brands = $data[2];

        return view('user.celebratingEvent-edit',
            [
                'products' => $products,
                'options' => $options,
                'brands' => $brands,
                'celebrations' => $celebrations,
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id )
    {
        //
    }

    /**
     *
     */
    public function search( CelebratingEventsInterface $celebratingEvents, Request $request )
    {
        $data = $request->all();
        if( !empty($data) ){
            $result = $celebratingEvents->search($data);
        }else{
            $result = null;
        }

        return view('user.celebratingEvent-search',
            [
                'celebrations' => $result[0],
                'user' => $result[1],
            ]);
    }

    /**
     *
     */
    public function addGift( CelebratingEventsInterface $celebratingEvents, Request $request )
    {
        $data = $request->all();
        $result = $celebratingEvents->addGift( $data );

        if($result[0]){
            $request->session()->forget('gift_product_id');
            return \Redirect::intended('user/celebratevents/'.$result[1]->event_url);
        }
    }

    /**
     *
     */
    public function combineGift( CelebratingEventsInterface $celebratingEvents, Request $request )
    {
        $data = $request->all();
        $result = $celebratingEvents->combineGift();

        if(count($result) > 1){
            $request->session()->put('gift_product_id', $data['id']);
            return \Redirect::to('user/celebratevents');
        }else{
            $data['celebrating_event_id'] = $result[0]->id;
            $data['product_id'] = $data['id'];
            return $this->storeGift( $data, $celebratingEvents );
        }
    }

    /**
     *
     */
    private function storeGift( $data, $celebratingEvents )
    {
        $result = $celebratingEvents->storeGift( $data );
        if($result->id){
            return \Redirect::intended('user/celebratevents/'.$data['celebrating_event_id']);
        }
        return false;
    }
    }
