<?php

namespace App\Http\Controllers;

use App\Models\WondeClient;
class WondeClientController extends Controller
{
    //
    public function getTeachers($teachersId)
    {
        $clientIntegration = new WondeClient();
        $data = $clientIntegration->getTeachers($teachersId);

        return response()->json($data);
    }
}
