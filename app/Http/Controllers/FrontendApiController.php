<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class FrontendApiController extends Controller
{
    use ApiResponse;
    public function category()
    {
        try {
            $category = Category::get();
            return $this->successResponse($category, 'Succesfully Fetched', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function categoryBooks($id)
    {
        try {
            $category = Category::with('books')->find($id);
            return $this->successResponse($category, 'Succesfully Fetched', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function userBooks($id)
    {
        try {
            $user = User::with('books')->find($id);
            return $this->successResponse($user, 'Succesfully Fetched', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function homePageBooks()
    {
        try {
            $shopBooks = Book::where('user_id', 1)->with('category:title,id', 'user:name,id')->orderBy('id', 'desc')->get();
            $otherBooks = Book::where('user_id', '!=', 1)->with('category:title,id')->orderBy('id', 'desc')->get();
            $data = [
                'shop_books' => $shopBooks,
                'other_books' => $otherBooks,
            ];
            return $this->successResponse($data, 'Succesfully Fetched', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function searchBook(Request $request)
    {
        $query = $request->input('q');

        $books = Book::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orWhere('author_name', 'like', "%{$query}%")
            ->get();

        return $this->successResponse($books, 'Successfully Fetched ', Response::HTTP_OK);
    }
      public function storeV2(Request $request)
    {
        // dd($request->all());
        $data = [
            'title' => $request->title,
            'author_name' => $request->author_name,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'qty' => $request->qty,
            'price' => $request->price,
            'user_id' => Auth::guard('api')->user()->id
        ];

        // if ($request->image != null) {
        //     $image_no = 1;
        //     foreach ($request->image as $item) {
        //         $imageName = time() . '.' . $item->extension();
        //         $item->move(public_path('/books/images'), $imageName);

        //         // $path = $item->store('books/images');
        //         $data['image_' . $image_no] = asset('/books/images/' . $imageName);
        //         $image_no++;
        //     }
        // }
        if ($request->hasFile('image_1')) {

            $imageName = time() . '.' . $request['image_1']->extension();
            $request['image_1']->move(public_path('/books/images'), $imageName);
            $data['image_1'] = asset('/books/images/' . $imageName);
        }
        if ($request->hasFile('image_2')) {

            $imageName = time() . '.' . $request['image_2']->extension();
            $request['image_2']->move(public_path('/books/images'), $imageName);
            $data['image_2'] = asset('/books/images/' . $imageName);
        }
        if ($request->hasFile('image_3')) {

            $imageName = time() . '.' . $request['image_3']->extension();
            $request['image_3']->move(public_path('/books/images'), $imageName);
            $data['image_3'] = asset('/books/images/' . $imageName);
        }
        if ($request->hasFile('image_4')) {

            $imageName = time() . '.' . $request['image_4']->extension();
            $request['image_4']->move(public_path('/books/images'), $imageName);
            $data['image_4'] = asset('/books/images/' . $imageName);
        }
        try {
            $book = Book::create($data);
            return $this->successResponse($book, 'Succesfully Created', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function updateDeviceKey(Request $request){
        $user_id = Auth::guard('api')->user()->id;
        $data = [
            'device_key' => $request->device_key
        ];
        User::where('id',$user_id)->update($data);
        return $this->successResponse(null,$message="Device key updated",200);
    }
}
