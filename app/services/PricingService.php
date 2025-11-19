<?php

/**
 * Dynamic Pricing Service
 * Calculates pricing based on usage and rules
 *
 * @author DigiParc Team
 * @version 1.0
 */
class PricingService
{
    private $db;

    // Base prices
    private $basePrices = [
        'user' => 10.00,           // €10 per active user/month
        'vehicle' => 5.00,          // €5 per vehicle/month
        'driver' => 3.00,           // €3 per driver/month
    ];

    // Module prices
    private $modulePrices = [
        'gps' => 49.00,
        'taxi_vtc' => 79.00,
        'smart_delivery' => 149.00,
        'maintenance' => 59.00,
        'stock' => 69.00,
        'hr' => 89.00,
        'fuel' => 39.00,
        'missions' => 49.00,
    ];

    // Package prices (all-inclusive)
    private $packagePrices = [
        'starter' => 149.00,
        'professional' => 299.00,
        'enterprise' => 599.00,
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Calculate monthly price for a company
     *
     * @param int $companyId
     * @return array Price breakdown
     */
    public function calculateMonthlyPrice($companyId)
    {
        $company = $this->getCompany($companyId);

        // If company has a package, return package price
        if ($company['subscription_plan'] && isset($this->packagePrices[$company['subscription_plan']])) {
            return $this->getPackagePricing($company['subscription_plan'], $companyId);
        }

        // Otherwise, calculate à la carte pricing
        return $this->getAlaCartePricing($companyId);
    }

    /**
     * Get package pricing
     */
    private function getPackagePricing($plan, $companyId)
    {
        $basePrice = $this->packagePrices[$plan];

        // Get active counts
        $usage = $this->getUsage($companyId);

        // Check if exceeds package limits
        $limits = $this->getPackageLimits($plan);
        $overages = $this->calculateOverages($usage, $limits);

        $totalPrice = $basePrice + $overages['total'];

        return [
            'type' => 'package',
            'plan' => $plan,
            'base_price' => $basePrice,
            'overages' => $overages,
            'total' => $totalPrice,
            'usage' => $usage,
            'limits' => $limits
        ];
    }

    /**
     * Get à la carte pricing
     */
    private function getAlaCartePricing($companyId)
    {
        $usage = $this->getUsage($companyId);
        $activeModules = $this->getActiveModules($companyId);

        // Calculate usage-based price
        $usagePrice = 0;
        $usagePrice += $usage['active_users'] * $this->basePrices['user'];
        $usagePrice += $usage['vehicles'] * $this->basePrices['vehicle'];
        $usagePrice += $usage['drivers'] * $this->basePrices['driver'];

        // Apply volume discounts
        $usagePrice = $this->applyVolumeDiscounts($usagePrice, $usage);

        // Calculate modules price
        $modulesPrice = 0;
        foreach ($activeModules as $module) {
            $modulesPrice += $this->modulePrices[$module] ?? 0;
        }

        $totalPrice = $usagePrice + $modulesPrice;

        return [
            'type' => 'alacarte',
            'usage_price' => round($usagePrice, 2),
            'modules_price' => round($modulesPrice, 2),
            'total' => round($totalPrice, 2),
            'usage' => $usage,
            'active_modules' => $activeModules,
            'breakdown' => [
                'users' => $usage['active_users'] * $this->basePrices['user'],
                'vehicles' => $usage['vehicles'] * $this->basePrices['vehicle'],
                'drivers' => $usage['drivers'] * $this->basePrices['driver'],
            ]
        ];
    }

    /**
     * Get current usage
     */
    private function getUsage($companyId)
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM users WHERE company_id = :company_id AND status = 'active') as active_users,
                    (SELECT COUNT(*) FROM vehicles WHERE company_id = :company_id AND status = 'active') as vehicles,
                    (SELECT COUNT(*) FROM drivers WHERE company_id = :company_id AND status = 'active') as drivers";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get active modules
     */
    private function getActiveModules($companyId)
    {
        $sql = "SELECT module_name FROM company_modules
                WHERE company_id = :company_id AND is_active = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'module_name');
    }

    /**
     * Get package limits
     */
    private function getPackageLimits($plan)
    {
        $limits = [
            'starter' => [
                'users' => 5,
                'vehicles' => 10,
                'drivers' => 10
            ],
            'professional' => [
                'users' => 20,
                'vehicles' => 50,
                'drivers' => 50
            ],
            'enterprise' => [
                'users' => -1,  // Unlimited
                'vehicles' => -1,
                'drivers' => -1
            ]
        ];

        return $limits[$plan] ?? $limits['starter'];
    }

    /**
     * Calculate overages
     */
    private function calculateOverages($usage, $limits)
    {
        $overages = [
            'users' => 0,
            'vehicles' => 0,
            'drivers' => 0,
            'total' => 0
        ];

        // Users overage
        if ($limits['users'] != -1 && $usage['active_users'] > $limits['users']) {
            $overages['users'] = ($usage['active_users'] - $limits['users']) * $this->basePrices['user'];
        }

        // Vehicles overage
        if ($limits['vehicles'] != -1 && $usage['vehicles'] > $limits['vehicles']) {
            $overages['vehicles'] = ($usage['vehicles'] - $limits['vehicles']) * $this->basePrices['vehicle'];
        }

        // Drivers overage
        if ($limits['drivers'] != -1 && $usage['drivers'] > $limits['drivers']) {
            $overages['drivers'] = ($usage['drivers'] - $limits['drivers']) * $this->basePrices['driver'];
        }

        $overages['total'] = $overages['users'] + $overages['vehicles'] + $overages['drivers'];

        return $overages;
    }

    /**
     * Apply volume discounts
     */
    private function applyVolumeDiscounts($price, $usage)
    {
        $totalResources = $usage['active_users'] + $usage['vehicles'] + $usage['drivers'];

        // Volume discount tiers
        if ($totalResources >= 100) {
            $price *= 0.70;  // 30% discount
        } elseif ($totalResources >= 50) {
            $price *= 0.80;  // 20% discount
        } elseif ($totalResources >= 20) {
            $price *= 0.85;  // 15% discount
        } elseif ($totalResources >= 10) {
            $price *= 0.90;  // 10% discount
        }

        return $price;
    }

    /**
     * Get pricing recommendations
     */
    public function getPricingRecommendations($companyId)
    {
        $currentPricing = $this->calculateMonthlyPrice($companyId);
        $recommendations = [];

        // If à la carte, check if package would be cheaper
        if ($currentPricing['type'] === 'alacarte') {
            foreach (['starter', 'professional', 'enterprise'] as $plan) {
                $packagePrice = $this->getPackagePricing($plan, $companyId);

                if ($packagePrice['total'] < $currentPricing['total']) {
                    $savings = $currentPricing['total'] - $packagePrice['total'];

                    $recommendations[] = [
                        'type' => 'switch_to_package',
                        'plan' => $plan,
                        'current_price' => $currentPricing['total'],
                        'new_price' => $packagePrice['total'],
                        'savings' => round($savings, 2),
                        'savings_percent' => round(($savings / $currentPricing['total']) * 100, 1),
                        'message' => "Économisez {$savings}€/mois en passant au pack " . ucfirst($plan)
                    ];
                }
            }
        }

        return $recommendations;
    }

    /**
     * Get company
     */
    private function getCompany($companyId)
    {
        $sql = "SELECT * FROM companies WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $companyId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Generate invoice for company
     */
    public function generateInvoice($companyId, $month = null)
    {
        $month = $month ?? date('Y-m');

        $pricing = $this->calculateMonthlyPrice($companyId);
        $company = $this->getCompany($companyId);

        $invoice = [
            'company_id' => $companyId,
            'company_name' => $company['name'],
            'month' => $month,
            'pricing' => $pricing,
            'subtotal' => $pricing['total'],
            'tax_rate' => 0.20,  // 20% VAT
            'tax_amount' => round($pricing['total'] * 0.20, 2),
            'total' => round($pricing['total'] * 1.20, 2),
            'generated_at' => date('Y-m-d H:i:s')
        ];

        // Save invoice to database
        $this->saveInvoice($invoice);

        return $invoice;
    }

    /**
     * Save invoice
     */
    private function saveInvoice($invoice)
    {
        $sql = "INSERT INTO invoices (company_id, month, amount, tax_amount, total_amount, pricing_data, status, created_at)
                VALUES (:company_id, :month, :amount, :tax_amount, :total, :pricing_data, 'pending', NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $invoice['company_id']);
        $stmt->bindParam(':month', $invoice['month']);
        $stmt->bindParam(':amount', $invoice['subtotal']);
        $stmt->bindParam(':tax_amount', $invoice['tax_amount']);
        $stmt->bindParam(':total', $invoice['total']);

        $pricingData = json_encode($invoice['pricing']);
        $stmt->bindParam(':pricing_data', $pricingData);

        $stmt->execute();
    }
}
