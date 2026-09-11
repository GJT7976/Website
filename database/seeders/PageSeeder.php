<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAbout();
        $this->seedPrivacy();
        $this->seedTerms();
        $this->seedRefunds();
    }

    private function seedAbout(): void
    {
        $page = Page::updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'About Niagara Inde Apps',
                'meta_title' => 'About Niagara Inde Apps',
                'meta_description' => 'Niagara Inde Apps is an independent Canadian app-development business based in Niagara, Ontario, building practical software for small businesses and everyday users.',
                'published' => true,
            ]
        );

        $sections = [
            [
                'heading' => 'Independent, and built in Niagara',
                'body' => "Niagara Inde Apps is an independent Canadian app-development business based in Niagara, Ontario. The goal is simple: build practical, easy-to-use software for small businesses, independent creators, and everyday users — without unnecessary complexity.",
            ],
            [
                'heading' => 'What we build',
                'body' => "Every application here is built to be genuinely useful first. That means real-world conditions are taken seriously: offline capability where it matters, sensible pricing, and software that respects the people using it rather than locking them into more than they need.",
            ],
            [
                'heading' => 'Privacy-conscious by default',
                'body' => "Collecting the least amount of data required, avoiding advertising trackers, and never selling customer information are starting points, not afterthoughts. Each app's own privacy information is available on its page.",
            ],
            [
                'heading' => 'Local roots, global ideas',
                'body' => "Built in the Niagara region, with an eye toward small businesses and independent operators anywhere — retail shops, cafés, restaurants, market vendors, hobby farms, food trucks, trades, and home businesses alike.",
            ],
        ];

        foreach ($sections as $index => $section) {
            $page->sections()->updateOrCreate(
                ['heading' => $section['heading']],
                ['sort_order' => $index, 'body' => $section['body']]
            );
        }
    }

    private function seedPrivacy(): void
    {
        $page = Page::updateOrCreate(
            ['slug' => 'privacy'],
            [
                'title' => 'Privacy Policy',
                'meta_title' => 'Privacy Policy — Niagara Inde Apps',
                'meta_description' => 'How Niagara Inde Apps collects, uses, and protects information.',
                'published' => true,
            ]
        );

        $sections = [
            [
                'heading' => 'Draft — owner/legal review required',
                'body' => "This page is a starting template, not a finished legal document. It must be reviewed (and, where appropriate, confirmed with a qualified professional) before the site goes live, so it accurately reflects what data this business actually collects, how Stripe and any other processors are used, and current Canadian privacy law (including PIPEDA).",
            ],
            [
                'heading' => 'Information we collect',
                'body' => "We collect the minimum information needed to operate this website and process purchases: contact form submissions, and — when you buy an app directly — the billing information needed to complete the transaction and calculate applicable tax. We do not use advertising trackers by default.",
            ],
            [
                'heading' => 'Payment information',
                'body' => "Card payment details are handled directly by Stripe, our payment processor. This site does not store complete card numbers.",
            ],
            [
                'heading' => 'How information is used',
                'body' => "Information is used to fulfil orders, respond to support requests, and keep accounting records required for tax purposes. It is not sold to third parties.",
            ],
            [
                'heading' => 'Contact',
                'body' => "Questions about this policy can be sent through the Contact page.",
            ],
        ];

        foreach ($sections as $index => $section) {
            $page->sections()->updateOrCreate(
                ['heading' => $section['heading']],
                ['sort_order' => $index, 'body' => $section['body']]
            );
        }
    }

    private function seedTerms(): void
    {
        $page = Page::updateOrCreate(
            ['slug' => 'terms'],
            [
                'title' => 'Terms of Use & Software Purchase Terms',
                'meta_title' => 'Terms — Niagara Inde Apps',
                'meta_description' => 'Terms of use for the Niagara Inde Apps website, and purchase terms for applications sold here.',
                'published' => true,
            ]
        );

        $sections = [
            [
                'heading' => 'Draft — owner/legal review required',
                'body' => "This page is a starting template, not a finished legal document. It must be reviewed before the site goes live.",
            ],
            [
                'heading' => 'Use of this website',
                'body' => "This website is provided by Niagara Inde Apps to showcase, demonstrate, and sell software applications. By using it, you agree to use it lawfully and not to misuse demo environments, which contain sample data only.",
            ],
            [
                'heading' => 'Software purchases',
                'body' => "Applications sold on this site are licensed for use as described on each application's own page. Specific licensing terms for each app are documented on that app's page where applicable.",
            ],
            [
                'heading' => 'Live demos',
                'body' => "Demo environments use sample/fake data only, are isolated from any production data, and may be reset or unavailable at times. Demos never charge real payment cards.",
            ],
        ];

        foreach ($sections as $index => $section) {
            $page->sections()->updateOrCreate(
                ['heading' => $section['heading']],
                ['sort_order' => $index, 'body' => $section['body']]
            );
        }
    }

    private function seedRefunds(): void
    {
        $page = Page::updateOrCreate(
            ['slug' => 'refunds'],
            [
                'title' => 'Refund Policy',
                'meta_title' => 'Refund Policy — Niagara Inde Apps',
                'meta_description' => 'Refund policy for application purchases made on the Niagara Inde Apps website.',
                'published' => true,
            ]
        );

        $sections = [
            [
                'heading' => 'Draft — owner review required',
                'body' => "This page is a starting template. Actual refund terms (time window, eligible circumstances, process) must be set by the owner before the site goes live and before direct purchasing is enabled.",
            ],
            [
                'heading' => 'How to request a refund',
                'body' => "Refund requests can be made through the Contact page. Include your order number and the app purchased.",
            ],
            [
                'heading' => 'Processing',
                'body' => "Approved refunds are processed back to the original payment method through Stripe. Processing times depend on your card issuer or bank.",
            ],
        ];

        foreach ($sections as $index => $section) {
            $page->sections()->updateOrCreate(
                ['heading' => $section['heading']],
                ['sort_order' => $index, 'body' => $section['body']]
            );
        }
    }
}
