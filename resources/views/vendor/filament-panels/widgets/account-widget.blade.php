@php
    $user = filament()->auth()->user();
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <x-filament-panels::avatar.user
            size="lg"
            :user="$user"
            loading="lazy"
        />

        <div class="fi-account-widget-main">
            <h2 class="fi-account-widget-heading">
                @php
                    $uniName = 'الجامعة غير معروفة';
                    if (auth()->user()->UNID == 0) {
                        $selected = session('selected_unid', 0);
                        if ($selected == 0) {
                            $uniName = 'الكل (جميع الجامعات)';
                        } else {
                            $uniName = \App\Models\University::withoutGlobalScope(\App\Models\Scopes\UniversityScope::class)->find($selected)?->U_NAME ?? 'الجامعة غير معروفة';
                        }
                    } else {
                        $uniName = auth()->user()->university?->U_NAME ?? 'الجامعة غير معروفة';
                    }
                @endphp
                {{ $uniName }}
            </h2>

            <p class="fi-account-widget-user-name">
                {{ filament()->getUserName($user) }}
            </p>
        </div>

        <form
            action="{{ filament()->getLogoutUrl() }}"
            method="post"
            class="fi-account-widget-logout-form"
        >
            @csrf

            <x-filament::button
                color="gray"
                :icon="\Filament\Support\Icons\Heroicon::ArrowLeftEndOnRectangle"
                :icon-alias="\Filament\View\PanelsIconAlias::WIDGETS_ACCOUNT_LOGOUT_BUTTON"
                labeled-from="sm"
                tag="button"
                type="submit"
            >
                {{ __('filament-panels::widgets/account-widget.actions.logout.label') }}
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
