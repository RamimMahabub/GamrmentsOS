<x-app-layout>
<x-slot name="header"><x-page-heading title="Your account" subtitle="Update your owner profile and sign-in details."/></x-slot>
<div class="max-w-4xl space-y-6"><div class="panel p-6"><div class="max-w-xl">@include('profile.partials.update-profile-information-form')</div></div><div class="panel p-6"><div class="max-w-xl">@include('profile.partials.update-password-form')</div></div></div>
</x-app-layout>
