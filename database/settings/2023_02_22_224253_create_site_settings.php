<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateSiteSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', '');
        $this->migrator->add('general.site_logo', '');
        $this->migrator->add('general.site_favicon', '');
        $this->migrator->add('general.site_footer', '');

    }
}
