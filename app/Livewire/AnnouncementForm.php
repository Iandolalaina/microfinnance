<?php

namespace App\Livewire;

use App\Models\Announcement;
use App\Models\Zone;
use App\Services\AnnouncementDeliveryService;
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

    public function publish(AnnouncementDeliveryService $delivery): void
    {
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

        $this->reset(['title', 'content', 'zoneId']);
        $this->published = true;
    }

    public function render()
    {
        return view('livewire.announcement-form', [
            'zones' => Zone::orderBy('name')->get(),
        ]);
    }
}
