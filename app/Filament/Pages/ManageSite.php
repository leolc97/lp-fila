<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;

class ManageSite extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Gerenciar';

    protected static string $settings = GeneralSettings::class;

    protected ?string $heading = 'Gerenciar';

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('Geral')->tabs([
                    Tab::make('Site')
                        ->schema([
                            TextInput::make('site_name')
                                ->required()
                                ->label('Nome do site')
                                ->placeholder('Nome do site'),
                            FileUpload::make('site_logo')
                                ->label('Logo')
                                ->image(),
                            FileUpload::make('site_favicon')
                                ->label('Favicon')
                                ->image(),
                            RichEditor::make('site_footer')
                                ->label('Rodapé')
                                ->placeholder('Rodapé'),
                        ]),
                    Tab::make('SEO')
                        ->schema([
                            TextInput::make('site_name')
                                ->required()
                                ->label('Nome do site')
                                ->placeholder('Nome do site'),
                        ]),
                    Tab::make('Social')
                        ->schema([
                            TextInput::make('site_name')
                                ->required()
                                ->label('Nome do site')
                                ->placeholder('Nome do site'),
                        ]),
                    Tab::make('Contatos')
                        ->schema([
                            TextInput::make('site_name')
                                ->required()
                                ->label('Nome do site')
                                ->placeholder('Nome do site'),
                        ]),
                ]
            ),
        ];
    }
}
