<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add barcode-related product attributes (EAN, UPC, barcode_type)
     * via Bagisto EAV system.
     */
    public function up(): void
    {
        $now = now();

        // Get next attribute position
        $maxPosition = DB::table('attributes')->max('position') ?? 28;

        // Create barcode attributes
        $attributes = [
            [
                'code' => 'barcode',
                'admin_name' => 'Barcode (EAN/UPC/Custom)',
                'type' => 'text',
                'validation' => null,
                'position' => $maxPosition + 1,
                'is_required' => 0,
                'is_unique' => 1,
                'value_per_locale' => 0,
                'value_per_channel' => 0,
                'is_filterable' => 0,
                'is_configurable' => 0,
                'is_user_defined' => 1,
                'is_visible_on_front' => 0,
                'is_comparable' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'barcode_type',
                'admin_name' => 'Barcode Type',
                'type' => 'select',
                'validation' => null,
                'position' => $maxPosition + 2,
                'is_required' => 0,
                'is_unique' => 0,
                'value_per_locale' => 0,
                'value_per_channel' => 0,
                'is_filterable' => 1,
                'is_configurable' => 0,
                'is_user_defined' => 1,
                'is_visible_on_front' => 0,
                'is_comparable' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($attributes as $attrData) {
            $attrId = DB::table('attributes')->insertGetId($attrData);

            // Add translations (EN and ES)
            $label = $attrData['code'] === 'barcode' ? 'Barcode' : 'Barcode Type';
            $labelEs = $attrData['code'] === 'barcode' ? 'Código de Barras' : 'Tipo de Código de Barras';

            DB::table('attribute_translations')->insert([
                ['attribute_id' => $attrId, 'locale' => 'en', 'name' => $label],
                ['attribute_id' => $attrId, 'locale' => 'es', 'name' => $labelEs],
            ]);

            // Add select options for barcode_type
            if ($attrData['code'] === 'barcode_type') {
                $options = [
                    ['EAN-13', 'EAN-13'],
                    ['EAN-8', 'EAN-8'],
                    ['UPC-A', 'UPC-A'],
                    ['UPC-E', 'UPC-E'],
                    ['CODE-128', 'Code 128'],
                    ['CODE-39', 'Code 39'],
                    ['QR', 'QR Code'],
                ];

                foreach ($options as $i => [$adminName, $label]) {
                    $optionId = DB::table('attribute_options')->insertGetId([
                        'attribute_id' => $attrId,
                        'admin_name' => $adminName,
                        'sort_order' => $i + 1,
                    ]);

                    DB::table('attribute_option_translations')->insert([
                        ['attribute_option_id' => $optionId, 'locale' => 'en', 'label' => $label],
                        ['attribute_option_id' => $optionId, 'locale' => 'es', 'label' => $label],
                    ]);
                }
            }

            // Add to all attribute families under a "Barcode" group
            $families = DB::table('attribute_families')->pluck('id');

            foreach ($families as $familyId) {
                // Create or find "Barcode" attribute group
                $groupId = DB::table('attribute_groups')
                    ->where('attribute_family_id', $familyId)
                    ->where('code', 'barcode')
                    ->value('id');

                if (! $groupId) {
                    $maxGroupPos = DB::table('attribute_groups')
                        ->where('attribute_family_id', $familyId)
                        ->max('position') ?? 0;

                    $groupId = DB::table('attribute_groups')->insertGetId([
                        'name' => 'Barcode',
                        'code' => 'barcode',
                        'column' => 1,
                        'is_user_defined' => 1,
                        'position' => $maxGroupPos + 1,
                        'attribute_family_id' => $familyId,
                    ]);
                }

                // Map attribute to group
                $maxMapping = DB::table('attribute_group_mappings')
                    ->where('attribute_group_id', $groupId)
                    ->max('position') ?? 0;

                DB::table('attribute_group_mappings')->insert([
                    'attribute_id' => $attrId,
                    'attribute_group_id' => $groupId,
                    'position' => $maxMapping + 1,
                ]);
            }
        }
    }

    public function down(): void
    {
        $codes = ['barcode', 'barcode_type'];

        foreach ($codes as $code) {
            $attr = DB::table('attributes')->where('code', $code)->first();

            if ($attr) {
                DB::table('attribute_translations')->where('attribute_id', $attr->id)->delete();
                DB::table('attribute_group_mappings')->where('attribute_id', $attr->id)->delete();
                DB::table('product_attribute_values')->where('attribute_id', $attr->id)->delete();

                if ($code === 'barcode_type') {
                    $optionIds = DB::table('attribute_options')->where('attribute_id', $attr->id)->pluck('id');
                    DB::table('attribute_option_translations')->whereIn('attribute_option_id', $optionIds)->delete();
                    DB::table('attribute_options')->where('attribute_id', $attr->id)->delete();
                }

                DB::table('attributes')->where('id', $attr->id)->delete();
            }
        }

        // Remove barcode attribute groups if empty
        $groups = DB::table('attribute_groups')->where('code', 'barcode')->get();

        foreach ($groups as $group) {
            $hasAttrs = DB::table('attribute_group_mappings')
                ->where('attribute_group_id', $group->id)
                ->exists();

            if (! $hasAttrs) {
                DB::table('attribute_groups')->where('id', $group->id)->delete();
            }
        }
    }
};
