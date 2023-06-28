# Wonde
Technical test see readme for more information

## Task Details
### User Story: 
As a Teacher I want to be able to see which students are in my class each day of the week so that I can be suitably prepared.
### Notes:
You can use any technology that you think is suitable for this task.
The brief has been kept deliberately vague to allow you scope to apply your own assumptions and interpretations and to demonstrate your skills.
 
### MiroBoard
Good, Bad and the Ugly, here I have posted my process for completing the user-story. What needs to be improved and what future working i would look to add on this miroboard [Link](https://miro.com/app/board/o9J_kj2Iu4I=/?share_link_id=314554344593)  after task is complete.

## How it works 
/getTeachersIds
Go to this endpoint to gain the TeachersID.There will be a list of IDs and Names of the teachers

Once gained the TeachersID from the endpoint called hit this endpoint using the ID

/getTeachersWeeklyTimeTable/`TeachersID`
Once hitting this endpoint wait for the page to load and you will be able to see the completed user Story task.

## Setup
Create an Env file using the env.example file as template (APP_DEBUG is optional)

In the terminal `composer install`
then run `php artisan serve`

Then hit the endpoints with the address shown in the terminal for example `http://127.0.0.1:8000/getTeachersIds` or to see the userStory task completed `http://127.0.0.1:8000/getTeachersWeeklyTimeTable/A1851705507`

