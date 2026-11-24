<?php

namespace Database\Seeders;

use App\Modules\Core\Models\CompanySetting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = CompanySetting::current();
        $settings->update([
            'company_name_ar' => 'النيل للتوريدات المكتبية',
            'company_name_en' => 'Nile Office Supply',
            'address_ar' => 'القاهرة الجديدة، مصر',
            'address_en' => 'New Cairo, Egypt',
            'phone' => '01000000000',
            'email' => 'hello@nileoffice.example',
            'tax_number' => '000-000-000',
            'currency_code' => 'EGP',
            'currency_symbol_ar' => 'ج.م',
            'currency_symbol_en' => 'EGP',
            'default_tax_rate' => 1400,
            'quotation_prefix' => 'QUO',
            'invoice_prefix' => 'INV',
            'payment_prefix' => 'PAY',
            'expense_prefix' => 'EXP',
            'default_locale' => 'ar',
        ]);
    }
}
