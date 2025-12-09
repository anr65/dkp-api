<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;

class PlatformProvider extends OrchidServiceProvider
{
    public function boot(\Orchid\Platform\Dashboard $dashboard): void
    {
        parent::boot($dashboard);
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Главная')
                ->icon('bs.house')
                ->route(config('platform.index'))
                ->title('Навигация'),

            Menu::make('Подписки')
                ->icon('bs.credit-card')
                ->route('platform.subscriptions'),

            Menu::make('Покупки подписок')
                ->icon('bs.cart')
                ->route('platform.purchases')
                ->divider(),

            Menu::make('Пользователи')
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title('Управление доступом'),

            Menu::make('Роли')
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles'),
        ];
    }

    public function permissions(): array
    {
        return [
            ItemPermission::group('Система')
                ->addPermission('platform.systems.roles', 'Роли')
                ->addPermission('platform.systems.users', 'Пользователи'),
        ];
    }
}
