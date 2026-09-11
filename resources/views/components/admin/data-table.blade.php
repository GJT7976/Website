{{-- Wraps a <table> with consistent admin styling. Usage:
     <x-admin.data-table>
         <table>...</table>
     </x-admin.data-table> --}}
<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-xl border border-border bg-white']) }}>
    <div class="[&_table]:w-full [&_th]:text-label [&_th]:px-4 [&_th]:py-3 [&_th]:text-left [&_thead]:border-b [&_thead]:border-border [&_thead]:bg-mist/60 [&_td]:px-4 [&_td]:py-3 [&_tbody_tr]:border-b [&_tbody_tr]:border-border [&_tbody_tr:last-child]:border-0 [&_tbody_tr:hover]:bg-mist/40">
        {{ $slot }}
    </div>
</div>
