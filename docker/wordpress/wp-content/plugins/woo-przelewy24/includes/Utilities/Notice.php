<?php

namespace WC_P24\Utilities;

use WC_P24\Render;

class Notice
{
    const WARNING = 'warning';
    const ERROR = 'error';
    const SUCCESS = 'success';
    const INFO = 'info';
    private ?string $message;
    private ?string $type;
    private int $order = 10;
    private bool $dismissible = false;

    private $callback;

    public function __construct(string $message, $type = self::WARNING, bool $dismissible = false, int $order = 10, $callback = null)
    {
        $this->message = $message;
        $this->type = $type;
        $this->order = $order;
        $this->dismissible = $dismissible;
        $this->callback = $callback;

        if ($this->message) {
            add_action('admin_notices', [$this, 'render'], $this->order);
        }
    }

    public function render(): void
    {
        if (is_callable($this->callback)) {
            $pass_condition = call_user_func($this->callback);
            if (!$pass_condition) return;
        }

        Render::template('admin/notice', [
            'message' => $this->message,
            'type' => $this->type,
            'dismissible' => $this->dismissible
        ]);
    }
}
