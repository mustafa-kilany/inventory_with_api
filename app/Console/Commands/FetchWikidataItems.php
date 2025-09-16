<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Item;
use Illuminate\Support\Str;

class FetchWikidataItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:fetch-wikidata-items {--limit=50 : Number of items to fetch}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Fetch office supply items from Wikidata SPARQL API and populate the inventory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Fetching office supply items from Wikidata...');
        
        // Office supply items to search for
        $officeItems = [
            'ballpoint pen', 'pencil', 'stapler', 'A4 paper', 'marker', 'eraser', 
            'scissors', 'tape dispenser', 'highlighter', 'paper clip', 'binder',
            'notebook', 'folder', 'calculator', 'ruler', 'sticky notes',
            'paper shredder', 'hole punch', 'rubber stamp', 'desk organizer',
            'paper tray', 'letter opener', 'correction fluid', 'ink cartridge',
            'printer paper', 'envelope', 'sticky tape', 'push pin', 'paper fastener',
            'clipboard', 'desk calendar', 'desk lamp', 'filing cabinet',
            'whiteboard', 'whiteboard marker', 'blackboard', 'chalk'
        ];

        // Convert labels to SPARQL VALUES format
        $values = implode(' ', array_map(fn($label) => "\"$label\"@en", $officeItems));

        // SPARQL query to get office supply items
        $sparql = <<<SPARQL
SELECT ?item ?itemLabel ?itemDescription ?image WHERE {
  VALUES ?needle { $values }
  ?item rdfs:label ?needle .
  FILTER(LANG(?needle) = "en")
  OPTIONAL { ?item schema:description ?itemDescription FILTER(LANG(?itemDescription)="en") }
  OPTIONAL { ?item wdt:P18 ?image }
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
}
LIMIT {$this->option('limit')}
SPARQL;

        try {
            $this->info('📡 Making API request to Wikidata...');
            
            // Make HTTP request to Wikidata SPARQL endpoint
            $response = Http::withHeaders([
                'User-Agent' => 'InventoryPro/1.0 (inventory@example.com)',
                'Accept' => 'application/json'
            ])->timeout(30)->get('https://query.wikidata.org/sparql', [
                'query' => $sparql,
                'format' => 'json',
            ]);

            if (!$response->successful()) {
                $this->error('❌ Failed to fetch data from Wikidata API');
                $this->error('Status: ' . $response->status());
                return 1;
            }

            $data = $response->json();
            $bindings = $data['results']['bindings'] ?? [];
            
            if (empty($bindings)) {
                $this->warn('⚠️ No items found in Wikidata response');
                return 0;
            }

            $this->info("📦 Found {" . count($bindings) . "} items from Wikidata");
            $this->newLine();

            $bar = $this->output->createProgressBar(count($bindings));
            $bar->start();

            $created = 0;
            $skipped = 0;

            foreach ($bindings as $binding) {
                $wikidataQid = basename($binding['item']['value']);
                $name = $binding['itemLabel']['value'] ?? null;
                $description = $binding['itemDescription']['value'] ?? null;
                $imageUrl = $binding['image']['value'] ?? null;

                if (!$name) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Generate SKU from name and Wikidata QID
                $sku = 'WD-' . strtoupper(Str::slug(Str::limit($name, 10, ''), '')) . '-' . $wikidataQid;

                // Check if item already exists
                $existingItem = Item::where('sku', $sku)->first();
                
                if ($existingItem) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Create new item
                try {
                    Item::create([
                        'name' => $name,
                        'sku' => $sku,
                        'description' => $description ? Str::limit($description, 500) : null,
                        'category' => 'Office Supplies',
                        'unit' => $this->determineUnit($name),
                        'quantity_on_hand' => rand(0, 100),
                        'reorder_level' => rand(5, 20),
                        'unit_price' => $this->estimatePrice($name),
                        'supplier' => 'Wikidata Import',
                        'location' => 'Warehouse-A',
                        'is_active' => true,
                        'wikidata_qid' => $wikidataQid,
                        'image_url' => $imageUrl,
                    ]);
                    
                    $created++;
                } catch (\Exception $e) {
                    $this->error("Failed to create item: {$name} - {$e->getMessage()}");
                    $skipped++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            $this->info("✅ Import completed!");
            $this->info("📈 Created: {$created} items");
            $this->info("⏭️ Skipped: {$skipped} items");
            $this->newLine();

            if ($created > 0) {
                $this->info("🎉 You can now view the imported items in your admin panel at /admin");
                $this->info("🔗 API endpoint: http://127.0.0.1:8000/api/v1/items");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error fetching data from Wikidata: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Determine appropriate unit for an item based on its name
     */
    private function determineUnit(string $name): string
    {
        $name = strtolower($name);
        
        if (str_contains($name, 'paper') || str_contains($name, 'sheet')) {
            return 'sheets';
        }
        if (str_contains($name, 'pen') || str_contains($name, 'pencil') || str_contains($name, 'marker')) {
            return 'pieces';
        }
        if (str_contains($name, 'tape') || str_contains($name, 'roll')) {
            return 'rolls';
        }
        if (str_contains($name, 'box') || str_contains($name, 'pack')) {
            return 'boxes';
        }
        if (str_contains($name, 'bottle') || str_contains($name, 'fluid')) {
            return 'bottles';
        }
        
        return 'pieces'; // Default unit
    }

    /**
     * Estimate price for an item based on its name
     */
    private function estimatePrice(string $name): float
    {
        $name = strtolower($name);
        
        // Basic price estimation based on item type
        if (str_contains($name, 'calculator') || str_contains($name, 'shredder')) {
            return rand(2500, 15000) / 100; // $25-150
        }
        if (str_contains($name, 'cabinet') || str_contains($name, 'desk')) {
            return rand(10000, 50000) / 100; // $100-500
        }
        if (str_contains($name, 'lamp') || str_contains($name, 'organizer')) {
            return rand(1500, 8000) / 100; // $15-80
        }
        if (str_contains($name, 'paper') && str_contains($name, 'a4')) {
            return rand(800, 1500) / 100; // $8-15 per ream
        }
        if (str_contains($name, 'pen') || str_contains($name, 'pencil')) {
            return rand(50, 300) / 100; // $0.50-3.00
        }
        if (str_contains($name, 'stapler') || str_contains($name, 'hole punch')) {
            return rand(500, 2500) / 100; // $5-25
        }
        
        return rand(100, 2000) / 100; // Default: $1-20
    }
}