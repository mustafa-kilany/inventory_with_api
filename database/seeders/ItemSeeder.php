<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Wireless Mouse',
                'sku' => 'MSE001',
                'description' => 'Ergonomic wireless mouse with USB receiver',
                'category' => 'Computer Accessories',
                'unit' => 'pieces',
                'quantity_on_hand' => 25,
                'reorder_level' => 10,
                'unit_price' => 29.99,
                'supplier' => 'TechCorp Inc.',
                'location' => 'Warehouse A-1',
            ],
            [
                'name' => 'A4 Copy Paper',
                'sku' => 'PPR001',
                'description' => '80gsm white A4 copy paper - 500 sheets per ream',
                'category' => 'Office Supplies',
                'unit' => 'reams',
                'quantity_on_hand' => 150,
                'reorder_level' => 50,
                'unit_price' => 4.99,
                'supplier' => 'Office Depot',
                'location' => 'Storage Room B-2',
            ],
            [
                'name' => 'Laptop Stand',
                'sku' => 'STA001',
                'description' => 'Adjustable aluminum laptop stand',
                'category' => 'Computer Accessories',
                'unit' => 'pieces',
                'quantity_on_hand' => 8,
                'reorder_level' => 5,
                'unit_price' => 45.00,
                'supplier' => 'ErgoTech Solutions',
                'location' => 'Warehouse A-2',
            ],
            [
                'name' => 'Blue Ballpoint Pens',
                'sku' => 'PEN001',
                'description' => 'Medium tip blue ballpoint pens - pack of 10',
                'category' => 'Office Supplies',
                'unit' => 'packs',
                'quantity_on_hand' => 45,
                'reorder_level' => 20,
                'unit_price' => 7.50,
                'supplier' => 'Stationery Plus',
                'location' => 'Storage Room B-1',
            ],
            [
                'name' => 'USB-C Cable',
                'sku' => 'CBL001',
                'description' => '2-meter USB-C to USB-A cable',
                'category' => 'Computer Accessories',
                'unit' => 'pieces',
                'quantity_on_hand' => 3,
                'reorder_level' => 15,
                'unit_price' => 12.99,
                'supplier' => 'Cable Connect',
                'location' => 'Warehouse A-1',
            ],
            [
                'name' => 'Sticky Notes',
                'sku' => 'STK001',
                'description' => 'Yellow sticky notes 3x3 inches - pack of 12',
                'category' => 'Office Supplies',
                'unit' => 'packs',
                'quantity_on_hand' => 22,
                'reorder_level' => 10,
                'unit_price' => 8.99,
                'supplier' => 'Office Depot',
                'location' => 'Storage Room B-1',
            ],
            [
                'name' => 'Mechanical Keyboard',
                'sku' => 'KBD001',
                'description' => 'RGB mechanical gaming keyboard with blue switches',
                'category' => 'Computer Accessories',
                'unit' => 'pieces',
                'quantity_on_hand' => 12,
                'reorder_level' => 8,
                'unit_price' => 89.99,
                'supplier' => 'Gaming Gear Co.',
                'location' => 'Warehouse A-2',
            ],
            [
                'name' => 'Whiteboard Markers',
                'sku' => 'MKR001',
                'description' => 'Assorted color whiteboard markers - pack of 8',
                'category' => 'Office Supplies',
                'unit' => 'packs',
                'quantity_on_hand' => 0,
                'reorder_level' => 5,
                'unit_price' => 11.99,
                'supplier' => 'Stationery Plus',
                'location' => 'Storage Room B-1',
            ],
            [
                'name' => 'Webcam HD 1080p',
                'sku' => 'CAM001',
                'description' => 'Full HD 1080p webcam with built-in microphone',
                'category' => 'Computer Accessories',
                'unit' => 'pieces',
                'quantity_on_hand' => 6,
                'reorder_level' => 10,
                'unit_price' => 65.00,
                'supplier' => 'VideoTech Inc.',
                'location' => 'Warehouse A-1',
            ],
            [
                'name' => 'File Folders',
                'sku' => 'FLD001',
                'description' => 'Manila file folders letter size - pack of 100',
                'category' => 'Office Supplies',
                'unit' => 'packs',
                'quantity_on_hand' => 18,
                'reorder_level' => 8,
                'unit_price' => 15.99,
                'supplier' => 'Office Depot',
                'location' => 'Storage Room B-2',
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
