<?php

namespace App\Console\Commands;

use App\Mail\AccountDeletionReminderMail;
use App\Mail\AccountDeletionFinalMail;
use App\Models\DeletedAccountArchive;
use App\Models\ShopVisit;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PurgeDeletedAccounts extends Command
{
    protected $signature = 'users:purge-deleted';
    protected $description = 'Envoie les rappels et supprime définitivement les comptes arrivés à échéance';

    public function handle(): int
    {
        $this->info('Envoi des rappels et purge des comptes...');
        $this->sendReminders();
        $this->purgeExpired();
        return self::SUCCESS;
    }

    private function sendReminders(): void
    {
        $candidates = User::whereNotNull('deletion_requested_at')->get();

        foreach ($candidates as $user) {
            $daysLeft = $user->daysUntilDeletion();

            if (in_array($daysLeft, [15, 5, 1])) {
                try {
                    Mail::to($user->email)->send(new AccountDeletionReminderMail($user, $daysLeft));
                    $this->line("  ✉️  Rappel J-{$daysLeft} envoyé à {$user->email}");
                } catch (\Throwable $e) {
                    Log::error('Reminder email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                }
            }
        }
    }

    private function purgeExpired(): void
    {
        $expired = User::whereNotNull('deletion_requested_at')
            ->where('deletion_requested_at', '<=', now()->subDays(30))
            ->get();

        foreach ($expired as $user) {
            $this->purgeUser($user);
        }
    }

    private function purgeUser(User $user): void
    {
        $this->info("Purging user {$user->email}");

        try {
            DB::transaction(function () use ($user) {
                $shop = $user->shop;

                if ($shop) {
                    $shopViews     = ShopVisit::where('shop_id', $shop->id)->where('event', 'view_shop')->count();
                    $shopVisitors  = ShopVisit::where('shop_id', $shop->id)->where('event', 'view_shop')
                        ->distinct('ip_hash')->count('ip_hash');
                    $waClicks      = ShopVisit::where('shop_id', $shop->id)->where('event', 'whatsapp_click')->count();
                    $modelsCount   = $shop->models()->withTrashed()->count();
                    $publishedCount = $shop->models()->withTrashed()->where('status', 'published')->count();

                    DeletedAccountArchive::create([
                        'email_hash'             => hash('sha256', $user->email . config('app.key')),
                        'shop_name'              => $shop->name,
                        'shop_slug'              => $shop->slug,
                        'shop_views_total'       => $shopViews,
                        'shop_visitors_total'    => $shopVisitors,
                        'shop_whatsapp_clicks'   => $waClicks,
                        'models_count'           => $modelsCount,
                        'published_models_count' => $publishedCount,
                        'deletion_reason'        => $user->deletion_reason,
                        'deletion_reason_detail' => $user->deletion_reason_detail,
                        'registered_at'          => $user->created_at,
                        'deletion_requested_at'  => $user->deletion_requested_at,
                        'deleted_at'             => now(),
                    ]);

                    foreach ($shop->models()->withTrashed()->get() as $model) {
                        foreach ($model->images as $image) {
                            Storage::disk('public')->delete($image->path);
                        }
                    }

                    if ($shop->logo_path)  Storage::disk('public')->delete($shop->logo_path);
                    if ($shop->cover_path) Storage::disk('public')->delete($shop->cover_path);

                    ShopVisit::where('shop_id', $shop->id)->delete();

                    $shop->models()->withTrashed()->forceDelete();
                    $shop->forceDelete();
                }

                $email     = $user->email;
                $firstName = $user->first_name;

                $user->tokens()->delete();
                $user->forceDelete();

                try {
                    Mail::to($email)->send(new AccountDeletionFinalMail($email, $firstName));
                } catch (\Throwable $e) {
                    Log::error('Final email failed', ['email' => $email, 'error' => $e->getMessage()]);
                }
            });

            $this->line("  ✅ Compte purgé");
        } catch (\Throwable $e) {
            $this->error("  ❌ Erreur : {$e->getMessage()}");
            Log::error('Purge failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }
}
