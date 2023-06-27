<?php

namespace App\Models;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Wonde\Client;


class WondeClient extends Model
{
    private $schoolClientSetup;
    public function __construct()
    {
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
        ]) as $employee)
        {
            if ($employee->employment_details->data->teaching_staff && !empty($employee->classes->data))
            {
                $teachersArray[$employee->id] = ['forename' => $employee->forename, 'surname' => $employee->surname];
            }
        }
        $this->displayTeachers($teachersArray);
    }
    public function getTeachersClassSchedule($teachersId)
    {
        $school = $this->schoolClientSetup;

        $classeDetailsArray = [];
        foreach ($school->employees->all([
            "employment_details",
            "classes",
            ]) as $employee)
        {
            if ($employee->id == $teachersId) {
                array_push($classeDetailsArray,$employee->classes->data);
                break;
            }
        }
        $classGroupNamesArray = [];
        foreach ($classeDetailsArray[0] as $class)
        {
            array_push($classGroupNamesArray,[$class->id => $class->name]);
        }
        return $classGroupNamesArray;
    }

    public function getClassRegister($class_name)
    {
        $school = $this->schoolClientSetup;
            foreach ($school->classes->all([
                "students",
            ], ['class_name' => $class_name]) as $class)
            {
                $studentsArrayForClass = $class->students->data;
            }
        $studentsIdSurnameNamesArray =[];

        foreach ($studentsArrayForClass as $students)
        {
            array_push($studentsIdSurnameNamesArray,['id' => $students->id, 'surname' => $students->surname, 'forename' => $students->forename,]);
        }

        return $studentsIdSurnameNamesArray;
    }

    public function getCurrentDateTime()
    {
        $dateTime = new DateTime();
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s.u');
        return $formattedDateTime;
    }

    public function lessonPeriodSchedule($classId)
    {
        $school = $this->schoolClientSetup;

        foreach ($school->lessons->all([
            "class",
            "period",
        ], ['lessons_start_after' => $this->getCurrentDateTime()]) as $lessons) {
            if($lessons->class->data->id == $classId )
            {
                return [$classId => [$lessons->period->data->day => $lessons->period->data->start_time]];
            }
        }
    }
public function getlessonPeriodSchedule($LessonidAndClassGroupNameArray)
{
    $LessonScheduleArray = [];

    foreach ($LessonidAndClassGroupNameArray as $class)
    {
        $classId = array_key_first($class);
        array_push($LessonScheduleArray,$this->lessonPeriodSchedule($classId));
    }
    return $LessonScheduleArray;
}
    public function getClassIdClassGroupStudents($LessonidAndClassGroupNameArray)
    {
        $ClassIdClassGroupStudentsArray=[];
        foreach ($LessonidAndClassGroupNameArray as $class )
        {
            $classId = array_key_first($class);
            $teachingGroupName = $class[$classId];

            $ClassIdClassGroupStudentsArray[$classId] = [$teachingGroupName => [$this->getClassRegister($teachingGroupName)]];
        }
        return $ClassIdClassGroupStudentsArray;
    }
    public function createWeeklyTimeTable ($LessonScheduleArray,$ClassIdClassGroupStudentsArray)
    {
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

        echo $this->displayWeeklyTimeTable($WeeklyTimeTable);
    }

    public function displayTeachers($employees)
    {
        echo '<!DOCTYPE html>';
        echo '<html>';
        echo '<head>';
        echo '<title>Teachers</title>';
        echo '<style>';
        echo '.employee-container {text-align: center;}';
        echo '.employee {display: inline-block; margin: 10px;}';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<h2 style="text-align: center;">Teachers</h2>';
        echo '<div class="employee-container">';

        foreach ($employees as $id => $employee) {
            echo '<div class="employee">';
            echo '<p><strong>ID:</strong> ' . $id . ' | ' .
                '<strong>Forename:</strong> ' . $employee['forename'] . ' | ' .
                '<strong>Surname:</strong> ' . $employee['surname'] . '</p>';
            echo '</div>';
        }
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }


    public function displayWeeklyTimeTable($WeeklyTimeTable){

        $table = '<table>';
        $table .= '<thead><tr><th>Day</th><th>Time</th><th>Group</th><th>ID</th><th>Surname</th><th>Forename</th></tr></thead>';
        $table .= '<tbody>';

        $previousGroup = null; // Track the previous group

        foreach ($WeeklyTimeTable as $item) {
            foreach ($item as $day => $slots) {
                foreach ($slots as $slot) {
                    foreach ($slot as $time => $groups) {
                        foreach ($groups as $group => $students) {
                            foreach ($students[0] as $student) {
                                $table .= '<tr>';
                                $table .= "<td>$day</td>";
                                $table .= "<td>$time</td>";
                                $table .= "<td>$group</td>";
                                $table .= "<td>{$student['id']}</td>";
                                $table .= "<td>{$student['surname']}</td>";
                                $table .= "<td>{$student['forename']}</td>";
                                $table .= '</tr>';
                            }
                            if ($group !== $previousGroup) {
                                // Add two empty rows if the group changes
                                $table .= '<tr><td colspan="6">&nbsp;</td></tr>';
                                $table .= '<tr><td colspan="6">&nbsp;</td></tr>';
                            }
                            $previousGroup = $group; // Update the previous group
                        }
                    }
                }
            }
        }

        $table .= '</tbody></table>';

        return $table;
    }

}
