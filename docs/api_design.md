HM Time API Design
==================

Data available
--------------

* Users
	* User
		* Timezone
		* Location
		* Work Hours
			* 2D array of start and end times per row


My Idea
-------
GET /hm-time/ gets all users with all possible data as a 4d array

GET /hm-time/user/{id}/ gets details of particular user

GET /hm/time/{timezone/location/workhours}/ gets all users with their {timezone/location/workhours} details.

GET /hm-time/user/{id}/{timezone/location/workhours} gets particular user with their {timezone/location/workhours} details.


From Joe:
---------
GET /api/time/now
```

**Returns a list of users with their timezones and working hours**

The reason I put "now" is if in the future we were to integrate vacatios etc, this endpoint would just reflect the people who are curently working, where as `/api/time` may return all the users and data with working schedules etc.


