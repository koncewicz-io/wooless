<?php

namespace WC_P24\Models\Simple;

if (!defined('ABSPATH')) {
    exit;
}

class Card_Notification
{
    public int $order_id = 0;
    public string $session_id;
    public ?int $error_code = 0;
    public string $message = '';
    public ?string $reject_message = '';
    public ?Card_Simple $card = null;

    public function __construct(array $data)
    {
        $this->order_id = (int)$data['orderId'];
        $this->session_id = $data['sessionId'];
        $card = [];

        if (isset($data['result'])) {
            $result = $data['result'];

            $this->error_code = (int)str_replace('err', '', $result['error']);
            $this->message = $result['message'];

            if ($this->error_code > 0) {
                $this->reject_message = $result['rejectReason'];
            }

            if (isset($result['cardInfoData'])) {
                $card = $result['cardInfoData'];
            }
        }

        /*
         * Old handler for urlCardPaymentNotification
         * check 3ds key if exist probably is old notification
         */

        if (isset($data['3ds'])) {
            if (isset($data['errorCode'])) {
                $this->error_code = (int)str_replace('err', '', $data['errorCode']);
            }

            if ($this->error_code > 0) {
                $this->reject_message = $data['errorMessage'];
            }

            if (isset($data['maskedCCNumber'])) {
                $card = [
                    'refId' => $data['refId'],
                    'cardDate' => $data['ccExp'],
                    'mask' => $data['maskedCCNumber'],
                    'cardType' => $data['cardType'],
                    'hash' => $data['hash']
                ];
            }
        }

        if (!empty($card)) {
            $this->card = new Card_Simple($card);
        }
    }

    public function has_error(): bool
    {
        return $this->error_code > 0;
    }
}
