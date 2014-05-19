HM Time API Design
==================

Data available
--------------

* Users
	* User
		* Timezone ID
		* Location
		* Work Hours
			* 2D array of start and end times per row


API Design
-------
GET /hm-time/users/ gets all users with all possible data as a 4d array

GET /hm-time/users/{id}/ gets details of particular user

GET /hm-time/users?filter={timezone/location/workhours}/ gets all users with their {timezone/location/workhours} details.

GET /hm-time/users/{id}?filter={timezone/location/workhours} gets particular user with their {timezone/location/workhours} details.

GET /hm-time/timezone/ returns a list of timezones with users in them , with the current time and current offset.

GET /hm-time/timezone/europe/london/ returns a list of users in that particular timezone as well as the current time and offset.


Future extension
----------------
GET /hm-time/users?filter=now

**Returns a list of users with their timezones and working hours**

The reason I put "now" is if in the future we were to integrate vacatios etc, this endpoint would just reflect the people who are curently working, where as `/api/time` may return all the users and data with working schedules etc.

GET /hm-time/users?filter=vactions returns a list of users with their upcoming vacation set.


