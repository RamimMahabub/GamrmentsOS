<?php

namespace App\Support;

use App\Models\Setting;

class UnderpantsSewingConfig
{
    public const DEFAULT_STEPS = [
        'over_lock' => [
            ['key' => 'ol_crotch', 'label' => 'Crotch Overlock', 'rate' => '10.00'],
            ['key' => 'ol_side', 'label' => 'Side Overlock', 'rate' => '10.00'],
        ],
        'lock' => [
            ['key' => 'lk_front', 'label' => 'Front Lock', 'rate' => '10.00'],
            ['key' => 'lk_hem', 'label' => 'Bottom Hem Lock', 'rate' => '10.00'],
        ],
        'elastic_join' => [
            ['key' => 'el_waist', 'label' => 'Waist Elastic Join', 'rate' => '12.00'],
        ],
    ];

    /**
     * @return array<string, array<int, array{key: string, label: string, rate?: string}>>
     */
    public static function getGroups(): array
    {
        return Setting::getValue('underpants_sewing_steps', self::DEFAULT_STEPS);
    }

    /**
     * Return flattened ordered list of all underpants sub-steps.
     *
     * @return array<int, array{group: string, key: string, label: string, rate?: string}>
     */
    public static function allSubSteps(): array
    {
        $groups = self::getGroups();
        $flat = [];

        foreach (['over_lock', 'lock', 'elastic_join'] as $groupKey) {
            $items = $groups[$groupKey] ?? [];
            foreach ($items as $item) {
                $flat[] = [
                    'group' => $groupKey,
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'rate' => (string) ($item['rate'] ?? '0'),
                ];
            }
        }

        return $flat;
    }

    /**
     * Save dynamic steps configuration with validation (1-8 sub-steps per group).
     */
    public static function saveGroups(array $groups): void
    {
        $sanitized = [];
        foreach (['over_lock', 'lock', 'elastic_join'] as $groupKey) {
            $items = $groups[$groupKey] ?? [];
            $validItems = [];
            foreach (array_slice($items, 0, 8) as $item) {
                $label = trim((string) ($item['label'] ?? ''));
                if ($label !== '') {
                    $key = ! empty($item['key']) ? preg_replace('/[^a-z0-9_]/', '', strtolower($item['key'])) : preg_replace('/[^a-z0-9_]/', '', strtolower($label));
                    $rate = isset($item['rate']) ? (string) $item['rate'] : '0';
                    $validItems[] = [
                        'key' => $key ?: 'step_'.uniqid(),
                        'label' => $label,
                        'rate' => $rate,
                    ];
                }
            }
            if (empty($validItems) && $groupKey === 'elastic_join') {
                $validItems[] = ['key' => 'el_waist', 'label' => 'Waist Elastic Join', 'rate' => '12.00'];
            }
            $sanitized[$groupKey] = $validItems;
        }

        Setting::putValue('underpants_sewing_steps', $sanitized);
    }
}
