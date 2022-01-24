<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostController extends Controller
{
    public function index()
    {
        $data = Post::all();
        // return (new Response($data, 200))
        //     ->header('Access-Control-Allow-Origin', '*');
        return response(['data' => $data, 'msg' => 'data berhasil didapatkanssss']);
    }
}
