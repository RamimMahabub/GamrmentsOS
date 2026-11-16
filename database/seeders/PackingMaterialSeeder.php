<?php

namespace Database\Seeders;

use App\Models\PackingMaterial;
use App\Models\User;
use App\Support\PackingStockLedger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PackingMaterialSeeder extends Seeder
{
    /**
     * Seed packaging materials with stock ready to pack 2,500 pieces.
     */
    public function run(): void
    {
        $user = User::first();
        if ($user) {
            Auth::login($user);
        }

        DB::transaction(function (): void {
            $ledger = app(PackingStockLedger::class);
            $today = now()->toDateString();

            // 1. Poly Bags (1 per piece = 2,500 units)
            $polyBag = PackingMaterial::firstOrCreate(
                ['name' => 'Clear Garment Poly Bag 10x14', 'category' => 'poly_bag'],
                [
                    'unit' => 'pcs',
                    'cost_per_unit' => '0.050000',
                    'min_stock_alert' => 250,
                    'is_active' => true,
                ]
            );
            if ($polyBag->movements()->doesntExist()) {
                $ledger->write(
                    $polyBag,
                    'opening',
                    '2500.000',
                    $today,
                    unitCost: '0.050000',
                    note: 'Opening stock for 2,500 pcs packaging run'
                );
            }

            // 2. Size Stickers (for S, M, L, XL, 2XL, 3XL, 4XL, 5XL, 6XL, 7XL - 2,500 each so any size mix is covered)
            $sizes = ['S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL', '6XL', '7XL'];
            foreach ($sizes as $size) {
                $sticker = PackingMaterial::firstOrCreate(
                    ['name' => "Size Sticker - {$size}", 'category' => 'size_sticker', 'size_code' => $size],
                    [
                        'unit' => 'pcs',
                        'cost_per_unit' => '0.010000',
                        'min_stock_alert' => 100,
                        'is_active' => true,
                    ]
                );
                if ($sticker->movements()->doesntExist()) {
                    $ledger->write(
                        $sticker,
                        'opening',
                        '2500.000',
                        $today,
                        unitCost: '0.010000',
                        note: "Opening stock for size {$size} stickers"
                    );
                }
            }

            // 3. Other Sticker(s) (1 per piece = 2,500 units)
            $barcodeSticker = PackingMaterial::firstOrCreate(
                ['name' => 'Barcode / Price Tag Sticker', 'category' => 'other_sticker'],
                [
                    'unit' => 'pcs',
                    'cost_per_unit' => '0.020000',
                    'min_stock_alert' => 250,
                    'is_active' => true,
                ]
            );
            if ($barcodeSticker->movements()->doesntExist()) {
                $ledger->write(
                    $barcodeSticker,
                    'opening',
                    '2500.000',
                    $today,
                    unitCost: '0.020000',
                    note: 'Opening stock for 2,500 pcs barcode stickers'
                );
            }

            // 4. Packet Materials (6 pcs per packet = 417 packets for 2,500 pcs; seeding 500 packet bags)
            $packetBag = PackingMaterial::firstOrCreate(
                ['name' => '6-Piece Master Packet Bag', 'category' => 'packet_material'],
                [
                    'unit' => 'pcs',
                    'cost_per_unit' => '0.150000',
                    'min_stock_alert' => 50,
                    'is_active' => true,
                ]
            );
            if ($packetBag->movements()->doesntExist()) {
                $ledger->write(
                    $packetBag,
                    'opening',
                    '500.000',
                    $today,
                    unitCost: '0.150000',
                    note: 'Opening stock: 500 packet bags (capacity for 3,000 pcs at 6 pcs/pkt)'
                );
            }
        });
    }
}
