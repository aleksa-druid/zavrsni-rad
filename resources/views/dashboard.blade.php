<x-layouts.app>
    <form method="POST" action="{{ route('logout') }}">
        @csrf

        <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ __('Log Out') }}
        </button>
    </form>
    <div class="h-screen flex">
        <div class="w-1/4 border-r">
            <livewire:contacts-list />
        </div>
        <div class="w-3/4">
            <livewire:chat-panel />
        </div>
    </div>
</x-layouts.app>
