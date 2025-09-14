<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(public Message $message) {}

   public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.'.$this->message->room_id)];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => (string) $this->message->_id,
            'room_id'    => (string) $this->message->room_id,
            'user_id'    => (string) $this->message->user_id,
            'body'       => (string) $this->message->body,
            'created_at' => optional($this->message->created_at)->toDateTimeString(),
        ];
    }
}
