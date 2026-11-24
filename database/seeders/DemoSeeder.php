<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Services\ProductService;
use App\Modules\Core\Services\Money;
use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Services\CustomerService;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Modules\Expenses\Services\ExpenseService;
use App\Modules\Inventory\Services\InventoryService;
use App\Modules\Inventory\Services\WarehouseService;
use App\Modules\Sales\Services\InvoiceService;
use App\Modules\Sales\Services\PaymentService;
use App\Modules\Sales\Services\QuotationService;
use App\Modules\Suppliers\Services\SupplierService;
use App\Modules\Users\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('is_demo', true)->exists()) {
            return;
        }

        $roles = Role::query()->pluck('id', 'name');

        $admin = User::create([
            'name' => 'أحمد فتحي', 'email' => 'admin@businessflow.test',
            'password' => Hash::make('password'), 'role_id' => $roles['admin'],
            'locale' => 'ar', 'is_active' => true, 'is_demo' => true,
        ]);
        $sales = User::create([
            'name' => 'خالد إبراهيم', 'email' => 'sales@businessflow.test',
            'password' => Hash::make('password'), 'role_id' => $roles['sales'],
            'locale' => 'ar', 'is_active' => true, 'is_demo' => true,
        ]);
        $accountant = User::create([
            'name' => 'منى سعيد', 'email' => 'accountant@businessflow.test',
            'password' => Hash::make('password'), 'role_id' => $roles['accountant'],
            'locale' => 'ar', 'is_active' => true, 'is_demo' => true,
        ]);
        User::create([
            'name' => 'Hala Mostafa', 'email' => 'warehouse@businessflow.test',
            'password' => Hash::make('password'), 'role_id' => $roles['warehouse'],
            'locale' => 'en', 'is_active' => true, 'is_demo' => true,
        ]);

        $warehouseService = app(WarehouseService::class);
        $mainWarehouse = $warehouseService->create([
            'code' => 'WH-MAIN', 'name' => 'المخزن الرئيسي', 'location' => 'القاهرة الجديدة',
            'is_default' => true, 'is_active' => true,
        ]);
        $gizaWarehouse = $warehouseService->create([
            'code' => 'WH-GIZA', 'name' => 'مخزن الجيزة', 'location' => 'الجيزة',
            'is_default' => false, 'is_active' => true,
        ]);

        $crm = app(CustomerService::class);
        $cities = ['القاهرة الجديدة', 'مدينة نصر', 'المعادي', 'الجيزة', 'الإسكندرية', 'المنصورة'];
        $customerRows = [
            ['مكتب النور للمحاسبة', 'company'], ['شركة الأفق للاستشارات', 'company'],
            ['مدرسة السلام الدولية', 'company'], ['عيادة الشفاء التخصصية', 'company'],
            ['شركة المدى للبرمجيات', 'company'], ['مؤسسة البناء الحديث', 'company'],
            ['شركة النخيل للعقارات', 'company'], ['مركز الإبداع للتدريب', 'company'],
            ['شركة الرياض للخدمات', 'company'], ['مستشفى الأمل العام', 'company'],
            ['شركة الدلتا للأغذية', 'company'], ['مكتب البيان للمحاماة', 'company'],
            ['شركة الشمس للتأمين', 'company'], ['مؤسسة الفجر للتعليم', 'company'],
            ['شركة البحر الأحمر للسياحة', 'company'], ['مركز النيل للتصوير', 'company'],
            ['شركة الواحة للاستثمار', 'company'], ['مكتب القاهرة للهندسة', 'company'],
            ['شركة الأهرام للطباعة', 'company'], ['مؤسسة السلام الخيرية', 'company'],
            ['شركة الجيزة للنقل', 'company'], ['مركز الإسكندرية للتدريب', 'company'],
            ['شركة الإنتاج الإعلامي', 'company'], ['مكتب المستقبل للاستشارات', 'company'],
            ['أحمد مصطفى', 'individual'], ['سارة حسن', 'individual'],
        ];
        $contactNames = ['كريم أشرف', 'هدى سالم', 'طارق منصور', 'ياسمين فؤاد', 'عمر خالد', 'نهى سمير', 'حسن علي', 'مريم يوسف', 'عادل فتحي', 'سلمى رشدي'];

        $customers = [];
        foreach ($customerRows as $i => [$name, $type]) {
            $customer = $crm->create([
                'type' => $type,
                'name' => $name,
                'company_name' => $type === 'company' ? $name.' ش.ذ.م.م' : null,
                'email' => 'customer'.$i.'@example.com',
                'phone' => sprintf('0100000%04d', $i),
                'address' => $cities[$i % count($cities)],
                'city' => $cities[$i % count($cities)],
                'country' => 'مصر',
                'tax_number' => sprintf('000-000-%03d', $i),
                'notes' => 'بيانات تجريبية.',
                'is_active' => true,
            ], $admin);
            $customers[] = $customer;

            if ($type === 'company' && $i < count($contactNames)) {
                Contact::create([
                    'customer_id' => $customer->id,
                    'name' => $contactNames[$i],
                    'position' => 'مسؤول المشتريات',
                    'email' => 'contact'.$i.'@example.com',
                    'phone' => sprintf('0110000%04d', $i),
                    'is_primary' => true,
                ]);
            }
        }

        $supplierService = app(SupplierService::class);
        $supplierRows = [
            ['شركة الورق المصرية', 'سامح عادل', 'القاهرة'],
            ['مؤسسة الأدوات المكتبية', 'منال حسن', 'الجيزة'],
            ['شركة الأحبار الحديثة', 'أشرف كمال', 'الإسكندرية'],
            ['مصنع الأثاث المكتبي', 'وليد سمير', 'القاهرة'],
            ['شركة الشحن السريع', 'هاني فؤاد', 'القاهرة'],
            ['مؤسسة الصيانة المتكاملة', 'رامي عادل', 'الجيزة'],
        ];
        $suppliers = [];
        foreach ($supplierRows as $i => [$name, $contact, $city]) {
            $suppliers[] = $supplierService->create([
                'name' => $name, 'contact_name' => $contact,
                'email' => 'supplier'.$i.'@example.com',
                'phone' => sprintf('0120000%04d', $i),
                'address' => $city, 'city' => $city, 'tax_number' => sprintf('111-111-%03d', $i),
                'notes' => 'بيانات تجريبية.', 'is_active' => true,
            ], $admin);
        }

        $categories = [
            'paper' => Category::create(['name' => 'Paper & Printing', 'name_ar' => 'ورق ومطبوعات']),
            'writing' => Category::create(['name' => 'Writing Supplies', 'name_ar' => 'أدوات كتابية']),
            'furniture' => Category::create(['name' => 'Office Furniture', 'name_ar' => 'أثاث مكتبي']),
            'ink' => Category::create(['name' => 'Ink & Toner', 'name_ar' => 'أحبار وتونر']),
            'services' => Category::create(['name' => 'Services', 'name_ar' => 'خدمات']),
        ];

        $productService = app(ProductService::class);
        $productRows = [
            ['PAP-A4', 'A4 Copy Paper', 'ورق تصوير A4', 'paper', 'ream', '250.00', '180.00', true, '100', 2000],
            ['PAP-A3', 'A3 Copy Paper', 'ورق تصوير A3', 'paper', 'ream', '420.00', '320.00', true, '40', 800],
            ['NOTE-A5', 'Notebook A5', 'دفتر ملاحظات A5', 'paper', 'piece', '45.00', '25.00', true, '50', 1000],
            ['ENV-DL', 'Envelopes DL', 'أظرف DL', 'paper', 'pack', '60.00', '35.00', true, '30', 800],
            ['PEN-BLUE', 'Blue Pens', 'أقلام حبر أزرق', 'writing', 'box', '90.00', '55.00', true, '40', 1000],
            ['PENCIL', 'Pencils', 'أقلام رصاص', 'writing', 'box', '70.00', '40.00', true, '40', 1000],
            ['MARKER', 'Markers', 'أقلام تحديد', 'writing', 'pack', '120.00', '70.00', true, '25', 800],
            ['STAPLER', 'Stapler', 'دباسة معدنية', 'writing', 'piece', '150.00', '95.00', true, '10', 4],
            ['STAPLES', 'Staples', 'دبابيس دباسة', 'writing', 'box', '25.00', '12.00', true, '50', 1500],
            ['TAPE', 'Adhesive Tape', 'شريط لاصق', 'writing', 'roll', '20.00', '10.00', true, '50', 1200],
            ['CHAIR-ERG', 'Ergonomic Chair', 'كرسي مكتب مريح', 'furniture', 'piece', '3200.00', '2400.00', true, '5', 60],
            ['DESK-OFF', 'Office Desk', 'مكتب مكتبي', 'furniture', 'piece', '4500.00', '3400.00', true, '3', 40],
            ['SHELF', 'Bookshelf', 'رفوف مكتبية', 'furniture', 'piece', '1800.00', '1300.00', true, '5', 50],
            ['CABINET', 'Filing Cabinet', 'خزانة ملفات', 'furniture', 'piece', '3500.00', '2600.00', true, '5', 40],
            ['TONER-BK', 'Black Toner', 'تونر أسود', 'ink', 'piece', '1200.00', '850.00', true, '15', 8],
            ['INK-COL', 'Colour Ink Cartridge', 'خرطوشة حبر ملونة', 'ink', 'piece', '450.00', '300.00', true, '20', 800],
            ['SVC-INSTALL', 'Installation Service', 'خدمة تركيب', 'services', 'service', '500.00', '0.00', false, '0', 0],
            ['SVC-MAINT', 'Maintenance Visit', 'زيارة صيانة', 'services', 'service', '350.00', '0.00', false, '0', 0],
            ['SVC-DELIVERY', 'Delivery', 'خدمة توصيل', 'services', 'service', '150.00', '0.00', false, '0', 0],
        ];

        $products = [];
        $inventory = app(InventoryService::class);
        foreach ($productRows as [$sku, $name, $nameAr, $cat, $unit, $price, $cost, $tracked, $threshold, $stock]) {
            $product = $productService->create([
                'sku' => $sku, 'name' => $name, 'name_ar' => $nameAr, 'type' => $tracked ? 'product' : 'service',
                'category_id' => $categories[$cat]->id, 'unit' => $unit, 'price' => $price, 'cost' => $cost,
                'tax_rate' => '14', 'is_tracked' => $tracked, 'low_stock_threshold' => $threshold,
                'is_active' => true, 'notes' => 'بيانات تجريبية.',
            ], $admin);
            $products[$sku] = $product;

            if ($tracked && $stock > 0) {
                $inventory->stockIn($product, $mainWarehouse, $stock, $admin, $product->cost_minor, now()->subMonths(6)->format('Y-m-d'), 'رصيد افتتاحي');
            }
        }

        $lowStockSkus = ['STAPLER', 'TONER-BK'];
        $furnitureSkus = ['CHAIR-ERG', 'DESK-OFF', 'SHELF', 'CABINET'];
        $invoicePool = array_values(array_filter($products, fn (Product $p) => ! in_array($p->sku, $lowStockSkus, true)));
        $quotePool = $invoicePool;

        $quotationService = app(QuotationService::class);
        $methods = ['bank_transfer', 'cash', 'card', 'cheque'];
        $quoteStatuses = ['draft', 'sent', 'accepted', 'rejected', 'expired'];
        $quoteIndex = 0;

        for ($m = 5; $m >= 0; $m--) {
            for ($k = 0; $k < 4; $k++) {
                $status = $quoteStatuses[($m * 4 + $k) % 5];
                $issue = $this->pastDate(now()->subMonths($m)->startOfMonth()->addDays(2 + ($k * 6)), 2 + $k);
                $first = $quotePool[$quoteIndex % count($quotePool)];
                $second = $quotePool[($quoteIndex + 4) % count($quotePool)];
                $quoteIndex++;

                $quotation = $quotationService->create([
                    'customer_id' => $customers[($m * 4 + $k) % count($customers)]->id,
                    'issue_date' => $issue->format('Y-m-d'),
                    'valid_until' => $issue->copy()->addDays(14)->format('Y-m-d'),
                    'discount' => '0.00',
                    'notes' => 'عرض سعر تجريبي.',
                    'terms' => 'الأسعار سارية لمدة أسبوعين، والتسليم خلال خمسة أيام عمل.',
                    'lines' => [
                        ['product_id' => $first->id, 'description' => $first->name_ar, 'quantity' => (string) (1 + ($k % 3)), 'unit_price' => Money::input($first->price_minor), 'tax_rate' => '14'],
                        ['product_id' => $second->id, 'description' => $second->name_ar, 'quantity' => '1', 'unit_price' => Money::input($second->price_minor), 'tax_rate' => '14'],
                    ],
                ], $sales);

                if ($status !== 'draft') {
                    $quotationService->updateStatus($quotation, 'sent');
                    if (in_array($status, ['accepted', 'rejected', 'expired'], true)) {
                        $quotationService->updateStatus($quotation, $status);
                    }
                }
            }
        }

        $invoiceService = app(InvoiceService::class);
        $paymentService = app(PaymentService::class);
        $invoiceStatuses = ['paid', 'partially_paid', 'issued', 'paid', 'draft', 'partially_paid', 'issued', 'cancelled', 'paid'];
        $invoiceIndex = 0;

        for ($m = 5; $m >= 0; $m--) {
            foreach ($invoiceStatuses as $idx => $status) {
                $customer = $customers[($m * 9 + $idx) % count($customers)];
                $issue = $this->pastDate(now()->subMonths($m)->startOfMonth()->addDays(1 + (($idx * 3) % 26)), 2 + ($idx % 6));
                $first = $invoicePool[$invoiceIndex % count($invoicePool)];
                $second = $invoicePool[($invoiceIndex + 5) % count($invoicePool)];
                $invoiceIndex++;

                $lines = [
                    ['product_id' => $first->id, 'description' => $first->name_ar, 'quantity' => $this->lineQuantity($first, $idx, $furnitureSkus), 'unit_price' => Money::input($first->price_minor), 'tax_rate' => '14'],
                ];
                if ($idx % 2 === 0) {
                    $lines[] = ['product_id' => $second->id, 'description' => $second->name_ar, 'quantity' => $this->lineQuantity($second, $idx + 1, $furnitureSkus), 'unit_price' => Money::input($second->price_minor), 'tax_rate' => '14'];
                }

                $invoice = $invoiceService->create([
                    'customer_id' => $customer->id,
                    'warehouse_id' => $mainWarehouse->id,
                    'issue_date' => $issue->format('Y-m-d'),
                    'due_date' => $issue->copy()->addDays(30)->format('Y-m-d'),
                    'discount' => '0.00',
                    'notes' => 'فاتورة تجريبية.',
                    'lines' => $lines,
                ], $sales);

                if ($status === 'draft') {
                    continue;
                }

                $invoiceService->issue($invoice, $sales);

                if ($status === 'paid' || $status === 'partially_paid') {
                    $amountMinor = $status === 'paid'
                        ? $invoice->total_minor
                        : max(1, intdiv($invoice->total_minor * 40, 100));

                    $paymentService->create([
                        'invoice_id' => $invoice->id,
                        'amount' => Money::input($amountMinor),
                        'method' => $methods[$invoiceIndex % count($methods)],
                        'payment_date' => $this->pastDate($issue->copy()->addDays(2), 1)->format('Y-m-d'),
                        'reference' => 'REF-'.str_pad((string) $invoiceIndex, 4, '0', STR_PAD_LEFT),
                        'notes' => 'دفعة تجريبية.',
                    ], $accountant);
                } elseif ($status === 'cancelled') {
                    $invoiceService->cancel($invoice, 'خطأ في بيانات العميل', $admin);
                }
            }
        }

        $expenseService = app(ExpenseService::class);
        $expenseCategories = [
            'rent' => ExpenseCategory::create(['name' => 'Rent', 'name_ar' => 'إيجار']),
            'utilities' => ExpenseCategory::create(['name' => 'Utilities', 'name_ar' => 'مرافق']),
            'marketing' => ExpenseCategory::create(['name' => 'Marketing', 'name_ar' => 'تسويق']),
            'salaries' => ExpenseCategory::create(['name' => 'Salaries', 'name_ar' => 'رواتب']),
            'maintenance' => ExpenseCategory::create(['name' => 'Maintenance', 'name_ar' => 'صيانة']),
            'shipping' => ExpenseCategory::create(['name' => 'Shipping', 'name_ar' => 'شحن']),
        ];
        $expenseRows = [
            ['rent', 'إيجار المقر', '8000.00', null],
            ['utilities', 'كهرباء ومياه', '2200.00', null],
            ['marketing', 'حملة تسويق رقمي', '2500.00', null],
            ['salaries', 'رواتب الفريق', '24000.00', null],
            ['maintenance', 'صيانة أجهزة', '900.00', $suppliers[5]],
            ['shipping', 'شحن طلبات', '700.00', $suppliers[4]],
        ];

        for ($m = 5; $m >= 0; $m--) {
            foreach ($expenseRows as $i => [$key, $description, $amount, $supplier]) {
                $date = $this->pastDate(now()->subMonths($m)->startOfMonth()->addDays(2 + ($i * 4)), 2 + $i);
                $expenseService->create([
                    'expense_category_id' => $expenseCategories[$key]->id,
                    'supplier_id' => $supplier?->id,
                    'expense_date' => $date->format('Y-m-d'),
                    'amount' => $amount,
                    'payment_method' => $methods[$i % count($methods)],
                    'reference' => 'EXP-REF-'.str_pad((string) ($m * 6 + $i), 4, '0', STR_PAD_LEFT),
                    'description' => $description,
                ], $accountant);
            }
        }

        $inventory->adjust($products['PAP-A4'], $mainWarehouse, 10, $admin, 'تسوية جرد', now()->subMonths(2)->format('Y-m-d'));
        $inventory->transfer($products['PAP-A4'], $mainWarehouse, $gizaWarehouse, 20, $admin, 'تحويل مخزون', now()->subMonths(1)->format('Y-m-d'));
    }

    private function pastDate(Carbon $date, int $fallbackDays = 2): Carbon
    {
        return $date->isFuture() ? now()->copy()->subDays($fallbackDays) : $date->copy();
    }

    /** Bulk quantities for consumables, single units for furniture, 1–2 for services. */
    private function lineQuantity(Product $product, int $seed, array $furnitureSkus): string
    {
        if ($product->type === 'service') {
            return (string) (1 + ($seed % 2));
        }

        if (in_array($product->sku, $furnitureSkus, true)) {
            return '1';
        }

        return (string) (30 + (($seed * 11) % 51));
    }
}
