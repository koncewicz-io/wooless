<?php

namespace WC_P24\Utilities;

use WC_P24\Render;

class Notice
{
    const WARNING = 'warning';
    const ERROR = 'error';
    const SUCCESS = 'success';
    private ?string $message;
    private ?string $type;
    private int $order = 10;
    private bool $dismissible = false;

    public function __construct(string $message, $type = self::WARNING, bool $dismissible = false, int $order = 10)
    {
        $this->message = $message;
        $this->type = $type;
        $this->order = $order;
        $this->dismissible = $dismissible;

        if ($this->message) {
            add_action('admin_notices', [$this, 'render'], $this->order);
        }
    }

    public function render(): void
    {
        Render::template('admin/notice', [
            'message' => $this->message,
            'type' => $this->type,
            'dismissible' => $this->dismissible
        ]);
    }
}
