<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Mews\Captcha\Facades\Captcha;

class CaptchaController extends Controller
{
    public function show()
    {
        $captcha = Captcha::create('flat', true);

        return response()->json([
            'success' => true,
            'data' => $captcha,
        ]);
    }
}
