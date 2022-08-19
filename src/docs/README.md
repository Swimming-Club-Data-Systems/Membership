# Membership
Membership is a project from [Chester-le-Street ASC](https://www.chesterlestreetasc.co.uk/) and [Swimming Club Data Systems](https://www.myswimmingclub.uk/) which aims to make swimming club
management simpler for club volunteers, coaches, helpers and members. It is available under the Apache open-source license, so you are free to use our code in any way possible.

## Documentation - Help and Support

You can find documentation here on our docs repository. Other documentation is available on the Chester-le-Street ASC Website. It will eventually be migrated here.

* [Log books](log-books)
* [Other categories](https://www.chesterlestreetasc.co.uk/support/onlinemembership/)
* [What's new?](whats-new)

## About

This software is continuously developed in accordance with the business and operational needs of Chester-le-Street ASC and SCDS customer clubs, with a steady stream of feature requests from all.

Chester-le-Street ASC and SCDS accept no liability for any issues, including all legal issues, with this software.

The system requires at least PHP 7.4 and for JavaScipt to be enabled on clients.

## Features
### Automatic Member Management
The application is built on a database of club members. Members are assigned to squads and parents can link members to their account. This allows us to automatically calculate monthly fees and more.

### Online Gala Entries
Galas are added to the system by admins. Parents can enter their children into swims by selecting their name, gala and swims. This cuts down on duplicated data from existing arrangements. Parents recieve emails detailing their entries and can then edit entries up to the closing date or when the entry is processed.

### Online Attendance Records
Attendance records are online, facilitating automatic attendance calculation. Squads are managed online and swimmer moves between squads can be scheduled in the system to be carried out automatically.

### Notify
Notify is our E-Mail and SMS mailing list solution. Administrators can send emails to selected groups of parents for each squad. The system is GDPR compliant and users can opt in or out of receiving emails at any time.

### Direct Debit Payments
This application has been integrated with GoCardless and their APIs to allow customer clubs to bill members by Direct Debit. The GoCardless client library which is included in this software is copyright of GoCardless.

### Online Membership Renewal and Registration
We're able to walk parents/members through the annual renewal process, including checking their details, updating details for their swimmers such as medical information and photography permissions as well as agreeing to the club code of conduct and terms and conditions. At the end of the process, we charge users
their Swim England Fees and any club fees by Direct Debit.

### Credit and Debit Card Payments
We support credit and debit card payments for gala entries via an integration with Stripe. Refunds for rejections can also be made via the system. We have plans to introduce card payments in more locations soon.

## Legal Notices for Third Party Libraries

This application contains third party client libraries. These are managed via Composer. They will come with the application, so we recommend that you do not update them via composer yourself, as this may cause issues.

### Included Packages

For a list of included packages, view [DEPENDENCIES.md](./DEPENDENCIES.md).

This product includes GeoLite2 data created by MaxMind, available from [http://www.maxmind.com](http://www.maxmind.com).
