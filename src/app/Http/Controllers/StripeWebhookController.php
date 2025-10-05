<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Address;
use Stripe\Stripe;
use Stripe\Webhook;

/**
 * Webhook通知受け取り用コントローラー
 */
class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {

        Stripe::setApiKey(env('STRIPE_SECRET'));
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        // イベントタイプごとに処理
        switch ($event->type) {

            // Checkout 完了時 → 仮注文を作成
            case 'checkout.session.completed':
                $session = $event->data->object;

                $itemId        = $session->metadata->item_id;
                $userId        = $session->metadata->user_id;
                $shippingAddress     = $session->metadata->shipping_address;
                $shippingAddress = [
                    'user_id'  => $userId,
                    'post_code'=> $session->metadata->post_code,
                    'address'  => $session->metadata->address,
                    'building' => $session->metadata->building,
                ];
                $paymentMethod = $session->metadata->payment_method;

                // 送付先住所作成
                $address = Address::firstOrCreate($shippingAddress);

                Purchase::updateOrCreate(
                    [
                        'item_id' => $itemId,
                        'user_id' => $userId,
                    ],
                    [
                        'address_id'     => $address->id,
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
                $purchaseId = $paymentIntent->metadata->purchase_id ?? null;

                $shippingAddress = [
                    'user_id'   => $userId,
                    'post_code' => $paymentIntent->metadata->post_code ?? null,
                    'address'   => $paymentIntent->metadata->address ?? null,
                    'building'  => $paymentIntent->metadata->building ?? null,
                ];

                $address = Address::firstOrCreate($shippingAddress);

                if ($itemId && $userId && $purchaseId) {
                    Purchase::where('item_id', $itemId)
                        ->where('user_id', $userId)
                        ->where('id', $purchaseId)
                        ->update([
                            'address_id' => $address->id,
                            'payment_status' => 1
                        ]);
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
