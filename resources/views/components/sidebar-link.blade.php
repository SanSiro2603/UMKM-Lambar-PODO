@props([
    'href' => '#',
    'icon' => '',
    'label' => '',
    'active' => false,
    'badge' => null,
])

<a href="{{ $href }}" wire:navigate
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
          {{ $active
              ? 'bg-primary-50 text-primary-600'
              : 'text-surface-600 hover:bg-surface-50 hover:text-surface-800' }}">
    @if($icon)
        <span class="shrink-0">{!! $icon !!}</span>
    @endif
    <span class="flex-1">{{ $label }}</span>
    @if($badge !== null)
        <span class="px-2 py-0.5 text-xs font-bold rounded-full
                     {{ $active ? 'bg-primary-500 text-white' : 'bg-surface-200 text-surface-600' }}">
            {{ $badge }}
        </span>
    @endif
</a>
