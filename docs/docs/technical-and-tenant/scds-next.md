# SCDS Next

SCDS Next is the new version of the SCDS Membership software. SCDS Next is built on the Laravel framework with a React powered user interface.

We'll slowly migrate features to the new platform over time. This means that for a while we'll be running new and old parts of the system side by side.

## Technology

The main reason we're building SCDS Next is to allow us to move to newer technology using a solid underlying web framework and a much more user friendly user interface while removing technical debt.

The original version of the SCDS Membership system is built upon a number of libraries which are no longer supported. The new Laravel framework meanwhile is much more widely used and supported, with a good long-term feature and support roadmap.

## New features

Moving to this new framework will help us roll out new features much more quickly, with more robust software testing.

The new features we're focussing on intially include,

* Payments
  * A unified solution across all payment methods (BACS Direct Debit, Credit/Debit cards and more)
  * GoCardless removal
  * Guest payments
* Competitions
  * Support for custom events
  * Event ordering
  * Eligibilty criteria
  * External entries

## A new look

We're ditching the [Bootstrap CSS framework](https://getbootstrap.com/) we've used since launch for a more custom approach built on [Tailwind CSS](https://tailwindcss.com/). As we're running new and old side by side, you'll notice two different looks - we're working hard to migrate features to the new system once we're done with payments and competitions.

SCDS Next uses the ReactJS framework from Meta, so is dependent on JavaScript in a modern browser.

## Better documentation

We're well aware of a need to improve the documentation available around the membership system. This page is part of a new documentation system that we're rolling out alongside SCDS Next. The new documentation system is powered by [Docusaurus](https://docusaurus.io), which is made by Meta.

We'll be working hard to ensure we document all our new features in these help pages as we add them.