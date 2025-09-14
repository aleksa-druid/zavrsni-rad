<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use App\Models\Message;
use App\Models\ChatRoom;

class ChatPanel extends Component
{
    public ?string $roomId = null;
    public array $messages = [];
    public array $participants = [];

    #[Validate('required|string|min:1|max:2000')]
    public string $body = '';

    public function getListeners(): array
    {
        return [];
    }

    #[On('open-room-with-contact')]
    public function openRoomWithContact(string $contactId): void
    {
        // Create or open the room
        $room = ChatRoom::firstOrCreateBetween((string) auth()->id(), (string) $contactId);
        $this->roomId = (string) $room->getKey();
        $this->participants = array_map('strval', (array) ($room->participant_ids ?? []));

        // Load last 100 messages (oldest -> newest)
        $this->messages = Message::where('room_id', $this->roomId)
            ->orderBy('_id', 'desc')->limit(100)->get()
            ->reverse()->values()->map(fn ($m) => [
                'id'         => (string) $m->_id,
                'user_id'    => (string) $m->user_id,
                'body'       => $m->body,
                'created_at' => optional($m->created_at)->toDateTimeString(),
            ])->toArray();

        $this->dispatch('bind-echo-chat', roomId: $this->roomId, componentId: $this->getId());

        $this->dispatch('scroll-to-bottom');
    }

    /** Called from the browser when a WS event arrives */
    public function receive(array $payload): void
    {
        if ($this->roomId !== ($payload['room_id'] ?? null)) return;

        // ignore my own events (in case a header ever goes missing)
        if ((string)auth()->id() === ($payload['user_id'] ?? '')) return;

        $this->messages[] = [
            'id'         => $payload['id'],
            'user_id'    => $payload['user_id'],
            'body'       => $payload['body'],
            'created_at' => $payload['created_at'],
        ];

        $this->dispatch('scroll-to-bottom');
    }

    public function send(): void
    {
        $this->body = trim($this->body);
        $this->validate();
        if (!$this->roomId) return;

        $msg = \App\Models\Message::create([
            'room_id' => $this->roomId,
            'user_id' => (string) auth()->id(),
            'body'    => $this->body,
        ]);

        \App\Models\ChatRoom::where('_id', $this->roomId)->update(['last_message_at' => now()]);

        // Broadcast to other participants only
        broadcast(new \App\Events\MessageSent($msg))->toOthers();

        $this->messages[] = [
            'id'         => (string) $msg->_id,
            'user_id'    => (string) $msg->user_id,
            'body'       => $msg->body,
            'created_at' => optional($msg->created_at)->toDateTimeString(),
        ];

        // Clear input reliably
        $this->reset('body');
        $this->dispatch('clear-message-input');
        $this->dispatch('scroll-to-bottom');
    }

    public function render()
    {
        return view('livewire.chat-panel');
    }
}
