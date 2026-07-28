<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Department;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\InventoryItem;
use App\Models\InventoryHistory;
use App\Models\SupplyRequest;
use App\Models\RequestItem;
use App\Models\Issuance;
use App\Models\IssuanceItem;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // --- Roles ---
        $roles = [
            'admin', 'supply-officer', 'staff', 'auditor', 'budget-officer', 'accounting', 
            'regional-director', 'assistant-regional-director', 'client',
            'supply-staff', 'budget-staff', 'accounting-staff', 'ard-staff', 'rd-staff'
        ];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // --- Departments ---
        $departments = [
            ['name' => 'Administration',       'code' => 'ADM'],
            ['name' => 'Finance',               'code' => 'FIN'],
            ['name' => 'Human Resources',       'code' => 'HR'],
            ['name' => 'Information Technology','code' => 'IT'],
            ['name' => 'Operations',            'code' => 'OPS'],
            ['name' => 'Procurement',           'code' => 'PROC'],
        ];
        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }

        // --- Users ---
        $users = [
            [
                'name'          => 'System Administrator',
                'email'         => 'admin@supply.local',
                'password'      => Hash::make('Supply@2026'),
                'department_id' => Department::where('code', 'ADM')->first()->id,
                'position'      => 'System Administrator',
                'phone'         => '09001234567',
                'is_active'     => true,
                'role'          => 'admin',
            ],
            [
                'name'          => 'Supply Officer',
                'email'         => 'officer@supply.local',
                'password'      => Hash::make('Supply@2026'),
                'department_id' => Department::where('code', 'PROC')->first()->id,
                'position'      => 'Supply Officer',
                'phone'         => '09007654321',
                'is_active'     => true,
                'role'          => 'supply-officer',
            ],
            [
                'name'          => 'Supply Officer Staff',
                'email'         => 'supply.staff@supply.local',
                'password'      => Hash::make('Supply@2026'),
                'department_id' => Department::where('code', 'PROC')->first()->id,
                'position'      => 'Supply Staff Assistant',
                'phone'         => '09007654322',
                'is_active'     => true,
                'role'          => 'supply-staff',
            ],
            [
                'name'          => 'Department Staff',
                'email'         => 'staff@supply.local',
                'password'      => Hash::make('Supply@2026'),
                'department_id' => Department::where('code', 'IT')->first()->id,
                'position'      => 'IT Specialist',
                'phone'         => '09001112222',
                'is_active'     => true,
                'role'          => 'staff',
            ],
            [
                'name'          => 'Office Auditor',
                'email'         => 'auditor@supply.local',
                'password'      => Hash::make('Supply@2026'),
                'department_id' => Department::where('code', 'FIN')->first()->id,
                'position'      => 'Internal Auditor',
                'phone'         => '09003334444',
                'is_active'     => true,
                'role'          => 'auditor',
            ],
            [
                'name'          => 'Budget Officer',
                'email'         => 'budget@supply.local',
                'password'      => Hash::make('Supply@2026'),
                'department_id' => Department::where('code', 'FIN')->first()->id,
                'position'      => 'Budget Officer',
                'phone'         => '09004445555',
                'is_active'     => true,
                'role'          => 'budget-officer',
            ],
            [
                'name'          => 'Budget Officer Staff',
                'email'         => 'budget.staff@supply.local',
                'password'      => Hash::make('Supply@2026'),
                'department_id' => Department::where('code', 'FIN')->first()->id,
                'position'      => 'Budget Assistant',
                'phone'         => '09004445556',
                'is_active'     => true,
                'role'          => 'budget-staff',
            ],
            [
                'name'          => 'Accounting Officer',
                'email'         => 'accounting@supply.local',
                'password'      => Hash::make('Supply@2026'),
                'department_id' => Department::where('code', 'FIN')->first()->id,
                'position'      => 'Chief Accountant',
                'phone'         => '09005556666',
                'is_active'     => true,
                'role'          => 'accounting',
            ],
            [
                'name'          => 'Accounting Staff',
                'email'         => 'accounting.staff@supply.local',
                'password'      => Hash::make('Supply@2026'),
                'department_id' => Department::where('code', 'FIN')->first()->id,
                'position'      => 'Accounting Assistant',
                'phone'         => '09005556667',
                'is_active'     => true,
                'role'          => 'accounting-staff',
            ],
            [
                'name'          => 'Regional Director',
                'email'         => 'director@supply.local',
                'password'      => Hash::make('Supply@2026'),
                'department_id' => Department::where('code', 'ADM')->first()->id,
                'position'      => 'Regional Director',
                'phone'         => '09006667777',
                'is_active'     => true,
                'role'          => 'regional-director',
            ],
            [
                'name'          => 'RD Staff',
                'email'         => 'rd.staff@supply.local',
                'password'      => Hash::make('Supply@2026'),
                'department_id' => Department::where('code', 'ADM')->first()->id,
                'position'      => 'RD Executive Staff',
                'phone'         => '09006667778',
                'is_active'     => true,
                'role'          => 'rd-staff',
            ],
            [
                'name'          => 'Assistant Regional Director',
                'email'         => 'ard@example.com',
                'password'      => Hash::make('Supply@2026'),
                'department_id' => Department::where('code', 'ADM')->first()->id,
                'position'      => 'Assistant Regional Director',
                'phone'         => '09009998888',
                'is_active'     => true,
                'role'          => 'assistant-regional-director',
            ],
            [
                'name'          => 'ARD Staff',
                'email'         => 'ard.staff@supply.local',
                'password'      => Hash::make('Supply@2026'),
                'department_id' => Department::where('code', 'ADM')->first()->id,
                'position'      => 'ARD Executive Staff',
                'phone'         => '09009998889',
                'is_active'     => true,
                'role'          => 'ard-staff',
            ],
            [
                'name'          => 'Client User',
                'email'         => 'client@example.com',
                'password'      => Hash::make('Supply@2026'),
                'department_id' => Department::where('code', 'IT')->first()->id,
                'position'      => 'Client',
                'phone'         => '09001112233',
                'is_active'     => true,
                'role'          => 'client',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            $user = User::firstOrCreate(['email' => $userData['email']], $userData);
            $user->syncRoles([$role]);
        }

        // --- Categories ---
        $categories = [
            ['name' => 'Office Supplies',      'code' => 'OFF'],
            ['name' => 'Cleaning Materials',    'code' => 'CLN'],
            ['name' => 'IT Equipment',          'code' => 'ITE'],
            ['name' => 'Paper Products',        'code' => 'PAP'],
            ['name' => 'Writing Instruments',   'code' => 'WRI'],
            ['name' => 'Furniture & Fixtures',  'code' => 'FUR'],
        ];
        foreach ($categories as $cat) {
            Category::firstOrCreate(['code' => $cat['code']], $cat);
        }

        // --- Suppliers ---
        $suppliers = [
            [
                'name'           => 'National Bookstore Supplies',
                'code'           => 'NBS',
                'contact_person' => 'Maria Santos',
                'email'          => 'supplies@nbs.com.ph',
                'phone'          => '(02) 8123-4567',
                'address'        => '123 Recto Ave, Manila',
                'city'           => 'Manila',
                'status'         => 'active',
            ],
            [
                'name'           => 'Officemate Philippines',
                'code'           => 'OMP',
                'contact_person' => 'Juan dela Cruz',
                'email'          => 'orders@officemate.ph',
                'phone'          => '(02) 8765-4321',
                'address'        => '456 Shaw Blvd, Mandaluyong',
                'city'           => 'Mandaluyong',
                'status'         => 'active',
            ],
            [
                'name'           => 'TechWorld IT Solutions',
                'code'           => 'TWS',
                'contact_person' => 'Ana Reyes',
                'email'          => 'sales@techworld.ph',
                'phone'          => '(02) 8987-6543',
                'address'        => '789 EDSA, Pasay',
                'city'           => 'Pasay',
                'status'         => 'active',
            ],
        ];
        foreach ($suppliers as $sup) {
            Supplier::firstOrCreate(['code' => $sup['code']], $sup);
        }

        // --- Inventory Items ---
        $offCat   = Category::where('code', 'OFF')->first();
        $clnCat   = Category::where('code', 'CLN')->first();
        $iteCat   = Category::where('code', 'ITE')->first();
        $papCat   = Category::where('code', 'PAP')->first();
        $wriCat   = Category::where('code', 'WRI')->first();
        $nbs      = Supplier::where('code', 'NBS')->first();
        $omp      = Supplier::where('code', 'OMP')->first();
        $tws      = Supplier::where('code', 'TWS')->first();

        $items = [
            ['item_code'=>'OFF-001','name'=>'Stapler (Standard)','category_id'=>$offCat->id,'supplier_id'=>$nbs->id,'unit'=>'piece','quantity'=>50,'reorder_level'=>10,'unit_cost'=>85.00,'status'=>'active'],
            ['item_code'=>'OFF-002','name'=>'Scotch Tape (1 inch)','category_id'=>$offCat->id,'supplier_id'=>$nbs->id,'unit'=>'roll','quantity'=>120,'reorder_level'=>20,'unit_cost'=>18.00,'status'=>'active'],
            ['item_code'=>'OFF-003','name'=>'Paper Clip (Box)','category_id'=>$offCat->id,'supplier_id'=>$nbs->id,'unit'=>'box','quantity'=>8,'reorder_level'=>15,'unit_cost'=>25.00,'status'=>'active'],
            ['item_code'=>'PAP-001','name'=>'Bond Paper A4 (500 sheets)','category_id'=>$papCat->id,'supplier_id'=>$omp->id,'unit'=>'ream','quantity'=>200,'reorder_level'=>30,'unit_cost'=>220.00,'status'=>'active'],
            ['item_code'=>'PAP-002','name'=>'Bond Paper Legal (500 sheets)','category_id'=>$papCat->id,'supplier_id'=>$omp->id,'unit'=>'ream','quantity'=>80,'reorder_level'=>20,'unit_cost'=>235.00,'status'=>'active'],
            ['item_code'=>'WRI-001','name'=>'Ballpen Black (Box)','category_id'=>$wriCat->id,'supplier_id'=>$nbs->id,'unit'=>'box','quantity'=>45,'reorder_level'=>10,'unit_cost'=>120.00,'status'=>'active'],
            ['item_code'=>'WRI-002','name'=>'Ballpen Blue (Box)','category_id'=>$wriCat->id,'supplier_id'=>$nbs->id,'unit'=>'box','quantity'=>38,'reorder_level'=>10,'unit_cost'=>120.00,'status'=>'active'],
            ['item_code'=>'WRI-003','name'=>'Whiteboard Marker (Box)','category_id'=>$wriCat->id,'supplier_id'=>$nbs->id,'unit'=>'box','quantity'=>12,'reorder_level'=>5,'unit_cost'=>180.00,'status'=>'active'],
            ['item_code'=>'CLN-001','name'=>'Liquid Soap (500ml)','category_id'=>$clnCat->id,'supplier_id'=>$omp->id,'unit'=>'bottle','quantity'=>30,'reorder_level'=>10,'unit_cost'=>65.00,'status'=>'active'],
            ['item_code'=>'CLN-002','name'=>'Disinfectant Spray (500ml)','category_id'=>$clnCat->id,'supplier_id'=>$omp->id,'unit'=>'bottle','quantity'=>7,'reorder_level'=>10,'unit_cost'=>95.00,'status'=>'active'],
            ['item_code'=>'CLN-003','name'=>'Garbage Bag (10 pcs/pack)','category_id'=>$clnCat->id,'supplier_id'=>$omp->id,'unit'=>'pack','quantity'=>40,'reorder_level'=>15,'unit_cost'=>55.00,'status'=>'active'],
            ['item_code'=>'ITE-001','name'=>'USB Flash Drive 32GB','category_id'=>$iteCat->id,'supplier_id'=>$tws->id,'unit'=>'piece','quantity'=>15,'reorder_level'=>5,'unit_cost'=>350.00,'status'=>'active'],
            ['item_code'=>'ITE-002','name'=>'HDMI Cable 1.5m','category_id'=>$iteCat->id,'supplier_id'=>$tws->id,'unit'=>'piece','quantity'=>10,'reorder_level'=>3,'unit_cost'=>280.00,'status'=>'active'],
            ['item_code'=>'ITE-003','name'=>'Ethernet Cable 3m','category_id'=>$iteCat->id,'supplier_id'=>$tws->id,'unit'=>'piece','quantity'=>20,'reorder_level'=>5,'unit_cost'=>150.00,'status'=>'active'],
        ];

        $admin = User::where('email', 'admin@supply.local')->first();
        foreach ($items as $itemData) {
            $qty = $itemData['quantity'];
            $item = InventoryItem::firstOrCreate(['item_code' => $itemData['item_code']], $itemData);
            if ($item->wasRecentlyCreated) {
                InventoryHistory::create([
                    'inventory_item_id' => $item->id,
                    'user_id'           => $admin->id,
                    'type'              => 'initial',
                    'quantity'          => $qty,
                    'quantity_before'   => 0,
                    'quantity_after'    => $qty,
                    'unit_cost'         => $item->unit_cost,
                    'notes'             => 'Initial stock entry',
                ]);
            }
        }

        // --- Sample Supply Request ---
        $staff = User::where('email', 'staff@supply.local')->first();
        $officer = User::where('email', 'officer@supply.local')->first();
        $itDept  = Department::where('code', 'IT')->first();
        $bondPaper = InventoryItem::where('item_code', 'PAP-001')->first();
        $ballpen   = InventoryItem::where('item_code', 'WRI-001')->first();

        if (!SupplyRequest::where('request_number', 'REQ-' . date('Ym') . '-0001')->exists()) {
            $req = SupplyRequest::create([
                'request_number' => SupplyRequest::generateRequestNumber(),
                'requester_id'   => $staff->id,
                'department_id'  => $itDept->id,
                'approved_by'    => $officer->id,
                'issued_by'      => $officer->id,
                'status'         => 'issued',
                'purpose'        => 'Monthly office supply replenishment for IT Department',
                'approved_at'    => now()->subDays(3),
                'issued_at'      => now()->subDays(2),
                'needed_by'      => now()->subDays(1),
            ]);

            RequestItem::create([
                'supply_request_id'  => $req->id,
                'inventory_item_id'  => $bondPaper->id,
                'quantity_requested' => 5,
                'quantity_approved'  => 5,
                'quantity_issued'    => 5,
            ]);
            RequestItem::create([
                'supply_request_id'  => $req->id,
                'inventory_item_id'  => $ballpen->id,
                'quantity_requested' => 2,
                'quantity_approved'  => 2,
                'quantity_issued'    => 2,
            ]);

            // Sample issuance
            $issuance = Issuance::create([
                'issuance_number'   => Issuance::generateIssuanceNumber(),
                'supply_request_id' => $req->id,
                'issued_to'         => $staff->id,
                'issued_by'         => $officer->id,
                'department_id'     => $itDept->id,
                'remarks'           => 'Issued per approved request.',
                'issued_at'         => now()->subDays(2),
            ]);

            IssuanceItem::create([
                'issuance_id'       => $issuance->id,
                'inventory_item_id' => $bondPaper->id,
                'quantity'          => 5,
                'unit_cost'         => $bondPaper->unit_cost,
            ]);
            IssuanceItem::create([
                'issuance_id'       => $issuance->id,
                'inventory_item_id' => $ballpen->id,
                'quantity'          => 2,
                'unit_cost'         => $ballpen->unit_cost,
            ]);
        }

        $this->command->info('✅ Supply Management System seeded successfully!');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Administrator', 'admin@supply.local',   'Admin@1234'],
                ['Supply Officer','officer@supply.local',  'Officer@1234'],
                ['Staff',         'staff@supply.local',    'Staff@1234'],
                ['Auditor',       'auditor@supply.local',  'Auditor@1234'],
            ]
        );
    }
}
