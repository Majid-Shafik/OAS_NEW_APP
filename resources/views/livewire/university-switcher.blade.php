<div class="flex items-center gap-2">
    @if(auth()->user()->UNID == 0)
        <label for="university-switcher" class="text-sm font-medium text-gray-700 dark:text-red-200">الجامعة:</label>
        <select 
            id="university-switcher"
            wire:model.live="selectedUnid" 
            onclick="event.stopPropagation();"
            class="block w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200"
        >
            @foreach($universities as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    @else
        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ auth()->user()->university?->U_NAME ?? 'جامعة غير معروفة' }}
        </span>
    @endif
</div>
