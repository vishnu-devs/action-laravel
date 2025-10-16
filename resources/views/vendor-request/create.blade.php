<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Become a Vendor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('vendor-request.store') }}" class="max-w-md mx-auto">
                        @csrf

                        <div class="mb-6">
                            <x-input-label for="company_name" :value="__('Company Name')" />
                            <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="company_address" :value="__('Company Address')" />
                            <x-text-input id="company_address" name="company_address" type="text" class="mt-1 block w-full" :value="old('company_address')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('company_address')" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Submit Request') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>