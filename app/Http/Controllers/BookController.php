<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $books = Book::orderBy('id', 'desc')->get();
            return $this->successResponse($books, 'Succesfully Fetched', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
        // dd($request->image);
        // return $this->successResponse($request->image, 'Succesfully Created', Response::HTTP_OK);
        // if ($request->image != null) {
        //     return $this->successResponse('image has', 'Succesfully Created', Response::HTTP_OK);
            
        // }else{
        //     return $this->successResponse('no image', 'Succesfully Created', Response::HTTP_OK);
        // }
        $data = [
            'title' => $request->title,
            'author_name' => $request->author_name,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'qty' => $request->qty,
            'price' => $request->price,
            'user_id' => Auth::guard('api')->user()->id
        ];

        if ($request->image != null) {
            $image_no = 1;
            foreach ($request->image as $item) {
                $imageName = time() .$image_no. '.' . $item->extension();
               
                $item->move(public_path('/books/images'), $imageName);

                // $path = $item->store('books/images');
                $data['image_' . $image_no] = asset('/books/images/' . $imageName);
                $image_no++;
            }
        }
        // return $this->successResponse($data, 'Succesfully Created', Response::HTTP_OK);
        try {
            $book = Book::create($data);
            return $this->successResponse($book, 'Succesfully Created', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function show(Book $book)
    {
        try {
            $book = Book::with('user')->where('id',$book->id)->first();

            $images = [];
            if ($book->image_1 != null) {
                $images[] = $book->image_1;
            }
            if ($book->image_2 != null) {
                $images[] = $book->image_2;
            }
            if ($book->image_3 != null) {
                $images[] = $book->image_3;
            }
            if ($book->image_4 != null) {
                $images[] = $book->image_4;
            }
            $book->images = $images;
            return $this->successResponse($book, 'Succesfully Fetched', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function edit(Book $book)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Book $book)
    {
        // dd($request->all());
        $data = [
            // 'title' => $request->title,
            // 'author_name' => $request->author_name,
            // 'category_id' => $request->category_id,
            // 'description' => $request->description,
            // 'qty' => $request->qty,
            'price' => $request->price,
            // 'user_id' => Auth::guard('api')->user()->id
        ];
        // if ($request->hasFile('image_1')) {

        //     $path = $request->file('image_1')->store('books/images');
        //     $data['image_1'] = $path;
        // }
        // if ($request->hasFile('image_2')) {

        //     $path = $request->file('image_2')->store('books/images');
        //     $data['image_2'] = $path;
        // }
        // if ($request->hasFile('image_3')) {

        //     $path = $request->file('image_3')->store('books/images');
        //     $data['image_3'] = $path;
        // }
        // if ($request->hasFile('image_4')) {

        //     $path = $request->file('image_4')->store('books/images');
        //     $data['image_4'] = $path;
        // }
        try {
            $book = $book->update($data);
            return $this->successResponse($book, 'Succesfully Updated', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function destroy(Book $book)
    {
        try {
            $book->delete();
            return $this->successResponse(null, 'Succesfully Deleted', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
