<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Названия тарифов по пакетам заказчика: за 600 — COMMUNITY (раньше Open), за 5000 — ACTIVE (раньше Community),
     * Private не меняется. Порядок важен: сначала прежний community становится active, и только потом open — community,
     * иначе обе группы слились бы в одну.
     *
     * Затрагивает тариф участницы (bot_users), историю подписок и оплат, заявки на тариф и две настройки админки:
     * цены подписки и список закрытых для покупки тарифов. Рассылки (audience = open) не трогаем: это «без тарифа».
     */
    private const TABLES = ['bot_users', 'subscriptions', 'payments', 'plan_requests'];

    public function up(): void
    {
        $this->rename(['community' => 'active', 'open' => 'community']);
    }

    public function down(): void
    {
        $this->rename(['community' => 'open', 'active' => 'community']);
    }

    /**
     * @param  array<string, string>  $map  старое значение => новое, в порядке выполнения
     */
    private function rename(array $map): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'plan')) {
                continue;
            }

            foreach ($map as $from => $to) {
                DB::table($table)->where('plan', $from)->update(['plan' => $to]);
            }
        }

        $this->renameSettings($map);
    }

    /** @param array<string, string> $map */
    private function renameSettings(array $map): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        // Ключи считаются «по старому → по новому» за один проход, поэтому community и open не перепутываются
        $remap = fn (string $plan): string => $map[$plan] ?? $plan;

        $prices = DB::table('site_settings')->where('key', 'subscription_prices')->first();

        if ($prices !== null) {
            $stored = json_decode((string) $prices->value, true);
            $renamed = [];

            foreach (is_array($stored) ? $stored : [] as $plan => $price) {
                $renamed[$remap((string) $plan)] = $price;
            }

            DB::table('site_settings')->where('key', 'subscription_prices')->update(['value' => json_encode($renamed)]);
        }

        $closed = DB::table('site_settings')->where('key', 'subscription_closed')->first();

        if ($closed !== null) {
            $stored = json_decode((string) $closed->value, true);
            $renamed = array_values(array_unique(array_map(fn ($plan): string => $remap((string) $plan), is_array($stored) ? $stored : [])));

            DB::table('site_settings')->where('key', 'subscription_closed')->update(['value' => json_encode($renamed)]);
        }

        Cache::forget('subscription_prices');
        Cache::forget('subscription_closed');
    }
};
