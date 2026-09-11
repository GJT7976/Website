<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Are the live demos safe to try?',
                'answer' => "Yes. Every demo uses sample/fake data only, is isolated from any real customer data, and never charges a real payment card. Look for the \"Demo Mode\" indicator on any demo page.",
                'category' => 'Demos',
            ],
            [
                'question' => 'Do your apps work offline?',
                'answer' => 'Where it makes sense for the app, yes — several are built to work offline or as installable Progressive Web Apps (PWAs). Each app\'s own page lists its supported platforms and requirements.',
                'category' => 'General',
            ],
            [
                'question' => 'What payment methods do you accept?',
                'answer' => 'Direct purchases are processed securely through Stripe. Card details are never stored on this website.',
                'category' => 'Purchasing',
            ],
            [
                'question' => 'How do I get support for an app I bought?',
                'answer' => 'Visit the Support page, or use the Contact form and select the relevant app.',
                'category' => 'Support',
            ],
        ];

        foreach ($faqs as $index => $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                $faq + ['app_id' => null, 'sort_order' => $index, 'published' => true]
            );
        }
    }
}
