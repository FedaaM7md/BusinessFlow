# Roadmap & honest gaps — BusinessFlow

This build covers the release 1.0 functional brief plus the release 1.1 identity, demo-mode,
security and ownership upgrade. The following items are **not implemented**, or
are deliberately limited, so the application is not mistaken for a production
ERP.

## Deliberately out of scope for this demo

1. **Server-side PDF generation.** Invoices and quotations have a dedicated
   printable A4 view with mixed RTL/LTR handling; PDF is produced through the
   browser’s Print → Save as PDF. No GPL/LGPL PDF library was added.
2. **Production operations.** No CI/CD, backups, WAF, malware scanning, SMTP
   delivery, queue supervision, log retention or disaster recovery.
3. **Payment reversal / refunds.** Recorded payments are immutable for audit
   integrity. There is no refund, reversal or credit-note document.
4. **Full accounting.** No general ledger, chart of accounts, journal entries,
   trial balance, VAT return, bank reconciliation or e-invoicing.
5. **Purchasing cycle.** Suppliers and expenses exist, but there are no purchase
   orders, goods receipts, supplier bills or three-way matching.
6. **Multi-company / multi-branch tenancy.** One company profile and a shared
   database; warehouses exist but not separate legal entities.
7. **Custom role creation.** Four seeded roles with an editable matrix; arbitrary
   roles and per-user overrides are not implemented.
8. **Advanced inventory.** No lots, serials, expiry, barcode, reorder automation,
   landed costs or FIFO/moving-average valuation. Valuation uses stored cost.
9. **Automatic quotation expiry.** Marked manually; no scheduled expiry job.
10. **Multi-currency transactions.** One configured currency (EGP default) with
    integer minor units; no FX rates.
11. **Email/SMS/in-app notifications.** No email verification, invitations,
    overdue reminders or two-factor authentication. Password reset uses the log
    mailer in the demo.
12. **API & mobile clients.** Server-rendered Blade only; no public REST/GraphQL.
13. **Document attachments.** Logo upload only.
14. **Dashboard builder.** Charts are lightweight CSS/SVG; no drill-down builder.
15. **Partial returns / credit notes.** Invoices can be cancelled only before any
    payment is recorded.
16. **Activity-log retention/export.** Filterable in the UI; no archival policy.
17. **Localization beyond Arabic and English.** Two complete locales.

## release 1.1-specific limits

18. **Shared demo dataset.** There is no per-visitor isolation; the demo is a
    shared workspace that resets hourly via `demo:reset`. `demo:reset` recreates
    the database, so an in-flight demo session may be signed out at reset.
19. **Demo names are Arabic-first.** Single-name entities (customers, suppliers,
    warehouses) and invoice line descriptions use Arabic names; the English UI
    shows the same names. Products remain bilingual via `name`/`name_ar`.
20. **Screenshots** now cover login and authenticated list/form pages in Arabic
    and English at mobile and desktop sizes. An automated visual-diff suite is
    not yet in place.
21. **Accessibility not formally audited.** Semantic labels, focus rings, skip
    link and keyboard navigation are present, but no WCAG audit or screen-reader
    pass has been completed.

## Recommended next milestones

1. Add per-visitor demo isolation (scoped tenant/sandbox) instead of a shared
   reset dataset.
2. Add credit notes, payment reversals and a general-ledger foundation.
3. Introduce purchase orders and supplier bills.
4. Add two-factor authentication and email verification.
5. Add a server-side PDF renderer with properly shaped Arabic (licence review).
6. Add backup/restore, deployment hardening and monitoring.
7. Build an API layer for integrations and mobile use.
8. Add document attachments, notifications and scheduled reminders.
9. Complete an external security review and WCAG audit before production use.
