@if (filled($brand = resolve(\App\Settings\GeneralSettings::class)->site_name))
    <div @class([
        'filament-brand text-xl font-bold tracking-tight',
        'dark:text-white' => config('filament.dark_mode'),
    ])>
        {{ $brand }}
    </div>
@endif

