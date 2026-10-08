@props(['user'])

<div {{ $attributes->class('flex shrink-0 items-center justify-center overflow-hidden rounded-xl bg-brand-100 font-semibold text-brand-700') }}>
    @if ($user->profile_photo)
        <img
            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_photo) }}"
            alt="Photo de profil de {{ $user->name }}"
            class="h-full w-full object-cover"
        >
    @else
        <span aria-label="Photo de profil absente">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
    @endif
</div>
