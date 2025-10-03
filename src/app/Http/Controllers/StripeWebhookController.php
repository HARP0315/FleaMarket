<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Purchase;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Stripeシークレットキー設定
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Webhook秘密鍵（Stripeの設定画面で取得するやつ）
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // ペイロードが不正
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // 署名が不正
            return response('Invalid signature', 400);
        }

        // イベントタイプごとに処理
        switch ($event->type) {

            // Checkout 完了時 → 仮注文を作成
            case 'checkout.session.completed':
                $session = $event->data->object;

                $itemId        = $session->metadata->item_id;
                $userId        = $session->metadata->user_id;
                $addressId     = $session->metadata->address_id;
                $paymentMethod = $session->metadata->payment_method;

                // item_id + user_id で一意に作成（重複防止）
                Purchase::firstOrCreate(
                    [
                        'item_id' => $itemId,
                        'user_id' => $userId,
                    ],
                    [
                        'address_id'     => $addressId,
                        'price'          => $session->amount_total,
                        'payment_method' => $paymentMethod,
                        'payment_status' => 0,
                        'is_deleted'     => 0,
                    ]
                );
                break;

            // PaymentIntent 成功 → 仮注文を payment_status = 1 に更新
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $itemId = $paymentIntent->metadata->item_id ?? null;
                $userId = $paymentIntent->metadata->user_id ?? null;

                if ($itemId && $userId) {
                    Purchase::where('item_id', $itemId)
                        ->where('user_id', $userId)
                        ->update(['payment_status' => 1]);
                }
                break;

            // PaymentIntent キャンセル / 失敗 → 仮注文を論理削除
            case 'payment_intent.canceled':
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $itemId = $paymentIntent->metadata->item_id ?? null;
                $userId = $paymentIntent->metadata->user_id ?? null;

                if ($itemId && $userId) {
                    Purchase::where('item_id', $itemId)
                        ->where('user_id', $userId)
                        ->update(['is_deleted' => 1]);
                }
                break;
        }

        return response('Webhook handled', 200);
    }
}
