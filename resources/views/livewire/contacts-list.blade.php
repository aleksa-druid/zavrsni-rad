<div class="h-full flex flex-col">
    <div class="p-2 border-b">
        <input type="text"
               wire:model.live.debounce.300ms="q"
               placeholder="Search contacts…"
               class="w-full border rounded px-3 py-2 text-sm">
    </div>

    <div class="flex-1 overflow-y-auto">
        @forelse ($contacts as $c)
            <button class="w-full text-left px-3 py-2 hover:bg-gray-100 border-b"
                    wire:click="openWith('{{ $c['id'] }}')">
                <div class="font-medium text-sm">{{ $c['name'] }}</div>
                <div class="text-xs text-gray-500">{{ $c['email'] }}</div>
            </button>
        @empty
            <div class="p-3 text-sm text-gray-500">No contacts.</div>
        @endforelse
    </div>
</div>
