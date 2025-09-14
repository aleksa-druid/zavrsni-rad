<div class="h-full flex flex-col">
    <div class="p-3 border-b text-sm">
        @if ($roomId)
            <span class="font-semibold">Room:</span> {{ $roomId }}
        @else
            <span class="text-gray-500">Select a contact to start chatting</span>
        @endif
    </div>

    <div id="messagesBox" class="flex-1 overflow-y-auto p-3 space-y-2">
        @foreach ($messages as $m)
            <div wire:key="msg-{{ $m['id'] }}"
                 class="max-w-[80%] {{ $m['user_id'] === (string) auth()->id() ? 'ml-auto text-right' : '' }}">
                <div class="inline-block border rounded-lg px-3 py-2 text-sm {{ $m['user_id'] === (string) auth()->id() ? 'bg-indigo-50 border-indigo-200' : 'bg-white' }}">
                    <div class="whitespace-pre-wrap">{{ $m['body'] }}</div>
                    <div class="text-[10px] text-gray-500 mt-1">{{ $m['created_at'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="border-t p-2 flex gap-2">
        <input
            id="messageInput"  {{-- <<< add this --}}
            type="text"
            wire:model.live.debounce.150ms="body"
            class="flex-1 border rounded px-3 py-2 text-sm"
            placeholder="{{ $roomId ? 'Type a message…' : 'Pick a contact first' }}"
            @if (!$roomId) disabled @endif
            wire:keydown.enter.prevent="send"
            autocomplete="off">
        <button
            type="button"
            class="px-4 py-2 rounded bg-indigo-600 text-white text-sm"
            wire:click="send"
            @if (!$roomId) disabled @endif>
            Send
        </button>
    </div>

    @error('body')
        <p class="text-red-600 text-xs px-2 mt-1">{{ $message }}</p>
    @enderror
</div>
