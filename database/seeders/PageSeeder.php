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
        $this->seedInstallAndroid();
        $this->seedInstallWindows();
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

    /**
     * All-sales-final policy for digital goods, per the owner's direction.
     * Kept to a handful of narrow, standard exceptions (duplicate/erroneous
     * charges, non-delivery, unauthorized transactions) rather than an
     * absolute policy with no carve-outs at all — a blanket "no refunds,
     * no exceptions, ever" clause is the kind of term that gets read out
     * of a contract by a court or overridden by non-waivable consumer
     * protection law anyway, so it buys nothing and only makes the page
     * look untrustworthy. This is still a starting point, not a substitute
     * for review by a lawyer licensed in the owner's jurisdiction.
     */
    private function seedRefunds(): void
    {
        $page = Page::updateOrCreate(
            ['slug' => 'refunds'],
            [
                'title' => 'Refund Policy',
                'meta_title' => 'Refund Policy — Niagara Inde Apps',
                'meta_description' => 'All sales of digital products through Niagara Inde Apps are final. Read the full refund policy, including the limited exceptions that apply.',
                'published' => true,
            ]
        );

        $sections = [
            [
                'heading' => 'All Sales Are Final',
                'body' => <<<'MD'
All products sold through Niagara Inde Apps — including every application, edition, license, and add-on purchased on this website — are digital products delivered electronically. Except as expressly set out under "Limited Exceptions" below, all sales are final and no refunds, credits, or exchanges will be issued for any reason, including but not limited to a change of mind, a purchase made in error by the customer, incompatibility with a device or operating system that was disclosed on the product's page prior to purchase, or dissatisfaction with the product's features or performance.

By completing a purchase, you acknowledge and agree that: (a) you have reviewed the product description, screenshots, system requirements, and any available demo prior to purchase; (b) a license, download, or access credential is granted to you immediately or shortly after payment is confirmed; and (c) this immediate grant of access is the reason no statutory "cooling-off" or withdrawal period applies to digital goods delivered in this manner, to the fullest extent permitted by applicable law.
MD,
            ],
            [
                'heading' => 'Why Digital Products Are Non-Refundable',
                'body' => "Unlike physical merchandise, a digital product cannot be returned once it has been downloaded, installed, or otherwise accessed. Niagara Inde Apps has no practical or reliable means of confirming that a purchaser has deleted every copy of a delivered file or has stopped using a granted license. This policy exists to protect the business against the unrecoverable loss that results from the duplication and continued use of digital goods after a refund, and applies uniformly to every customer regardless of platform, edition, or purchase price.",
            ],
            [
                'heading' => 'Limited Exceptions',
                'body' => <<<'MD'
Niagara Inde Apps will review a request, at its sole discretion, only in the following narrow circumstances:

1. Duplicate or erroneous charge — you were charged more than once for the same order, or the amount charged does not match the price displayed at checkout.
2. Non-delivery — payment was confirmed but no working license, download, or access credential was ever provided, and the issue could not be resolved through ordinary support.
3. Unauthorized transaction — the purchase was made fraudulently, without your authorization, using your payment method.
4. Non-waivable statutory right — a refund, replacement, or other remedy is required by consumer protection legislation that cannot lawfully be excluded by this policy in your jurisdiction.

Approval of a request under any of these exceptions is at the sole discretion of Niagara Inde Apps and does not obligate a refund in any other case, past or future. Where a refund is issued, any license or download access granted for that order will be revoked or deactivated as of the refund date.
MD,
            ],
            [
                'heading' => 'How to Report a Billing Issue',
                'body' => "If you believe one of the limited exceptions above applies to your order, contact us through the Contact page within 14 days of the original purchase date. Include your order number, the app and edition purchased, the email address used at checkout, and a description of the issue. Requests submitted outside this window, or that do not fall within a listed exception, will not be considered.",
            ],
            [
                'heading' => 'Processing an Approved Exception',
                'body' => "Where a refund is approved under one of the exceptions above, it is issued to the original payment method through Stripe. Processing times beyond that point are determined by your card issuer or bank and are outside our control.",
            ],
            [
                'heading' => 'Governing Law',
                'body' => "This policy is governed by the laws of the Province of Ontario and the federal laws of Canada applicable therein, without regard to conflict-of-law principles, except to the extent a mandatory consumer-protection law of the jurisdiction where you reside grants you additional non-waivable rights, in which case this policy applies only to the extent consistent with those rights.",
            ],
        ];

        foreach ($sections as $index => $section) {
            $page->sections()->updateOrCreate(
                ['heading' => $section['heading']],
                ['sort_order' => $index, 'body' => $section['body']]
            );
        }

        // Prune the old draft-era sections (different headings entirely —
        // "Draft — owner review required" etc.) that updateOrCreate above
        // wouldn't otherwise touch or remove.
        $page->sections()->whereNotIn('heading', array_column($sections, 'heading'))->delete();
    }

    /**
     * §31/§32 — installation help for a direct Android APK purchase.
     */
    private function seedInstallAndroid(): void
    {
        $page = Page::updateOrCreate(
            ['slug' => 'install-android'],
            [
                'title' => 'Installing on Android',
                'meta_title' => 'Installing Niagara Inde Apps software on Android',
                'meta_description' => 'How to install an Android app purchased directly from Niagara Inde Apps.',
                'published' => true,
            ]
        );

        $sections = [
            [
                'heading' => 'Direct Android Download',
                'body' => "This Android app is purchased and downloaded directly from Niagara Inde Apps rather than through Google Play. Android may ask you to allow installation from this source when installing the app.",
            ],
            [
                'heading' => 'Installing your app',
                'body' => "1. Download the Android version from My Apps.\n2. Open the downloaded file.\n3. Android may request permission to install apps from the browser or file manager you're using.\n4. Follow Android's security prompt.\n5. Install the Niagara Inde Apps application.\n6. You can disable the temporary \"install unknown apps\" permission afterward if you'd like.\n\nExact wording and screens vary by Android version and device.",
            ],
            [
                'heading' => 'A note on Android\'s security prompt',
                'body' => "This prompt is a normal, expected part of installing an app from outside Google Play — it isn't an error, and it doesn't mean anything is wrong with the app. Only install Niagara Inde Apps software obtained from our official website.",
            ],
        ];

        foreach ($sections as $index => $section) {
            $page->sections()->updateOrCreate(
                ['heading' => $section['heading']],
                ['sort_order' => $index, 'body' => $section['body']]
            );
        }
    }

    /**
     * §31/§33 — installation help for a direct Windows installer purchase.
     */
    private function seedInstallWindows(): void
    {
        $page = Page::updateOrCreate(
            ['slug' => 'install-windows'],
            [
                'title' => 'Installing on Windows',
                'meta_title' => 'Installing Niagara Inde Apps software on Windows',
                'meta_description' => 'How to install a Windows app purchased directly from Niagara Inde Apps.',
                'published' => true,
            ]
        );

        $sections = [
            [
                'heading' => 'Direct Windows Download',
                'body' => "This Windows app is purchased and downloaded directly from Niagara Inde Apps. Depending on the installer format and your Windows security settings, you may see a publisher/security notice when you first run it.",
            ],
            [
                'heading' => 'Installing your app',
                'body' => "1. Download the Windows version from My Apps.\n2. Open the downloaded installer.\n3. If Windows SmartScreen shows a publisher notice, review it — this is normal for independently distributed software and is explained accurately here rather than something to bypass blindly.\n4. Follow the installer's prompts to finish installing.\n\nOnly install Niagara Inde Apps software obtained from our official website. The production application is signed when our code-signing infrastructure is in place.",
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
