<?php

namespace App\Models;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Wonde\Client;


class WondeClient extends Model
{
    private $schoolClientSetup;
    public function __construct() {
        $token = getenv('API_TOKEN');
        $schoolId = getenv('SCHOOL_ID');
        $client = new Client($token);
        $this->schoolClientSetup = $client->school($schoolId);
    }
    public function getTeachersIds()
    {
        $school = $this->schoolClientSetup;

        $teachersArray = [];
        foreach ($school->employees->all([
            "employment_details",
            "classes",
        ]) as $employee) {
            if ($employee->employment_details->data->teaching_staff && !empty($employee->classes->data)) {
                $teachersArray[$employee->id] = ['forename' => $employee->forename, 'surname' => $employee->surname];
            }
        }

        return $teachersArray;
    }
    public function getTeachersClassSchedule($teachersId)
    {
        $school = $this->schoolClientSetup;

        $classeDetailsArray = [];
        foreach ($school->employees->all([
            "employment_details",
            "classes",
            ]) as $employee) {
            if ($employee->id == $teachersId) {
                array_push($classeDetailsArray,$employee->classes->data);
                break;
            }
        }
        $classGroupNamesArray = [];
        foreach ($classeDetailsArray[0] as $class){
            array_push($classGroupNamesArray,[$class->id => $class->name]);
        }
        return $classGroupNamesArray;

    }

    public function getClassRegister($class_name) {
        $school = $this->schoolClientSetup;
            foreach ($school->classes->all([
                "students",
            ], ['class_name' => $class_name]) as $class) {
                $studentsArrayForClass = $class->students->data;
            }
        $studentsIdSurnameNamesArray =[];

        foreach ($studentsArrayForClass as $students){
            array_push($studentsIdSurnameNamesArray,['id' => $students->id, 'surname' => $students->surname, 'forename' => $students->forename,]);
        }

        return $studentsIdSurnameNamesArray;
    }

    public function getCurrentDateTime() {
        $dateTime = new DateTime();
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s.u');
        return $formattedDateTime;
    }

    public function lessonPeriodSchedule($classId) {
        $school = $this->schoolClientSetup;

        foreach ($school->lessons->all( [
            "class",
            "period",
        ], ['lessons_start_after' => $this->getCurrentDateTime()] ) as $lessons) {
            if($lessons->class->data->id == $classId ) {
                return [$classId => [$lessons->period->data->day => $lessons->period->data->start_time]];
            }
        }
    }
public function getlessonPeriodSchedule ($LessonidAndClassGroupNameArray){
    $LessonScheduleArray = [];

    foreach ($LessonidAndClassGroupNameArray as $class) {
        $classId = array_key_first($class);
        array_push($LessonScheduleArray,$this->lessonPeriodSchedule($classId));
    }
    return $LessonScheduleArray;
}
    public function getClassIdClassGroupStudents ($LessonidAndClassGroupNameArray){
        $ClassIdClassGroupStudentsArray=[];
        foreach ($LessonidAndClassGroupNameArray as $class ) {
            $classId = array_key_first($class);
            $teachingGroupName = $class[$classId];

            $ClassIdClassGroupStudentsArray[$classId] = [$teachingGroupName => [$this->getClassRegister($teachingGroupName)]];
        }
        return $ClassIdClassGroupStudentsArray;
    }
    public function createWeeklyTimeTable ($LessonScheduleArray,$ClassIdClassGroupStudentsArray){
        $weeklyTimeTable = [];
        foreach ($LessonScheduleArray as $lessonID => $LessonDayAndTime ) {
            $classID = array_key_first($LessonDayAndTime);
            $dayOfTheWeek = array_key_first($LessonDayAndTime[$classID]);
            $timeOfTheLesson = $LessonDayAndTime[$classID][$dayOfTheWeek];

            $weeklyTimeTable[][$dayOfTheWeek][] = [$timeOfTheLesson=>$ClassIdClassGroupStudentsArray[$classID]];
        }

        usort($weeklyTimeTable, function($a, $b) {
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
            $dayA = array_keys($a)[0];
            $dayB = array_keys($b)[0];

            // Compare the day values
            $dayComparison = array_search($dayA, $days) - array_search($dayB, $days);
            if ($dayComparison !== 0) {
                return $dayComparison;
            }

            // If the days are the same, compare the timestamps
            $timeA = array_keys($a[$dayA][0])[0];
            $timeB = array_keys($b[$dayB][0])[0];
            return strcmp($timeA, $timeB);
        });
        return $weeklyTimeTable;
    }
    public function getTeachersWeeklyTimeTable($teachersId)
    {
        $teachersClassSchedule = $this->getTeachersClassSchedule($teachersId);
        $LessonScheduleArray = $this->getlessonPeriodSchedule($teachersClassSchedule);
        $ClassIdClassGroupStudentsArray = $this->getClassIdClassGroupStudents($teachersClassSchedule);
        $WeeklyTimeTable = $this->createWeeklyTimeTable($LessonScheduleArray,$ClassIdClassGroupStudentsArray);
        return $WeeklyTimeTable;
    }

}