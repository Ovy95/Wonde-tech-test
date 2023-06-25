<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Wonde\Client;


class WondeClient extends Model
{
    private function getClientSetup(){
        $token = getenv('API_TOKEN');
        $schoolId = getenv('SCHOOL_ID');
        $client = new Client($token);
        $schoolClientSetup = $client->school($schoolId);
        return $schoolClientSetup;
    }
    public function getTeachers($teachersId)
    {
        $school = $this->getClientSetup();

        $teachersArray = [];
        foreach ($school->employees->all([
            "employment_details",
            "roles", // This doesn't work only returns null values on all employers
            "classes",
            ]) as $employee) {

            // Get Teachers basiced off teaching stuff set to true, also checks if they have got classes is true
            // returns array by ID => forename => forname, surname=> surname , classes => [classes]
            if ($employee->employment_details->data->teaching_staff && !empty($employee->classes->data)) {
                $teachersArray[$employee->id] = ['forename' => $employee->forename, 'surname' => $employee->surname,'classes'=> $employee->classes->data];
            }
        }
        // Next step is the filter on classes to return students in each of the classes by day/time
       return $teachersArray;
    }
}
