<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Services\FCMService;
use App\Traits\ApiResponse;
use Exception;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $book = Book::where('id', $request->book_id)->first();
        $seller_id = $book->user_id;
        if($seller_id == Auth::guard('api')->user()->id){
            return $this->successResponse(null, 'Seller can buy his own book', 400);
        }
        if ($book->qty < $request->qty) {
            return $this->successResponse(null, 'Seller doesnot have the requested quantity', 400);
        }
        $data = [
            'book_id' => $request->book_id,
            'buyer_id' => Auth::guard('api')->user()->id,
            'seller_id' => $seller_id,
            'qty' => $request->qty,
            'price' => ($book->price * $request->qty),
        ];
        $seller_data = User::find($seller_id);
        $buyer_data = User::find(Auth::guard('api')->user()->id);
        try {
            $order =  Order::create($data);
              FCMService::send(
                $seller_data->device_key,
                [
                    'title' => "New Buy Request",
                    'body' => $buyer_data->name." has sent a buy request",
                ]
            );
           
            return $this->successResponse($order, 'Successfully Created', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function userBuyOrder()
    {
        try {
            // dd(Auth::guard('api')->user()->id);

            // $order = Order::where('buyer_id', Auth::guard('api')->user()->id)->with('buyer', 'seller')->orderBy('id', 'desc')->get();
            $order = Auth::guard('api')->user()->buyOrder;
            $data = [];
            $orderData = [];
            foreach ($order as $item) {
                $data['order_info'] = $item;
                $data['seller_info'] = User::find($item->seller_id);
                $data['book_info'] = Book::find($item->book_id);
                array_push($orderData, $data);
            }
            return $this->successResponse($orderData, 'Successfully Fetched', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function userSellOrder()
    {
        try {
            // $order = Order::where('seller_id', Auth::guard('api')->user()->id)->with('seller', 'buyer')->orderBy('id', 'desc')->get();
            $order = Auth::guard('api')->user()->sellOrder;
            $data = [];
            $orderData = [];
            foreach ($order as $item) {
                $data['order_info'] = $item;
                $data['buyer_info'] = User::find($item->buyer_id);
                $data['book_info'] = Book::find($item->book_id);
                array_push($orderData, $data);
            }
            return $this->successResponse($orderData, 'Successfully Fetched', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function acceptOrder($id)
    {
        $order = Order::find($id);
        if ($order) {
            if ($order->is_accepted) {
                return $this->successResponse($order, 'Already Accepted This Order', Response::HTTP_OK);
            }
            if ($order->seller_id == Auth::guard('api')->user()->id) {
                $book = Book::find($order->book_id);
                if ($book->qty >= $order->qty) {
                    try {
                        $book->decrement('qty', $order->qty);
                        $order->is_accepted = true;
                        $order->save();
                        $seller_data = User::find($order->seller_id);
                        $buyer_data = User::find($order->buyer_id);
                        FCMService::send(
                            $buyer_data->device_key,
                            [
                                'title' => "Request Accepted",
                                'body' => $seller_data->name." has accepted your buy request",
                            ]
                        );
                        return $this->successResponse($order, 'Successfully Accepted Order', Response::HTTP_OK);
                    } catch (Exception $e) {
                        return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return $this->successResponse(null, 'Book Qty is lower than order quantity.', Response::HTTP_OK);
                }
            } else {
                return $this->successResponse(null, 'Order Invalid', Response::HTTP_OK);
            }
        } else {
            return $this->successResponse(null, 'Order Not Found', Response::HTTP_OK);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }
}
