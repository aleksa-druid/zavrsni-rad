<div class="mx-auto max-w-2xl p-4">
    <div class="border rounded-xl p-3 h-[65vh] overflow-y-auto" id="messages">
        @foreach($messages as $m)
            <div class="py-1">
                <span class="text-xs text-gray-500">{{ $m['user_id'] }}</span>
                <div class="text-sm">{{ $m['body'] }}</div>
            </div>
        @endforeach
    </div>

   <form wire:submit.prevent="send" class="border-t p-2 flex gap-2">
        <input type="text"
                wire:model.defer="body"
                class="flex-1 border rounded px-3 py-2 text-sm"
                placeholder="{{ $roomId ? 'Type a message…' : 'Pick a contact first' }}"
                @disabled(!$roomId) />
        <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white text-sm" @disabled(!$roomId)>
            Send
        </button>
    </form>


    <script data-navigate-once>
        // keep the list scrolled to the bottom when new messages come in
        window.addEventListener('scrolled-to-bottom', () => {
            const box = document.getElementById('messages');
            box.scrollTop = box.scrollHeight;
        });
    </script>
</div>
