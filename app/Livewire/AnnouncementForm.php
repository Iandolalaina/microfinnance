<?php

namespace App\Livewire;

use App\Models\Announcement;
use App\Models\Zone;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layout')]
class AnnouncementForm extends Component
{
    public string $title = '';

    public string $content = '';

    public ?int $zoneId = null;

    public bool $sendSms = false;

    public bool $published = false;

    public function publish(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string'],
        ], [
            'title.required' => "Le titre est obligatoire.",
            'content.required' => "Le contenu est obligatoire.",
        ]);

        Announcement::create([
            'title' => $this->title,
            'content' => $this->content,
            'zone_id' => $this->zoneId,
            'created_by' => auth()->id(),
            'send_sms' => $this->sendSms,
            'published_at' => now(),
        ]);

        $this->reset(['title', 'content', 'zoneId', 'sendSms']);
        $this->published = true;
    }

    public function render()
    {
        return view('livewire.announcement-form', [
            'zones' => Zone::orderBy('name')->get(),
        ]);
    }
}
