# Security Policy

## Reporting a vulnerability

If you find a security issue in PDFPost, please report it privately through
[GitHub's private vulnerability reporting](https://github.com/andyshrx/pdfpost/security/advisories/new)
on this repository. Please do not open a public issue for security problems.

I read every report and will get back to you as fast as I can, usually within a few
days. If the issue is confirmed I'll ship a fix and credit you in the release notes,
unless you'd rather stay anonymous.

## Supported versions

Only the latest release gets security fixes.

## Scope notes

PDFPost is single tenant by design, template authors are trusted operators. Reports
about what an authenticated operator can do to their own instance are usually not
security issues. The interesting surface is the API, the webhooks, the signed artifact
URLs, and the Gotenberg sandbox.
