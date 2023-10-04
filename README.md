# lytix\_activity

This module was designed specifically for students. Its primary goal is to offer students a comprehensive overview of the time they've spent in the course. This module is accompanied by a counterpart for lecturers: lytix_timeoverview.

Students can view their time spent in two formats:

- Bar Chart
- Line Chart

The time data is aggregated per Moodle activity. Anything not related to a specific activity (like quizzes, H5P videos, or BigBlueButton sessions) is categorized as "Course Time". The line chart offers a more granular view, allowing students to see individual activity times on a day-to-day basis. Additionally, students have the option to view the median time spent by their peers.

Data Aggregation

The data is aggregated using a scheduled task. This task retrieves all student interactions (clicks) for the course from a log table in the database and calculates the 