# fullcalendar-php-example

A small/preview project on what the https://fullcalendar.io/ is able to do.

Short description :

When visitor is not logged-in, view full calendar, with read only permissions. 

![plain user](http://www.pipiscrew.com/wp-content/uploads/2016/09/fcalendar2.jpg)

When admin logged-in (login.php) 

![admin](http://www.pipiscrew.com/wp-content/uploads/2016/09/fcalendar1.jpg)

has the :

**point3** – add worker

**point2** – drag the workers to calendar, added from point3 (saved to local sqlite)

**point4** - can move the event(s).

**point1** – anchor drives to login.php / when logged-in drives to logout.php


the application runs on index.php, all workers are available to view the calendar.

when a worker dropped to a date this modal appears 

![event type](http://www.pipiscrew.com/wp-content/uploads/2016/09/fcalendar3.png)

and is one other, when the draggable events r-clicked!




-keep in mind when admin logged-in, there is a 24h validation + 3 login attempts!

ps to create the admin, you have to unrem the lines at login.php once, the rem again!!


___



**sqlite** doesnt have **date** field type, using **yyyy/mm/dd** on TEXT field type, we are able to execute a query (get_events.php) like :
```sql
select * from days_off 
left join users on users.user_id=days_off.user_id
where date_occur between '2016-09-23' and '2016-09-29'
```
more at https://www.sqlite.org/lang_datefunc.html
