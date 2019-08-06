Purpose

The purpose of TimeTracker is to allow employee users to record their work time on a specific task on the Kimai platform, and the time recorded to spend on Dolibarr so that the Dolibarr supervisor can control it.

Implementation

TimeTracker is essentially an intermediate script in php using the CodeIgniter framework which uses the Dolibarr and Kimai APIs to coordinate employee timesheets. After transferring the timesheets, for security reasons and to detect possible edits to the timesheets we keep records of the timesheets we transferred.

Installation

We need a webserver with PHP 7.3 and a database.
We create a database in our database:

CREATE DATABASE timetracker;

and inside it we run the sql code in the table creation file.

We place the “Time-Tracker” folder on our webserver. We go to application> config> database.php and import the data from our database.
Configuration

The application> config> tt_config.php file contains the settings for running the application. There we put the Dolibarr and Kimai API Keys and select true or false in the "transfer_on_load" function to choose whether we want to transfer the timesheets when loading the application page.
More detail:

// Transfer timesheets from Kimai to Dolibarr on page load.
$ config ['transfer_on_load'] = false;
// Dolibarr API Key
$ config ['DOLAPIKEY'] = 'Wi2S4ZQGtKYcv9hur9wPV88sj5w90H1F';
$ config ['dolibarr_uri'] = 'http://192.168.2.234/dolibarr/htdocs/api/index.php/';
// Kimai 2 API Authentication
$ config ['kimai_user'] = 'admin';
$ config ['kimai_token'] = 'password';
$ config ['kimai_uri'] = "kimai.local / api /";

We find (or create) the Dolibarr API Key on the Dolibarr platform in the user's profile.
Kimai uses API headers for authentication of the user that are in the user's profile, which is its username and the API Password that it sets (it is different from the login password).
Both API Authentications are required to have the necessary permissions for the user whose keys we will use.

Controllers

The Controllers of the project include TimeController which contains the code to display the TimeTracker homepage.
There is also AjaxController which manages the responses from the application's AJAX Requests.

Libraries

In library sync_handler.php, we include functions that copy data from one application to another and store the associations of the two applications to avoid unnecessary API requests. The following functions are included:
    • syncUsers (): Uses the usernames of Dolibarr & Kimai users to store the relationship between them.
      
    • syncCustomers (): Copies all Dolibarr Customers that do not exist in Kimai to Kimai.
      
    • syncProjects (): Copies all Dolibarr projects that do not exist in Kimai and assigns them to the appropriate Customers in Kimai.
      
    • syncTask (): Copies all Dolibarr tasks to Kimai as activities and assigns them to the appropriate Projects in Kimai.
      
    • syncTimesheets (): Synchronizes timesheets from Kimai by sending them to Dolibarr using the two platforms API. It tries to match Dolibarr's tasks with Kimai's activities so that the timesheets are properly transferred.

The library api_handler.php which contains the functions that manage the 2 application APIs.
    • callKimaiAPI ($ method, $ url, $ body = false): sends requests to the Kimai API using the guzzle library for php. As a method we put a string with the request method ("GET", "POST", "PATCH", "PUT"). As the url we put the API method we will run and as the body the array with the body We want to include in the request.

	• callDOLAPI ($ method, $ url, $ body = false): sends requests to the Kimai API using the guzzle library for php. "PUT"). As the url we put the API method we will run and as the body the array with the body We want to include in the request.

The library restore_associations.php contains functions that repair associations in the TimeTracker database.
    • associateUsers (): fixes the table of users. If it fails, it returns false.
    • associateCustomers (): fixes the table of customers. If it fails, it returns false.
    • associateProjects (): fixes the project table. If it fails, it returns false.
    • associateTasks (): fixes the tasks / activities table. If it fails, it returns false.
    • restore (): Attempts to run all the above functions. If it fails, it returns false.
