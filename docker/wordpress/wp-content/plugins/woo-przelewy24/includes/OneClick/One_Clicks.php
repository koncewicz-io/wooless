<?php

namespace WC_P24\OneClick;

use WC_P24\Models\Database\Reference;
use WC_P24\Utilities\Account_Page;
use WC_P24\Utilities\Ajax;

class One_Clicks
{
    private Account_Page $page;

    public function __construct()
    {
        new Client_Page();
    }
}
