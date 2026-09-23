@php
    use Filament\Support\Icons\Heroicon;
    use Filament\View\PanelsIconAlias;

    $user = filament()->auth()->user();
@endphp

<x-filament-widgets::widget
    class="fi-account-widget"
    x-data="{ submitLogout() { this.$refs.logoutForm.submit() } }"
>
    <x-filament::section>
            <x-filament-panels::avatar.user
                size="lg"
                :user="$user"
                loading="lazy"
            />

            <div class="fi-account-widget-main">
                <h2 class="fi-account-widget-heading">
                    {{ __('filament-panels::widgets/account-widget.welcome', ['app' => config('app.name')]) }}
                </h2>

                <p class="fi-account-widget-user-name">
                    {{ filament()->getUserName($user) }}
                </p>
            </div>

            <form
                x-ref="logoutForm"
                x-on:submit.prevent="$dispatch('open-modal', { id: 'confirm-admin-widget-logout' })"
                action="{{ filament()->getLogoutUrl() }}"
                method="post"
                class="fi-account-widget-logout-form"
            >
                @csrf

                <x-filament::button
                    color="gray"
                    :icon="Heroicon::ArrowLeftEndOnRectangle"
                    :icon-alias="PanelsIconAlias::WIDGETS_ACCOUNT_LOGOUT_BUTTON"
                    labeled-from="sm"
                    tag="button"
                    type="submit"
                >
                    {{ __('filament-panels::widgets/account-widget.actions.logout.label') }}
                </x-filament::button>
            </form>
    </x-filament::section>

    <x-filament::modal
        id="confirm-admin-widget-logout"
        width="md"
        icon="heroicon-o-arrow-left-end-on-rectangle"
        icon-color="danger"
        heading="Keluar dari LajuNalar Admin?"
        description="Sesi admin pada perangkat ini akan diakhiri. Kamu perlu masuk kembali untuk mengelola data."
    >
        <x-slot name="footer">
            <div class="flex justify-end gap-3">
                <x-filament::button
                    color="gray"
                    x-on:click="$dispatch('close-modal', { id: 'confirm-admin-widget-logout' })"
                >
                    Batal
                </x-filament::button>
                <x-filament::button color="danger" x-on:click="submitLogout()">
                    Ya, keluar
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</x-filament-widgets::widget>
