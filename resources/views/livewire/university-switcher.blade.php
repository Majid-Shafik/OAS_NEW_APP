<div style="display: inline-flex; align-items: center; gap: 8px; flex-wrap: nowrap; white-space: nowrap;" onclick="event.stopPropagation();">
    @if(auth()->user()?->UNID == 0)
        @php
            $uniList = [];
            foreach($universities as $id => $name) {
                $uniList[] = [
                    'id' => (int)$id, 
                    'name' => (string)$name,
                ];
            }
            $currentName = $universities[$selectedUnid] ?? 'الكل (جميع الجامعات)';
        @endphp

        <span style="font-size: 14px; font-weight: 400; white-space: nowrap;" class="text-gray-700 dark:text-gray-200">الجامعة:</span>

        <div 
            x-data="{
                search: '',
                selectedId: {{ (int)$selectedUnid }},
                selectedName: '{{ addslashes($currentName) }}',
                universities: {{ \Illuminate\Support\Js::from($uniList) }},
                get filteredUniversities() {
                    if (!this.search || !this.search.trim()) return this.universities;
                    const q = this.search.toLowerCase().trim();
                    return this.universities.filter(u => 
                        u.name.toLowerCase().includes(q) || 
                        (u.id > 0 && u.id.toString().includes(q))
                    );
                },
                select(item) {
                    this.selectedId = item.id;
                    this.selectedName = item.name;
                    $wire.selectUniversity(item.id);
                }
            }"
            style="display: inline-block;"
        >
            <x-filament::dropdown 
                placement="bottom-start" 
                :teleport="true"
                width="lg"
            >
                <x-slot name="trigger">
                    <button 
                        type="button"
                        x-on:click="setTimeout(() => $refs.searchInput?.focus(), 60)"
                        class="bg-white border border-gray-300 rounded-lg shadow-2xs hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-750 cursor-pointer transition"
                        style="width: 290px; max-width: 330px; padding: 6px 12px; display: inline-flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 13.5px; font-weight: 600;"
                    >
                        <div style="display: inline-flex; align-items: center; gap: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; text-align: start;">
                            <span 
                                x-show="selectedId > 0" 
                                x-text="'#' + selectedId"
                                style="padding: 2px 6px; font-size: 12px; font-family: monospace; font-weight: 700; border-radius: 4px; flex-shrink: 0;"
                                class="bg-primary-50 dark:bg-primary-950/60 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800"
                            ></span>
                            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" x-text="selectedName"></span>
                        </div>
                        <x-filament::icon 
                            icon="heroicon-m-chevron-down" 
                            class="text-gray-400" 
                            style="width: 15px; height: 15px; flex-shrink: 0;" 
                        />
                    </button>
                </x-slot>

                <!-- Search Input Header -->
                <div style="padding: 10px 12px;" class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/70 dark:bg-gray-900/40">
                    <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass" size="sm">
                        <x-filament::input
                            x-ref="searchInput"
                            x-model="search"
                            type="text"
                            placeholder="ابحث برقم الجامعة أو اسمها..."
                            x-init="
                                let panel = $el.closest('.fi-dropdown-panel');
                                if (panel) {
                                    let obs = new MutationObserver(() => {
                                        if (panel.style.display === 'block') {
                                            setTimeout(() => { $el.focus(); $el.select(); }, 60);
                                        }
                                    });
                                    obs.observe(panel, { attributeFilter: ['style', 'class'] });
                                }
                            "
                            @keydown.enter.prevent="if(filteredUniversities.length > 0) { select(filteredUniversities[0]); }"
                        />
                    </x-filament::input.wrapper>
                </div>

                <!-- Universities List -->
                <div style="max-height: 300px; overflow-y: auto; padding: 6px 8px;">
                    <template x-for="item in filteredUniversities" :key="item.id">
                        <button 
                            type="button"
                            @click="select(item)"
                            style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 14px; margin: 3px 0; border-radius: 8px; width: 100%; text-align: start; cursor: pointer; font-size: 13.5px; font-weight: 500; transition: all 0.15s ease;"
                            class="hover:bg-gray-100 dark:hover:bg-white/5 text-gray-700 dark:text-gray-200"
                            :class="{'!bg-primary-50 dark:!bg-primary-950/60 !text-primary-700 dark:!text-primary-300 font-bold shadow-2xs': item.id == selectedId}"
                        >
                            <div style="display: flex; align-items: center; gap: 8px; overflow: hidden; flex: 1;">
                                <span 
                                    x-show="item.id > 0"
                                    x-text="'#' + item.id"
                                    style="display: inline-flex; align-items: center; justify-content: center; padding: 2px 6px; font-size: 12px; font-family: monospace; font-weight: 700; border-radius: 5px; min-width: 34px; flex-shrink: 0;"
                                    class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                                    :class="{'!bg-primary-100 dark:!bg-primary-900/80 !text-primary-800 dark:!text-primary-200': item.id == selectedId}"
                                ></span>
                                <span 
                                    x-show="item.id === 0"
                                    style="display: inline-flex; align-items: center; justify-content: center; padding: 2px 6px; font-size: 12px; font-weight: 700; border-radius: 5px; flex-shrink: 0;"
                                    class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                                    :class="{'!bg-primary-100 dark:!bg-primary-900/80 !text-primary-800 dark:!text-primary-200': item.id == selectedId}"
                                >الكل</span>
                                <span x-text="item.name" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.4;"></span>
                            </div>

                            <span x-show="item.id == selectedId" class="text-primary-600 dark:text-primary-400" style="flex-shrink: 0;">
                                <x-filament::icon 
                                    icon="heroicon-m-check" 
                                    style="width: 15px; height: 15px;" 
                                />
                            </span>
                        </button>
                    </template>

                    <div x-show="filteredUniversities.length === 0" style="padding: 24px 12px; text-align: center; font-size: 13px;" class="text-gray-400 dark:text-gray-500">
                        لا توجد نتائج مطابقة للبحث
                    </div>
                </div>
            </x-filament::dropdown>
        </div>
    @else
        <span style="font-size: 13.5px; font-weight: 600; padding: 4px 10px; border-radius: 8px;" class="text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 whitespace-nowrap">
            {{ auth()->user()->university?->U_NAME ?? 'جامعة غير معروفة' }}
        </span>
    @endif
</div>
