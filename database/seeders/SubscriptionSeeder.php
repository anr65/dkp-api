<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\SubscriptionDuration;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $subscription = Subscription::create([
            'name' => ' Тестовые подписки',
            'status' => 'active',
        ]);

        SubscriptionDuration::insert([
            [
                'sub_id' => $subscription->id,
                'days' => 30,
                'price' => 299,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sub_id' => $subscription->id,
                'days' => 90,
                'price' => 799,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sub_id' => $subscription->id,
                'days' => 365,
                'price' => 2990,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
