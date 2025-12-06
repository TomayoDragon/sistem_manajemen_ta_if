<x-app-layout>
    @if(Auth::user()->hasRole('mahasiswa'))
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <div class="max-w-xl">
                <section>
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            Manajemen Kunci Digital
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Generate sepasang kunci (Private & Public) untuk tanda tangan digital Anda.
                        </p>
                    </header>

                    @if(Auth::user()->mahasiswa->public_key)
                        <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            Anda sudah memiliki Kunci Digital.
                        </div>
                    @else
                        <form method="POST" action="{{ route('mahasiswa.keys.generate') }}" class="mt-6">
                            @csrf
                            <x-primary-button>
                                Generate Kunci Saya
                            </x-primary-button>
                        </form>
                    @endif
                </section>
            </div>
        </div>
    @endif
</x-app-layout>