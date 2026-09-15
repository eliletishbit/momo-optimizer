@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
        @if($notifications->count() > 0)
            <button class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Tout marquer comme lu</button>
        @endif
    </div>

    <div class="space-y-4">
        @forelse($notifications as $notification)
            <x-card class="{{ $notification->read_at ? 'opacity-60' : 'border-indigo-100 bg-indigo-50/10' }}">
                <div class="flex justify-between items-start">
                    <div class="flex items-start">
                        <div class="p-2 {{ $notification->read_at ? 'bg-gray-100 text-gray-400' : 'bg-indigo-100 text-indigo-600' }} rounded-lg mr-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                            <span class="text-[10px] text-gray-400 mt-2 block">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @if(!$notification->read_at)
                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs text-indigo-600 hover:underline">Marquer comme lu</button>
                        </form>
                    @endif
                </div>
            </x-card>
        @empty
            <div class="text-center py-24">
                <div class="w-16 h-16 bg-gray-100 text-gray-300 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <p class="text-gray-500">Vous n'avez aucune notification.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
