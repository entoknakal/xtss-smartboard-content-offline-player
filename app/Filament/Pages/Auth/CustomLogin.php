<?php

namespace App\Filament\Pages\Auth;

use App\Models\Settings;
use Filament\Actions\Action;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;

class CustomLogin extends BaseLogin
{
    protected string $view = 'filament.pages.auth.custom-login';

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('authenticate')
            ->footer([
                Grid::make()
                    ->schema([
                        Action::make('back')
                            ->label('Halaman Utama')
                            ->icon(Heroicon::Home)
                            ->color('gray')
                            ->url(fn(): string => route('dashboard'))
                            ->hidden(
                                function () {
                                    $uuid_sekolah = Settings::where('name', 'license_id')->first()->uuid;

                                    if (null === $uuid_sekolah || '' === $uuid_sekolah) {
                                        return true;
                                    }

                                    return false;
                                }
                            ),
                        Actions::make($this->getFormActions())
                            ->alignment($this->getFormActionsAlignment())
                            ->fullWidth($this->hasFullWidthFormActions())
                            ->key('form-actions'),
                    ])
                    ->columns(
                        function () {
                            $uuid_sekolah = Settings::where('name', 'license_id')->first()->uuid;
                            if (null === $uuid_sekolah || '' === $uuid_sekolah) {
                                return 1;
                            }

                            return 2;
                        }
                    )
            ])
            ->visible(fn(): bool => blank($this->userUndertakingMultiFactorAuthentication));
    }
}
