# What's new in February 2020?

Welcome to the first **What's new** digest for the SCDS Membership Software. Going forward, there will be a publication every month detailing new features and changes.

* If you're a member of club staff, you'll be able to choose how to deliver training on new features that are referenced here.
* If you're a keen parent/club member, these publications will let you know what we're doing to improve the membership system.

We're always looking for feedback. You can contact us via [feedback@myswimmingclub.uk](mailto:feedback@myswimmingclub.uk).

You can also follow us on Twitter for the latest news [@myswimmingclub.uk](https://twitter.com/myswimmingclub).

## Email attachments in Notify

We've introduced support for email attachments in the Notify composer. This was a common feature request from several customer clubs.

You can attach

* 10MB worth of files in total
* Each up to 3MB in size

A copy of every attachment is stored on our servers, available for those with sending permissions to view via the *Message History* page.

Normal users can access attachments from emails sent to them via *Your Message History* in my account.

## Photography permissions reports

Club admins can now export a Photography Permissions Report (CSV) via Admin -> Reports.

These show all members and which categories they have agreed to photography permissions for, if applicable.

A printable Photography Permissions Report (PDF) is now also generated for all galas, showing just the members entered into a specific gala.

These can be printed out and taken to a gala for use by club staff to quickly check permissions if, for example, they are regularly posting to social media during a gala.

## Upgradeable members report

The system now generates a report of members who are 'upgradeable'. These are members who will be nine or over next year and are currently Category 1 Swim England members.

This allows clubs to easily review members who will need to move to a Category 2 SE membership in the *next or current* membership year.

## Allow staff to resend registration emails

Occasionally, registration emails don't reach end users either because the email fails to be delivered or the email address is entered incorrectly.

Club staff are now able to navigate to the page for any user and, if they have not yet completed registration, can resend the registration email, correcting user details beforehand if required.

## Create payment items for GoCardless UI created payments

We now create a payment item if a payment is created via the GoCardless UI allowing the payment to be tracked in payout reports, where previously no information could be displayed.

## Update to TinyMCE 5

We've upgraded the notify composer from TinyMCE 4 to TinyMCE 5. This delivers user experience improvements for our notify users.

**This change was reverted on Friday 21 February following user complaints of slow load times for the TinyMCE textarea. We have also compiled all TinyMCE plugins into a single JS file to dramatically reduce the time taken for HTTP requests.**

## Improved colour styling for customer clubs

We've improved the header at the top of each page so a club's custom colour is also applied to the menu at the top of each page. We've also added code to dynamically adjust the text contrast on these coloured backgrounds.

## Home Nations Meet Choice/Country of International Representation Choice

Members can pick their country of international representation. This includes a full list of ISO countries, with GB removed and replaced with the four Home Nations for Home Nations Meet Choice. For Home Nations, CoIR is GB, though NIR members have the right to choose Northern Ireland (for GB) or the Republic of Ireland.

The general list of countries is linked to a third party library which gets updated should any countries appear/disappear.