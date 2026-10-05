<?php

namespace App\Livewire;

use App\Models\Announcement;
use App\Models\User;
use App\Models\Zone;
use App\Services\AnnouncementDeliveryService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layout')]
class AnnouncementForm extends Component
{
    public string $title = '';

    public string $content = '';

    public ?int $zoneId = null;

    public bool $published = false;

    public string $deliveryMessage = '';

    public ?int $deletedAnnouncementId = null;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->role === 'agent') {
            $this->zoneId = $user->zone_id;
        }
    }

    public function publish(AnnouncementDeliveryService $delivery): void
    {
        if (auth()->user()->role === 'agent') {
            $this->zoneId = auth()->user()->zone_id;
        }

        $this->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string'],
        ], [
            'title.required' => "Le titre est obligatoire.",
            'content.required' => "Le contenu est obligatoire.",
        ]);

        $announcement = Announcement::create([
            'title' => $this->title,
            'content' => $this->content,
            'zone_id' => $this->zoneId,
            'created_by' => auth()->id(),
            'published_at' => now(),
        ]);

        $result = $delivery->deliver($announcement);
        $this->deliveryMessage = match (true) {
            ! $result['configured'] => "Annonce publiée dans l'espace membre. Les notifications push ne sont pas configurées sur le serveur.",
            $result['sent'] > 0 && $result['failed'] > 0 => "Annonce publiée ; {$result['sent']} appareil(s) notifié(s), {$result['failed']} envoi(s) en échec. Consultez les journaux.",
            $result['sent'] > 0 => "Annonce publiée et envoyée à {$result['sent']} appareil(s) abonné(s).",
            $result['failed'] > 0 => "Annonce publiée dans l'espace membre, mais {$result['failed']} notification(s) push ont échoué. Consultez les journaux.",
            default => "Annonce publiée dans l'espace membre. Aucun appareil n'a encore activé les notifications.",
        };

        $this->reset(['title', 'content']);

        if (auth()->user()->role === 'admin') {
            $this->zoneId = null;
        }

        $this->published = true;
    }

    public function delete(int $announcementId): void
    {
        $announcement = $this->announcementQuery()
            ->whereKey($announcementId)
            ->firstOrFail();

        $announcement->delete();
        $this->deletedAnnouncementId = $announcementId;
    }

    public function render()
    {
        $announcements = $this->announcementQuery()
            ->with(['author', 'zone'])
            ->latest('published_at')
            ->latest('id')
            ->get()
            ->map(function (Announcement $announcement) {
                $targetUsersQuery = $this->targetUsersQuery($announcement);

                $announcement->target_clients_count = (clone $targetUsersQuery)->count();
                $announcement->target_clients_preview = (clone $targetUsersQuery)
                    ->orderBy('name')
                    ->limit(3)
                    ->pluck('name')
                    ->all();

                return $announcement;
            });

        return view('livewire.announcement-form', [
            'zones' => $this->zonesQuery()->get(),
            'announcements' => $announcements,
        ]);
    }

    protected function announcementQuery(): Builder
    {
        $user = auth()->user();

        return Announcement::query()
            ->when($user->role === 'agent', function (Builder $query) use ($user) {
                $query->where('created_by', $user->id);
            });
    }

    protected function zonesQuery(): Builder
    {
        $user = auth()->user();

        return Zone::query()
            ->when($user->role === 'agent', fn (Builder $query) => $query->whereKey($user->zone_id))
            ->orderBy('name');
    }

    protected function targetUsersQuery(Announcement $announcement): Builder
    {
        return User::query()
            ->where('role', 'client')
            ->where('is_active', true)
            ->when($announcement->zone_id, fn (Builder $query) => $query->where('zone_id', $announcement->zone_id));
    }
}
