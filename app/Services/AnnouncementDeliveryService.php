<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class AnnouncementDeliveryService
{
    public function deliver(Announcement $announcement): array
    {
        $vapid = config('webpush.vapid');

        if (blank($vapid['public_key']) || blank($vapid['private_key'])) {
            Log::warning('Annonce publiée sans envoi push : les clés VAPID ne sont pas configurées.', [
                'announcement_id' => $announcement->id,
            ]);

            return ['configured' => false, 'sent' => 0, 'failed' => 0];
        }

        $push = new WebPush([
            'VAPID' => [
                'subject' => $vapid['subject'],
                'publicKey' => $vapid['public_key'],
                'privateKey' => $vapid['private_key'],
            ],
        ], [
            'TTL' => 604800,
            'urgency' => 'normal',
            'topic' => 'announcement-'.$announcement->id,
        ]);

        $payload = json_encode([
            'title' => $announcement->title,
            'body' => Str::limit(strip_tags($announcement->content), 180),
            'url' => '/client/dashboard',
            'tag' => 'mitsinjo-announcement-'.$announcement->id,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $sent = 0;
        $failed = 0;

        foreach ($this->targetUsers($announcement)->with('pushSubscriptions')->get() as $user) {
            foreach ($user->pushSubscriptions as $subscription) {
                try {
                    $report = $push->sendOneNotification(
                        Subscription::create([
                            'endpoint' => $subscription->endpoint,
                            'keys' => [
                                'p256dh' => $subscription->public_key,
                                'auth' => $subscription->auth_token,
                            ],
                            'contentEncoding' => $subscription->content_encoding,
                        ]),
                        $payload,
                    );

                    if ($report->isSuccess()) {
                        $sent++;
                    } else {
                        $failed++;

                        if ($report->isSubscriptionExpired()) {
                            $subscription->delete();
                        } else {
                            Log::warning('Échec de livraison Web Push.', [
                                'announcement_id' => $announcement->id,
                                'subscription_id' => $subscription->id,
                                'reason' => $report->getReason(),
                            ]);
                        }
                    }
                } catch (Throwable $exception) {
                    $failed++;
                    Log::warning('Erreur pendant la livraison Web Push.', [
                        'announcement_id' => $announcement->id,
                        'subscription_id' => $subscription->id,
                        'exception' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return ['configured' => true, 'sent' => $sent, 'failed' => $failed];
    }

    private function targetUsers(Announcement $announcement)
    {
        $users = User::query()
            ->where('role', 'client')
            ->where('is_active', true);

        if ($announcement->zone_id) {
            $zoneName = $announcement->zone?->name;
            $zoneNames = collect([$zoneName])
                ->filter()
                ->flatMap(function (string $name) {
                    $normalized = mb_strtolower(trim($name));
                    $withoutPrefix = preg_replace('/^fokontany\s+/u', '', $normalized);

                    return [$normalized, $withoutPrefix];
                })
                ->unique()
                ->values()
                ->all();

            $users->where(function ($query) use ($announcement, $zoneNames) {
                $query->where('zone_id', $announcement->zone_id);

                if ($zoneNames !== []) {
                    $query->orWhereIn(DB::raw('LOWER(region)'), $zoneNames)
                        ->orWhereIn(DB::raw('LOWER(fokontany)'), $zoneNames);
                }
            });
        }

        return $users;
    }
}