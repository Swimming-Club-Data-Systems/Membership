# Custom Hostnames

You can use your own domain name with the membership system. This costs an additional £2 per month, which will be billed automatically with your monthly invoice.

## Getting started

To request to use a custom hostname, contact [support@myswimmingclub.uk](mailto:support@myswimmingclub.uk) with;

- Your tenant name
- Your desired custom domain

## What domains can I use?

You can use any **subdomain** of a domain name that you control. In most circumstances, you cannot use your apex domain.

Example domain names you could choose include

- membership.yourclubname.co.uk
- account.yourclubname.co.uk

## What do I need to do?

You will need access to create `TXT` and `CNAME` records on your domain. This is required to verify ownership and issue appropriate TLS/SSL certificates.

The `CNAME` for your custom domain should point to `custom-domain.myswimmingclub.uk`.

The values of your TXT records will be unique for you so these will be emailed out to you when you request your custom domain.

## Certificate Authority Authorization (CAA) records

CAA is a new DNS resource record type defined in RFC 6844
that allows a domain owner to indicate which CAs are allowed to issue certificates for them. If your customer has CAA records set on their domain, they will either need to add the following (or remove CAA entirely):

```
example.com. IN CAA 0 issue "digicert.com"
example.com. IN CAA 0 issue "letsencrypt.org"
```

While it’s possible for CAA records to be set on the subdomain you wish to use with the membership system, it is unlikely. You would also have to remove this CAA record.