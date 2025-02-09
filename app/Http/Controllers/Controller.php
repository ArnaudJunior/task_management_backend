<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;


/**
 * 
 * @OA\info(
 *     version="1.0.0",
 *     title="Task Management Backend | Restful API",
 *     contact=@OA\Contact(
 *         email="arnaudjuniorwolle003@gmail.com"
 *     ),
 *     @OA\Server(
 *         description="Task Management",
 *         url="http://localhost:8000/api"
 *     ),
 * )
 * 
 * 
 * 
 */


class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
