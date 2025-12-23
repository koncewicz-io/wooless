<?php

namespace WC_P24\OneClick;

use Exception;
use WC_P24\API\Resources\Blik_Resource;
use WC_P24\Models\Database\Reference;
use WC_P24\Utilities\Encryption;

class One_Clicks
{
    public function __construct()
    {
        new Client_Page();
    }

    public static function get_blik_aliases(): array
    {
        $items = [];

        try {
            $current_user = WC()->customer;
            $client = new Blik_Resource();
            $response = $client->get_aliases($current_user->get_email());

            if (!empty($response['data'])) {
                foreach ($response['data'] as $i => $item) {
                    if (!($item['type'] === 'UID' && $item['status'] === 'REGISTERED'))
                        continue;

                    $id = $i + $current_user->get_id();
                    $ref = new Reference();

                    $ref->parse([
                        'id' => $id,
                        'user_id' => $current_user->get_id(),
                        'ref_id' => $item['value'],
                        'valid_to' => $item['expirationDate'],
                        'info' => __('BLIK one click', 'woocommerce-p24'),
                        'type' => Reference::TYPE_BLIK,
                        'status' => Reference::STATUS_REGISTERED,
                        '_hash' => Encryption::encrypt($id . '_' . $item['value'])
                    ]);

                    $items[] = $ref;
                }
            }
        } catch (Exception $e) {
        }

        return $items;
    }

    public static function get_blik_alias(int $key): ?Reference
    {
        $aliases = self::get_blik_aliases();

        foreach ($aliases as $alias) {
            if ($key === $alias->get_id()) {
                $correct = $alias->_hash === Encryption::encrypt($alias->get_id() . '_' . $alias->get_ref_id());

                return $correct ? $alias : null;
            }
        }

        return null;
    }
}
