<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesApiRequests;

abstract class Controller
{
    use AuthorizesApiRequests;
}
