<?php

return [
    'about' => [
        'title' => 'About BusinessFlow',
        'intro' => 'BusinessFlow is a bilingual ledger for invoices, stock, customers and cash, built for Egyptian small and medium businesses.',
        'story_title' => 'Why we built it',
        'story' => 'Most small teams run on spreadsheets and chat messages. BusinessFlow puts quotations, invoices, payments, stock and expenses in one place, in Arabic and English, with money stored as whole piasters so totals always reconcile.',
        'values_title' => 'What matters to us',
        'values' => [
            'Clear numbers: integer money, per-line tax and an auditable stock ledger.',
            'Arabic first: a real RTL interface, not a translated afterthought.',
            'Quiet software: no noise, no dark patterns, no hidden fees.',
        ],
        'contact_title' => 'Talk to us',
        'contact' => 'For a walkthrough or a question, email hello@businessflow.example or call 01000000000 (demo line).',
        'faq_title' => 'Common questions',
        'faq' => [
            ['q' => 'Is this a real product?', 'a' => 'This is a demonstration build with sample data. It shows the full product experience; do not enter real business data.'],
            ['q' => 'Does it work in Arabic?', 'a' => 'Yes. Arabic RTL and English LTR are both first-class, with a language switch on every page.'],
            ['q' => 'Can I try it?', 'a' => 'Use the demo entry on the sign-in page to open a role without a password.'],
        ],
        'changelog_title' => 'Changelog',
        'changelog' => 'v1.0 — initial release: bilingual ledger, quotations, invoices, payments, expenses, inventory and reports.',
    ],
    'privacy' => [
        'title' => 'Privacy policy',
        'updated' => 'Last updated: November 2026',
        'intro' => 'This policy explains what this demo stores and how it is handled. It is written for an Egyptian context and is not legal advice.',
        'sections' => [
            ['title' => 'What we collect', 'body' => 'In this demo we store the business records you create (customers, invoices, stock, expenses) and the account details you sign in with. Demo data is sample data.'],
            ['title' => 'How we use it', 'body' => 'Data is used only to run the demo workspace: showing lists, totals, reports and the activity log. We do not sell data or use it for advertising.'],
            ['title' => 'Cookies and sessions', 'body' => 'A session cookie keeps you signed in. It is HttpOnly and SameSite=Lax. No analytics or tracking cookies are used.'],
            ['title' => 'Retention', 'body' => 'The shared demo resets on a schedule. Anything you enter may be removed when the demo is reset.'],
            ['title' => 'Your rights', 'body' => 'Under Egyptian data-protection practice you can ask what is stored and request correction or deletion. Contact hello@businessflow.example.'],
            ['title' => 'Contact', 'body' => 'Questions about privacy: hello@businessflow.example.'],
        ],
    ],
    'terms' => [
        'title' => 'Terms of use',
        'updated' => 'Last updated: November 2026',
        'intro' => 'These terms govern use of this demonstration build. By using it you accept them.',
        'sections' => [
            ['title' => 'Demo use only', 'body' => 'This build is for evaluation. Do not enter real customer, financial or personal data. It is provided as-is, without warranties.'],
            ['title' => 'Accounts', 'body' => 'Demo accounts are shared and may be reset. Do not rely on them for real work.'],
            ['title' => 'Acceptable use', 'body' => 'Do not attempt to disrupt the service, access other users’ data, or upload unlawful content.'],
            ['title' => 'Intellectual property', 'body' => 'The BusinessFlow name, design and code are proprietary. See the repository licence for details.'],
            ['title' => 'Governing law', 'body' => 'These terms are governed by the laws of the Arab Republic of Egypt.'],
            ['title' => 'Contact', 'body' => 'Questions about these terms: hello@businessflow.example.'],
        ],
    ],
    'meta' => [
        'about' => 'About BusinessFlow, a bilingual ledger for Egyptian businesses.',
        'privacy' => 'How this BusinessFlow demo handles data, cookies and retention.',
        'terms' => 'Terms of use for the BusinessFlow demonstration build.',
    ],
];
