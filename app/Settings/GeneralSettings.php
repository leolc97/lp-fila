<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;
    public string $site_logo;
    public string $site_favicon;
    public string $site_footer;


    public static function group(): string
    {
        return 'general';
    }

}
