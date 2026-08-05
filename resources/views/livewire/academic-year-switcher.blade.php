<div style="display: inline-flex; align-items: center; gap: 8px; flex-wrap: nowrap; white-space: nowrap;" onclick="event.stopPropagation();">
    @if($canSwitch)
        <label for="academic-year-switcher" style="font-size: 13.5px; font-weight: 700; white-space: nowrap;" class="text-gray-700 dark:text-gray-200">
            العام:
        </label>
        <select 
            id="academic-year-switcher"
            wire:model.live="selectedDatabase" 
            onclick="event.stopPropagation();"
            style="font-size: 13px; font-weight: 600; padding: 6px 28px 6px 12px; min-width: 140px;"
            class="bg-white border border-gray-300 rounded-lg shadow-2xs hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 cursor-pointer transition"
        >
            @foreach($databases as $db => $label)
                <option value="{{ $db }}">{{ $label }}</option>
            @endforeach
        </select>
    @else
        <span style="font-size: 13px; font-weight: 600; padding: 5px 12px; border-radius: 8px;" class="text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 whitespace-nowrap">
            العام: {{ $currentLabel }}
        </span>
    @endif
</div>
