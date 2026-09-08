<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends messages to a single Telegram chat via the Bot API. A missing
 * token/chat id or a failed request never throws — notifications are a
 * side channel and must not interrupt a sale or stock update.
 */
class Telegram
{
    public static function send(string $message): void
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (! $token || ! $chatId) {
            return;
        }

        try {
            $response = Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if ($response->failed()) {
                Log::warning('Telegram notification rejected: '.$response->body());
            }
        } catch (Throwable $e) {
            Log::warning('Telegram notification failed: '.$e->getMessage());
        }
    }

    public static function saleCompleted(Sale $sale): void
    {
        $currency = Setting::get('currency_symbol', '$');
        $itemCount = (int) $sale->items()->sum('quantity');
        $payment = ucfirst(str_replace('_', ' ', $sale->payment_method));

        self::send(
            "🛒 <b>New Sale #{$sale->id}</b>\n".
            'Cashier: '.e($sale->user?->name ?? 'Unknown')."\n".
            "Items: {$itemCount}\n".
            'Total: '.e($currency).number_format((float) $sale->total, 2)."\n".
            "Payment: {$payment}"
        );
    }

    public static function lowStock(Product $product): void
    {
        self::send(
            "⚠️ <b>Low Stock Alert</b>\n".
            e($product->name)." (SKU {$product->sku}) is now at {$product->stock_qty} units."
        );
    }
}
