# What's new in August 2020?

What's New? returns following a hiatus due to the Coronavirus COVID-19 pandemic closing all aqautics clubs.

Welcome to the latest **What's new** digest for the SCDS Membership Software. There is a publication every month detailing new features and changes.

* If you're a member of club staff, you'll be able to choose how to deliver training on new features that are referenced here.
* If you're a keen parent/club member, these publications will let you know what we're doing to improve the membership system.

We're always looking for feedback. You can contact us via [feedback@myswimmingclub.uk](mailto:feedback@myswimmingclub.uk).

You can also follow us on Twitter for the latest news [@myswimmingclub.uk](https://twitter.com/myswimmingclub).

## COVID-19 Tools

To support clubs due the COVID-19 pandemic, which will last indefinitely, a number of COVID-19 tools have been added to the membership software.

### Contact Tracing

Our new contact tracing tools allow anybody to check in at club locations, whether they are a coach, member, parent or guest.

Check in can be performed by individuals or by COVID Liasons (Squad Reps).

Addition COVID-19 Contact Tracing features include;

* Fast QR code posters generated automatically for each location,
* Fast and flexible contact details report generation,
* Sign out tools, saved and shared with other users in real time

### Health Screening Survey

We've added full support for the Swim England Health Screening Survey. Members can log in and complete a survey, with fields shown to them as required by their inputs.

Club staff can then approve or reject a member's survey. Survey's can also be voided, if for example a member contracts or comes into close contact with COVID-19.

### Risk Awareness Form*

Similarly, there is full support for the Swim England COVID-19 Risk Awareness Form. Members agree to this declaration the same way, but no club approval is required.

\* (Also known as *Sport Sheffield Return to Training Form* for UoSSWPC users)

## Redesigned Registers

Attendance registers have been redesigned from the ground up to support;

* Real-time saving (via AJAX),
* Live views of changes to registers (via WebSockets with socket.io),
* Automated register generation, ahead of time with current squad lists
* Sessions can now (behind the scenes) cover multiple squads, with front end support for adding multi-squad sessions coming soon.

Registers are no longer tied directly to squad membership and going forward this will allow registers to also support one-off events and registers for gala sessions.

Additional contextual information has been added, including emergency contact details in addition to the existing medical and notes forms.

COVID-19 Health Survey and Risk Awarenss Form data is also being displayed while the COVID-19 pandemic continues.

We also now support an *excused* state on registers, for when a swimmer is not there, but non-attendance should not count negatively towards attendance percentages and other stats.

## Other changes

If you're a user with multiple roles, e.g. Parent and Admin, you'll now see links on Error 404 pages to reload the page with a different account role. These problems can occur if you follow a link from outside the system and cannot view it with your current role.

Clubs can now disable notify for squad reps and increased warnings against the use of *Force Send* in *Notify* are now displayed.