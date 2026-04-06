<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Service::truncate();
        $services = [
            ['title' => 'Web App Pentesting', 'slug' => 'web-vapt', 'category' => 'VAPT', 'is_automated' => true, 'icon' => '🌐', 'description' => 'Automated scanning for web infrastructures.'],
            ['title' => 'Network Security Audit', 'slug' => 'network-vapt', 'category' => 'VAPT', 'is_automated' => true, 'icon' => '📡', 'description' => 'Automated internal/external network mapping.'],
            ['title' => 'API Security Testing', 'slug' => 'api-vapt', 'category' => 'VAPT', 'is_automated' => true, 'icon' => '🔑', 'description' => 'Deep automated analysis of API logic.'],
            ['title' => 'Advanced Manual Exploitation', 'slug' => 'manual-pentest', 'category' => 'VAPT', 'is_automated' => false, 'icon' => '🎯', 'description' => 'High-end manual testing for critical assets.'],
            ['title' => 'SIEM Deployment', 'slug' => 'siem-setup', 'category' => 'SOC', 'is_automated' => false, 'icon' => '📊', 'description' => 'Security Information and Event Management setup.'],
            ['title' => 'Endpoint Protection (EDR)', 'slug' => 'edr-defense', 'category' => 'SOC', 'is_automated' => false, 'icon' => '💻', 'description' => 'Monitoring and defense for workstations.'],
            ['title' => 'Incident Response Plan', 'slug' => 'incident-response', 'category' => 'SOC', 'is_automated' => false, 'icon' => '🚨', 'description' => 'Procedures for security breach handling.'],
            ['title' => 'Security Consultation', 'slug' => 'consultation', 'category' => 'Consultation', 'is_automated' => false, 'icon' => '🤝', 'description' => 'Strategic advisory and risk management.'],
            ['title' => 'AWS/Azure Hardening', 'slug' => 'cloud-hardening', 'category' => 'Cloud', 'is_automated' => false, 'icon' => '☁️', 'description' => 'Auditing cloud configurations to prevent leaks.'],
            ['title' => 'Kubernetes Security', 'slug' => 'k8s-security', 'category' => 'Cloud', 'is_automated' => false, 'icon' => '☸️', 'description' => 'Securing containerized workloads.']
        ];
        foreach ($services as $s) {
            \App\Models\Service::create($s);
        }
    }
}
