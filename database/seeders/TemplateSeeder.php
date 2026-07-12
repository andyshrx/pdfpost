<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * A small gallery of ready to use templates. Safe to re-run, existing
     * templates are left alone.
     */
    public function run(): void
    {
        foreach ($this->templates() as $definition) {
            $template = Template::firstOrCreate(
                ['slug' => $definition['slug']],
                ['name' => $definition['name'], 'description' => $definition['description']],
            );

            if ($template->versions()->doesntExist()) {
                $template->publishNewVersion($definition['source'], $definition['sample']);
            }
        }
    }

    protected function templates(): array
    {
        return [
            [
                'slug' => 'invoice',
                'name' => 'Invoice',
                'description' => 'A clean invoice with line items and totals',
                'sample' => [
                    'number' => 'INV-0042',
                    'date' => 'July 12, 2026',
                    'from' => ['name' => 'Studio North', 'email' => 'billing@studionorth.test'],
                    'to' => ['name' => 'Acme Co', 'email' => 'accounts@acme.test'],
                    'items' => [
                        ['name' => 'Design sprint', 'qty' => '1', 'price' => '$1,200.00'],
                        ['name' => 'Development (10h)', 'qty' => '10', 'price' => '$1,500.00'],
                    ],
                    'total' => '$2,700.00',
                    'notes' => 'Payment due within 14 days. Thank you!',
                ],
                'source' => <<<'LIQUID'
<html>
<body style="font-family: -apple-system, 'Segoe UI', sans-serif; color: #18181b; margin: 0; padding: 48px;">
  <table style="width: 100%; border-collapse: collapse; margin-bottom: 40px;">
    <tr>
      <td style="font-size: 28px; font-weight: 700;">INVOICE</td>
      <td style="text-align: right; color: #71717a;">{{ number }}<br>{{ date }}</td>
    </tr>
  </table>
  <table style="width: 100%; border-collapse: collapse; margin-bottom: 32px; font-size: 14px;">
    <tr>
      <td style="color: #71717a; padding-bottom: 4px;">From</td>
      <td style="color: #71717a; padding-bottom: 4px;">Billed to</td>
    </tr>
    <tr>
      <td style="font-weight: 600;">{{ from.name }}<br><span style="font-weight: 400; color: #52525b;">{{ from.email }}</span></td>
      <td style="font-weight: 600;">{{ to.name }}<br><span style="font-weight: 400; color: #52525b;">{{ to.email }}</span></td>
    </tr>
  </table>
  <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
    <tr style="text-align: left; border-bottom: 2px solid #18181b;">
      <th style="padding: 8px 0;">Item</th>
      <th style="padding: 8px 0; text-align: center; width: 60px;">Qty</th>
      <th style="padding: 8px 0; text-align: right; width: 120px;">Price</th>
    </tr>
    {% for item in items %}
    <tr style="border-bottom: 1px solid #e4e4e7;">
      <td style="padding: 10px 0;">{{ item.name }}</td>
      <td style="padding: 10px 0; text-align: center;">{{ item.qty }}</td>
      <td style="padding: 10px 0; text-align: right;">{{ item.price }}</td>
    </tr>
    {% endfor %}
    <tr>
      <td></td>
      <td style="padding: 16px 0; font-weight: 700; text-align: center;">Total</td>
      <td style="padding: 16px 0; font-weight: 700; text-align: right; font-size: 18px;">{{ total }}</td>
    </tr>
  </table>
  <p style="color: #71717a; font-size: 13px; margin-top: 40px;">{{ notes }}</p>
</body>
</html>
LIQUID,
            ],
            [
                'slug' => 'receipt',
                'name' => 'Receipt',
                'description' => 'A compact payment receipt',
                'sample' => [
                    'store' => 'Corner Coffee',
                    'date' => 'July 12, 2026 09:41',
                    'items' => [
                        ['name' => 'Flat white', 'price' => '$5.50'],
                        ['name' => 'Almond croissant', 'price' => '$6.00'],
                    ],
                    'total' => '$11.50',
                    'payment' => 'Visa ending 4242',
                ],
                'source' => <<<'LIQUID'
<html>
<body style="font-family: 'SF Mono', Menlo, monospace; color: #18181b; margin: 0; padding: 40px; font-size: 13px;">
  <div style="max-width: 320px; margin: 0 auto;">
    <p style="text-align: center; font-size: 16px; font-weight: 700; letter-spacing: 2px; margin: 0 0 4px;">{{ store }}</p>
    <p style="text-align: center; color: #71717a; margin: 0 0 24px;">{{ date }}</p>
    <div style="border-top: 1px dashed #a1a1aa; border-bottom: 1px dashed #a1a1aa; padding: 12px 0; margin-bottom: 12px;">
      {% for item in items %}
      <table style="width: 100%;"><tr>
        <td>{{ item.name }}</td>
        <td style="text-align: right;">{{ item.price }}</td>
      </tr></table>
      {% endfor %}
    </div>
    <table style="width: 100%; font-weight: 700; font-size: 15px;"><tr>
      <td>TOTAL</td>
      <td style="text-align: right;">{{ total }}</td>
    </tr></table>
    <p style="color: #71717a; margin-top: 12px;">{{ payment }}</p>
    <p style="text-align: center; margin-top: 24px;">* * *</p>
  </div>
</body>
</html>
LIQUID,
            ],
            [
                'slug' => 'certificate',
                'name' => 'Certificate',
                'description' => 'A certificate of completion',
                'sample' => [
                    'recipient' => 'Jordan Lee',
                    'course' => 'Advanced Espresso Techniques',
                    'date' => 'July 12, 2026',
                    'issuer' => 'The Coffee Academy',
                ],
                'source' => <<<'LIQUID'
<html>
<body style="font-family: Georgia, serif; color: #1c1917; margin: 0; padding: 24px;">
  <div style="border: 3px double #a16207; padding: 56px 48px; text-align: center;">
    <p style="letter-spacing: 4px; color: #a16207; font-size: 13px; margin: 0 0 24px;">CERTIFICATE OF COMPLETION</p>
    <p style="font-size: 15px; color: #57534e; margin: 0 0 8px;">This certifies that</p>
    <p style="font-size: 34px; font-style: italic; margin: 0 0 8px;">{{ recipient }}</p>
    <p style="font-size: 15px; color: #57534e; margin: 0 0 8px;">has successfully completed</p>
    <p style="font-size: 21px; font-weight: 700; margin: 0 0 32px;">{{ course }}</p>
    <table style="width: 100%; margin-top: 40px; font-size: 13px; color: #57534e;">
      <tr>
        <td style="border-top: 1px solid #d6d3d1; padding-top: 8px; width: 45%;">{{ date }}</td>
        <td style="width: 10%;"></td>
        <td style="border-top: 1px solid #d6d3d1; padding-top: 8px; width: 45%;">{{ issuer }}</td>
      </tr>
    </table>
  </div>
</body>
</html>
LIQUID,
            ],
            [
                'slug' => 'report',
                'name' => 'Report',
                'description' => 'A monthly summary report with a metrics table',
                'sample' => [
                    'title' => 'Monthly Operations Report',
                    'period' => 'June 2026',
                    'author' => 'Operations Team',
                    'summary' => 'Renders grew 23% month over month while error rates stayed under half a percent.',
                    'metrics' => [
                        ['name' => 'Documents rendered', 'value' => '48,102', 'change' => '+23%'],
                        ['name' => 'Average render time', 'value' => '1.4s', 'change' => '-0.2s'],
                        ['name' => 'Error rate', 'value' => '0.4%', 'change' => '-0.1%'],
                    ],
                ],
                'source' => <<<'LIQUID'
<html>
<body style="font-family: -apple-system, 'Segoe UI', sans-serif; color: #18181b; margin: 0; padding: 48px;">
  <div style="border-left: 4px solid #4f46e5; padding-left: 16px; margin-bottom: 32px;">
    <h1 style="margin: 0; font-size: 24px;">{{ title }}</h1>
    <p style="margin: 4px 0 0; color: #71717a;">{{ period }} &middot; {{ author }}</p>
  </div>
  <p style="font-size: 15px; line-height: 1.6; margin-bottom: 32px;">{{ summary }}</p>
  <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
    <tr style="text-align: left; background: #f4f4f5;">
      <th style="padding: 10px 12px;">Metric</th>
      <th style="padding: 10px 12px; text-align: right;">Value</th>
      <th style="padding: 10px 12px; text-align: right;">Change</th>
    </tr>
    {% for metric in metrics %}
    <tr style="border-bottom: 1px solid #e4e4e7;">
      <td style="padding: 10px 12px;">{{ metric.name }}</td>
      <td style="padding: 10px 12px; text-align: right; font-weight: 600;">{{ metric.value }}</td>
      <td style="padding: 10px 12px; text-align: right; color: #16a34a;">{{ metric.change }}</td>
    </tr>
    {% endfor %}
  </table>
</body>
</html>
LIQUID,
            ],
            [
                'slug' => 'og-image',
                'name' => 'OG Image',
                'description' => 'A 1200x630 social share card, render with format png',
                'sample' => [
                    'title' => 'Ship documents, not spreadsheets',
                    'subtitle' => 'How we automated our billing paperwork',
                    'site' => 'blog.example.com',
                ],
                'source' => <<<'LIQUID'
<html>
<body style="margin: 0; width: 1200px; height: 630px; background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 100%); font-family: -apple-system, 'Segoe UI', sans-serif; color: white;">
  <div style="padding: 80px; display: flex; flex-direction: column; justify-content: space-between; height: 470px;">
    <div>
      <h1 style="font-size: 64px; line-height: 1.1; margin: 0 0 24px; max-width: 950px;">{{ title }}</h1>
      <p style="font-size: 28px; color: #c7d2fe; margin: 0;">{{ subtitle }}</p>
    </div>
    <p style="font-size: 24px; color: #a5b4fc; margin: 0;">{{ site }}</p>
  </div>
</body>
</html>
LIQUID,
            ],
        ];
    }
}
