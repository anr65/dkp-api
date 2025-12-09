<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Subscription;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SubscriptionListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'subscriptions' => Subscription::withCount('durations', 'userSubscriptions')
                ->orderBy('id', 'desc')
                ->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Подписки';
    }

    public function description(): ?string
    {
        return 'Управление подписками';
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить')
                ->icon('bs.plus-circle')
                ->modal('createSubscriptionModal')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('subscriptions', [
                TD::make('id', 'ID')
                    ->sort()
                    ->width('80px'),

                TD::make('name', 'Название')
                    ->sort()
                    ->filter(Input::make()),

                TD::make('status', 'Статус')
                    ->sort()
                    ->render(fn (Subscription $subscription) => $subscription->status === 'active'
                        ? '<span class="badge bg-success">Активна</span>'
                        : '<span class="badge bg-secondary">Неактивна</span>'),

                TD::make('durations_count', 'Тарифы')
                    ->align(TD::ALIGN_CENTER),

                TD::make('user_subscriptions_count', 'Покупок')
                    ->align(TD::ALIGN_CENTER),

                TD::make('created_at', 'Создана')
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make('Действия')
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Subscription $subscription) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            ModalToggle::make('Редактировать')
                                ->icon('bs.pencil')
                                ->modal('editSubscriptionModal')
                                ->modalTitle('Редактирование подписки')
                                ->method('save')
                                ->asyncParameters([
                                    'subscription' => $subscription->id,
                                ]),

                            Button::make('Удалить')
                                ->icon('bs.trash3')
                                ->confirm('Вы уверены, что хотите удалить эту подписку?')
                                ->method('remove', [
                                    'id' => $subscription->id,
                                ]),
                        ])),
            ]),

            Layout::modal('createSubscriptionModal', Layout::rows([
                Input::make('subscription.name')
                    ->title('Название')
                    ->required(),

                Select::make('subscription.status')
                    ->title('Статус')
                    ->options([
                        'active' => 'Активна',
                        'inactive' => 'Неактивна',
                    ])
                    ->value('active'),
            ]))->title('Создание подписки'),

            Layout::modal('editSubscriptionModal', Layout::rows([
                Input::make('subscription.name')
                    ->title('Название')
                    ->required(),

                Select::make('subscription.status')
                    ->title('Статус')
                    ->options([
                        'active' => 'Активна',
                        'inactive' => 'Неактивна',
                    ]),
            ]))->title('Редактирование подписки')
                ->deferred('loadSubscription'),
        ];
    }

    public function loadSubscription(Subscription $subscription): iterable
    {
        return [
            'subscription' => $subscription,
        ];
    }

    public function save(Request $request, ?Subscription $subscription = null): void
    {
        $validated = $request->validate([
            'subscription.name' => 'required|string|max:255',
            'subscription.status' => 'required|in:active,inactive',
        ]);

        $subscription = $subscription ?? new Subscription();
        $subscription->fill($validated['subscription'])->save();

        Toast::info($subscription->wasRecentlyCreated ? 'Подписка создана' : 'Подписка обновлена');
    }

    public function remove(Request $request): void
    {
        Subscription::findOrFail($request->get('id'))->delete();

        Toast::info('Подписка удалена');
    }
}
