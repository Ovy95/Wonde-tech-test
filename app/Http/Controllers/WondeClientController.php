<?php

namespace App\Http\Controllers;

use App\Models\WondeClient;
class WondeClientController extends Controller
{
    //getTeachersIds
    public function getTeachersIds()
    {
        $clientIntegration = new WondeClient();
        $data = $clientIntegration->getTeachersIds();

        return response()->json($data);
    }
    public function getTeachersClassSchedule($teachersId)
    {
        $clientIntegration = new WondeClient();
        $data = $clientIntegration->getTeachersClassSchedule($teachersId);

        return response()->json($data);
    }

    public function getClassRegister($teachersClassNamesArray)
    {
        $clientIntegration = new WondeClient();
        $data = $clientIntegration->getClassRegister($teachersClassNamesArray);

        return response()->json($data);
    }


    public function lessonPeriodSchedule()
    {
        $clientIntegration = new WondeClient();
        $data = $clientIntegration->lessonPeriodSchedule();

        return response()->json($data);
    }

    public function getTeachersWeeklyTimeTable($teachersId)
    {
        $clientIntegration = new WondeClient();
        $data = $clientIntegration->getTeachersWeeklyTimeTable($teachersId);

        return response()->json($data);
    }
}
