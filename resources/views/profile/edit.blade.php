<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container px-3 px-lg-4" style="max-width: 1200px;">
            <div class="p-3 p-sm-4 bg-white shadow-sm rounded-3 mb-4">
                <div style="max-width: 36rem;">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-3 p-sm-4 bg-white shadow-sm rounded-3 mb-4">
                <div style="max-width: 36rem;">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-3 p-sm-4 bg-white shadow-sm rounded-3 mb-4">
                <div style="max-width: 36rem;">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
