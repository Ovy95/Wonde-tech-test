<?php

namespace App\Http\Controllers;

use App\Models\WondeClient;
class WondeClientController extends Controller
{
    public function getTeachersIds()
    {
        $clientIntegration = new WondeClient();
        $data = $clientIntegration->getTeachersIds();

        return response()->json($data);
    }

    public function getTeachersWeeklyTimeTable($teachersId)
    {
        $clientIntegration = new WondeClient();
        $data = $clientIntegration->getTeachersWeeklyTimeTable($teachersId);

        return response()->json($data);
    }
}
