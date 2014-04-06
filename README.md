# HM Time

### Overview

Build a WordPress plugin to define working hours / timezones for users of the WordPress database. Expose the data through JSON API endpoints, preferably leverageing the WP-API Plugin. This will act as a centralized store for general working hours of the Human Made team, and will be displayed in a variety of ways. For example, a Timezone widget on our internal p2 website, a hubot command in HipChat, any time interective / visualization.  

#### Detail

Each user should have the following data stored:

- Timezone
- Working Hours Start Time
- Working Hours End Time

For example, Joe Hoyle: Europe/London, working 08:00 to 18:00.  

We currently have a widget that shows something like this, but there is no data backend (what this project hopes to provide)

![](https://s3.amazonaws.com/joehoyle-captured/1ujbP.png)

The JSON API is also an important component so we can use this data elsewhere, and I imagine would have endpoints somethign like:


```
GET /api/time/now
```

**Returns a list of users with their timezones and working hours**

The reason I put "now" is if in the future we were to integrate vacatios etc, this endpoint would just reflect the people who are curently working, where as `/api/time` may return all the users and data with working schedules etc.

-----

For data entry, we should probably just add some extra fields to the Edit User admin page for their TZ, start and end workign hours. I imagine in the future we could have endpoints in the API for updating the time, so we would implement a simple mobile optimized website and allow poeple to update their timezone. Better yet - use Zapier to take Foursquare check-ins and push the timezone to `/api/time/user/joe` to update my timezone!
