@props(['name'])

<svg {{ $attributes->class(['shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('dashboard')<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/>@break
        @case('clock')<circle cx="12" cy="12" r="8.5"/><path d="M12 7v5l3 2"/>@break
        @case('users')<path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20"/><circle cx="10" cy="7.5" r="3.5"/><path d="M16 4.4a3.5 3.5 0 0 1 0 6.7M17 15h.5a3.5 3.5 0 0 1 3.5 3.5V20"/>@break
        @case('office')<path d="M3.5 20.5h17M5.5 20V5.5l6.5-2 6.5 2V20M9 8h.01M15 8h.01M9 12h.01M15 12h.01M10 20v-4h4v4"/>@break
        @case('pin')<path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.2"/>@break
        @case('shield')<path d="M12 21s7-3.5 7-9V5l-7-2-7 2v7c0 5.5 7 9 7 9Z"/><path d="m9 12 2 2 4-4"/>@break
        @case('arrow-right')<path d="M4.5 12h15M13 5.5l6.5 6.5-6.5 6.5"/>@break
        @case('search')<circle cx="10.8" cy="10.8" r="6.3"/><path d="m16 16 4.5 4.5"/>@break
        @case('trash')<path d="M4 7h16M10 11v5M14 11v5M6 7l1 13h10l1-13M9 7V4h6v3"/>@break
        @case('edit')<path d="m14.5 5.5 4 4M4 20l4.2-.9L19 8.3a2.8 2.8 0 0 0-4-4L4.9 15.8 4 20Z"/>@break
        @case('calendar')<rect x="3.5" y="5" width="17" height="15.5" rx="2"/><path d="M7.5 3v4M16.5 3v4M3.5 9.5h17"/>@break
        @case('file')<path d="M6 3.5h8l4 4V20a.5.5 0 0 1-.5.5h-11A.5.5 0 0 1 6 20z"/><path d="M14 3.5V8h4M9 12h6M9 16h6"/>@break
        @case('check')<path d="m5 12 4.5 4.5L19 7"/>@break
        @case('briefcase')<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V4h8v3M3 12h18M10 12v2h4v-2"/>@break
        @default<circle cx="12" cy="12" r="8"/>
    @endswitch
</svg>
