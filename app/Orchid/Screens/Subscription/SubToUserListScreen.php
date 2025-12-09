<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Subscription;

use App\Models\SubToUser;
use App\Models\Subscription;
use App\Models\SubscriptionDuration;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SubToUserListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'purchases' => SubToUser::with(['user', 'subscription', 'subscriptionDuration'])
                ->orderBy('id', 'desc')
                ->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Покупки подписок';
    }

    public function description(): ?string
    {
        return 'Список всех покупок подписок пользователями';
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить')
                ->icon('bs.plus-circle')
                ->modal('createPurchaseModal')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('purchases', [
                TD::make('id', 'ID')
                    ->sort()
                    ->width('80px'),

                TD::make('user_id', 'Пользователь')
                    ->render(fn (SubToUser $purchase) => $purchase->user
                        ? ($purchase->user->name ?: $purchase->user->telegram_id)
                        : '-'),

                TD::make('sub_id', 'Подписка')
                    ->render(fn (SubToUser $purchase) => $purchase->subscription?->name ?? '-'),

                TD::make('sub_dur_id', 'Тариф')
                    ->render(fn (SubToUser $purchase) => $purchase->subscriptionDuration
                        ? $purchase->subscriptionDuration->days . ' дн. / ' . $purchase->subscriptionDuration->price . ' ₽'
                        : '-'),

                TD::make('valid_thru', 'Действует до')
                    ->render(fn (SubToUser $purchase) => $purchase->valid_thru
                        ? ($purchase->valid_thru->isPast()
                            ? '<span class="text-danger">' . $purchase->valid_thru->format('d.m.Y H:i') . '</span>'
                            : '<span class="text-success">' . $purchase->valid_thru->format('d.m.Y H:i') . '</span>')
                        : '-'),

                TD::make('created_at', 'Куплено')
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make('Действия')
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (SubToUser $purchase) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            ModalToggle::make('Редактировать')
                                ->icon('bs.pencil')
                                ->modal('editPurchaseModal')
                                ->modalTitle('Редактирование покупки')
                                ->method('save')
                                ->asyncParameters([
                                    'purchase' => $purchase->id,
                                ]),

                            Button::make('Удалить')
                                ->icon('bs.trash3')
                                ->confirm('Вы уверены, что хотите удалить эту покупку?')
                                ->method('remove', [
                                    'id' => $purchase->id,
                                ]),
                        ])),
            ]),

            Layout::modal('createPurchaseModal', Layout::rows([
                Relation::make('purchase.user_id')
                    ->title('Пользователь')
                    ->fromModel(User::class, 'name')
                    ->displayAppend('full')
                    ->required(),

                Relation::make('purchase.sub_id')
                    ->title('Подписка')
                    ->fromModel(Subscription::class, 'name')
                    ->required(),

                Relation::make('purchase.sub_dur_id')
                    ->title('Тариф')
                    ->fromModel(SubscriptionDuration::class, 'days')
                    ->displayAppend('full')
                    ->required(),

                DateTimer::make('purchase.valid_thru')
                    ->title('Действует до')
                    ->format('Y-m-d H:i:s')
                    ->required(),
            ]))->title('Создание покупки'),

            Layout::modal('editPurchaseModal', Layout::rows([
                Relation::make('purchase.user_id')
                    ->title('Пользователь')
                    ->fromModel(User::class, 'name')
                    ->displayAppend('full')
                    ->required(),

                Relation::make('purchase.sub_id')
                    ->title('Подписка')
                    ->fromModel(Subscription::class, 'name')
                    ->required(),

                Relation::make('purchase.sub_dur_id')
                    ->title('Тариф')
                    ->fromModel(SubscriptionDuration::class, 'days')
                    ->displayAppend('full')
                    ->required(),

                DateTimer::make('purchase.valid_thru')
                    ->title('Действует до')
                    ->format('Y-m-d H:i:s')
                    ->required(),
            ]))->title('Редактирование покупки')
                ->deferred('loadPurchase'),
        ];
    }

    public function loadPurchase(SubToUser $purchase): iterable
    {
        return [
            'purchase' => $purchase,
        ];
    }

    public function save(Request $request, ?SubToUser $purchase = null): void
    {
        $validated = $request->validate([
            'purchase.user_id' => 'required|exists:users,id',
            'purchase.sub_id' => 'required|exists:subscriptions,id',
            'purchase.sub_dur_id' => 'required|exists:subscription_durations,id',
            'purchase.valid_thru' => 'required|date',
        ]);

        $purchase = $purchase ?? new SubToUser();
        $purchase->fill($validated['purchase'])->save();

        Toast::info($purchase->wasRecentlyCreated ? 'Покупка создана' : 'Покупка обновлена');
    }

    public function remove(Request $request): void
    {
        SubToUser::findOrFail($request->get('id'))->delete();

        Toast::info('Покупка удалена');
    }
}
