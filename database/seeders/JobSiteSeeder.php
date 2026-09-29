<?php

namespace Database\Seeders;

use App\Models\JobSite;
use Illuminate\Database\Seeder;

class JobSiteSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'job_site_name' => 'Downtown Office Building',
                'contact_no' => 'JS-001',
                'start_date' => '2026-01-15',
                'end_date' => '2026-06-15',
                'description' => 'Complete renovation of 5-story office building',
                'customer_no' => 'CUST-001',
                'customer_name' => 'ABC Corporation',
                'address' => '123 Main Street, Downtown, NY 10001',
                'contact_person' => 'John Smith',
                'phone' => '555-123-4567',
                'email' => 'john@abccorp.com',
                'status' => 1,
                'note' => 'Priority project - high budget',
            ],
            [
                'job_site_name' => 'Riverside Apartments',
                'contact_no' => 'JS-002',
                'start_date' => '2026-02-01',
                'end_date' => '2026-08-01',
                'description' => 'Installation of new plumbing systems in 50-unit apartment complex',
                'customer_no' => 'CUST-002',
                'customer_name' => 'Riverside Properties LLC',
                'address' => '456 River Road, Suite 100, Riverside, CA 92501',
                'contact_person' => 'Sarah Johnson',
                'phone' => '555-234-5678',
                'email' => 'sarah@riversideprops.com',
                'status' => 1,
                'note' => 'Large scale project',
            ],
            [
                'job_site_name' => 'Tech Park Warehouse',
                'contact_no' => 'JS-003',
                'start_date' => '2026-03-01',
                'end_date' => '2026-04-30',
                'description' => 'Electrical system upgrade for warehouse facility',
                'customer_no' => 'CUST-003',
                'customer_name' => 'TechPark Industries',
                'address' => '789 Tech Boulevard, Innovation Park, TX 75001',
                'contact_person' => 'Mike Chen',
                'phone' => '555-345-6789',
                'email' => 'mike@techpark.com',
                'status' => 1,
                'note' => 'Quick turnaround required',
            ],
            [
                'job_site_name' => 'Harbor View Condos',
                'contact_no' => 'JS-004',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'description' => 'Full construction of 100-unit waterfront condominium complex',
                'customer_no' => 'CUST-004',
                'customer_name' => 'Harbor Development Corp',
                'address' => '321 Ocean Drive, Harbor City, FL 33101',
                'contact_person' => 'Lisa Martinez',
                'phone' => '555-456-7890',
                'email' => 'lisa@harbordev.com',
                'status' => 1,
                'note' => 'Multi-year project',
            ],
            [
                'job_site_name' => 'Central Mall Renovation',
                'contact_no' => 'JS-005',
                'start_date' => '2026-04-01',
                'end_date' => '2026-07-31',
                'description' => 'Interior renovation of shopping mall food court',
                'customer_no' => 'CUST-005',
                'customer_name' => 'Central Mall Management',
                'address' => '500 Central Avenue, Shopville, IL 60601',
                'contact_person' => 'Robert Brown',
                'phone' => '555-567-8901',
                'email' => 'robert@centralmall.com',
                'status' => 1,
                'note' => 'Phase 1 of renovation',
            ],
            [
                'job_site_name' => 'Sunset Villa Construction',
                'contact_no' => 'JS-006',
                'start_date' => '2026-02-15',
                'end_date' => '2026-09-15',
                'description' => 'Custom home construction for luxury residential project',
                'customer_no' => 'CUST-006',
                'customer_name' => 'Sunset Homes Inc',
                'address' => '888 Sunset Boulevard, Beverly Hills, CA 90210',
                'contact_person' => 'Emily White',
                'phone' => '555-678-9012',
                'email' => 'emily@sunsethomes.com',
                'status' => 1,
                'note' => 'High-end residential',
            ],
            [
                'job_site_name' => 'Industrial Park Phase 2',
                'contact_no' => 'JS-007',
                'start_date' => '2026-03-15',
                'end_date' => '2026-10-15',
                'description' => 'Construction of additional warehousing units',
                'customer_no' => 'CUST-007',
                'customer_name' => 'Industrial Solutions Group',
                'address' => '1000 Industrial Way, Commerce City, CO 80001',
                'contact_person' => 'David Wilson',
                'phone' => '555-789-0123',
                'email' => 'david@industrialsol.com',
                'status' => 2,
                'note' => 'On hold due to permit issues',
            ],
            [
                'job_site_name' => 'City Hospital Expansion',
                'contact_no' => 'JS-008',
                'start_date' => '2026-01-20',
                'end_date' => '2027-01-20',
                'description' => 'New emergency department wing construction',
                'customer_no' => 'CUST-008',
                'customer_name' => 'City Medical Center',
                'address' => '250 Medical Center Drive, Healthville, WA 98101',
                'contact_person' => 'Dr. Patricia Lee',
                'phone' => '555-890-1234',
                'email' => 'plee@citymed.org',
                'status' => 1,
                'note' => 'Critical infrastructure project',
            ],
            [
                'job_site_name' => 'University Library Modernization',
                'contact_no' => 'JS-009',
                'start_date' => '2026-05-01',
                'end_date' => '2026-11-30',
                'description' => 'Complete modernization of university library facilities',
                'customer_no' => 'CUST-009',
                'customer_name' => 'State University',
                'address' => '1 University Circle, College Town, MA 01001',
                'contact_person' => 'Professor Adams',
                'phone' => '555-901-2345',
                'email' => 'adams@stateuniv.edu',
                'status' => 1,
                'note' => 'Academic year deadline',
            ],
            [
                'job_site_name' => 'Airport Terminal Upgrade',
                'contact_no' => 'JS-010',
                'start_date' => '2026-02-01',
                'end_date' => '2026-12-31',
                'description' => 'Security checkpoint expansion and modernization',
                'customer_no' => 'CUST-010',
                'customer_name' => 'Metro Airport Authority',
                'address' => 'Terminal Access Road, Airport City, GA 30301',
                'contact_person' => 'James Taylor',
                'phone' => '555-012-3456',
                'email' => 'jtaylor@metroairport.org',
                'status' => 1,
                'note' => 'High security clearance required',
            ],
        ];

        $currentYear = date('Y');
        $prefix = 'JS';

        foreach ($items as $index => $data) {
            $jobSiteNo = sprintf('%s-%s-%03d', $prefix, $currentYear, $index + 1);

            JobSite::updateOrCreate(
                ['job_site_no' => $jobSiteNo],
                array_merge($data, [
                    'project_id' => 1,
                    'job_site_no' => $jobSiteNo,
                    'inserted_by' => 0,
                    'updated_by' => 0,
                    'is_deleted' => 0,
                ])
            );
        }
    }
}
