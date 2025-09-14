<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\ChatRoom;

class ContactsList extends Component
{
    public string $q = '';
    public array $contacts = [];

    public function mount(): void
    {
        $this->refreshContacts();
    }

    public function updatedQ(): void
    {
        $this->refreshContacts();
    }

    protected function refreshContacts(): void
    {
        $authId = (string) auth()->id();

        // Simple name/email search (Mongo-backed User)
        $query = User::query()
            ->where('_id', '!=', $authId);

        if (trim($this->q) !== '') {
            $q = trim($this->q);
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $this->contacts = $query
            ->orderBy('name')
            ->limit(50)
            ->get(['_id', 'name', 'email'])
            ->map(fn($u) => [
                'id' => (string) $u->_id,
                'name' => $u->name,
                'email' => $u->email,
            ])->toArray();
    }

    public function openWith(string $contactId): void
    {
        $this->dispatch('open-room-with-contact', contactId: (string) $contactId)->to(\App\Livewire\ChatPanel::class);
    }

    public function render()
    {
        return view('livewire.contacts-list');
    }
}
