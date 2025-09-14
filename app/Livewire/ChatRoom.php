<?php // app/Livewire/ChatRoom.php
namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Models\Message;
use App\Events\MessageSent;

#[Layout('layouts.app')]
class ChatRoom extends Component
{
    public string $roomId;
    public array $messages = [];

    #[Validate('required|string|min:1|max:2000')]
    public string $body = '';

    public function mount(string $roomId): void
    {
        $this->roomId = $roomId;
        $this->messages = Message::where('room_id', $roomId)
            ->orderBy('_id', 'desc')->limit(50)->get()->reverse()->values()->toArray();
            dd($this->messages);
    }

    // Livewire 3: dynamic Echo listener
    public function getListeners(): array
    {
        return [
            "echo:chat.{$this->roomId},message.sent" => 'pushIncoming',
        ];
    }

    public function pushIncoming(array $payload): void
    {
        $this->messages[] = $payload;
        $this->dispatch('scrolled-to-bottom'); // optional UI hook
    }

    public function send(): void
    {
        dd('hello');
        $this->validate();

        $msg = Message::create([
            'room_id' => $this->roomId,
            'user_id' => auth()->id() ? (string) auth()->id() : 'guest',
            'body'    => $this->body,
        ]);

        MessageSent::dispatch($msg); // queues & broadcasts to chat.{roomId}

        $this->body = '';
        // Optimistic UI (append immediately); Reverb will also push to others:
        $this->messages[] = [
            'id'      => (string) $msg->_id,
            'room_id' => $msg->room_id,
            'user_id' => $msg->user_id,
            'body'    => $msg->body,
            'created_at' => now()->toISOString(),
        ];
        $this->dispatch('scrolled-to-bottom');
    }

    public function render()
    {
        return view('livewire.chat-room');
    }
}
