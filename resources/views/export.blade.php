<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Export Log QR Code
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Export Berdasarkan Range Tanggal -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <a href="{{ route('export.all') }}">
                    <x-secondary-button class="mb-4">Export All</x-secondary-button>
                </a>

                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Export Berdasarkan Tanggal</h3>

                <form action="{{ route('export.qrlog.range') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf
                    <div>
                        <label for="from" class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Dari
                            Tanggal</label>

                        <input type="date" name="from" id="from"
                            class="w-full rounded border-gray-300 dark:border-gray-700">
                    </div>
                    <div>
                        <label for="to" class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Sampai
                            Tanggal</label>
                        <input type="date" name="to" id="to"
                            class="w-full rounded border-gray-300 dark:border-gray-700">
                    </div>
                    <p class="text-sm text-gray-500 mb-3">Unduh log berdasarkan tanggal tertentu.</p>
                    <div>
                        <x-primary-button class="w-fit self-end">Export</x-primary-button>
                    </div>


                </form>
            </div>

            <!-- Export Preset Range -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mt-5">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Export Cepat (Preset)</h3>

                <form action="{{ route('export.qrlog.preset') }}" method="GET"
                    class="flex flex-col sm:flex-row gap-4 sm:items-end">
                    <div class="w-full sm:w-1/3">
                        <label for="range" class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Pilih
                            Range</label>
                        <select name="range" id="range"
                            class="w-full rounded border-gray-300 dark:border-gray-700">
                            <option value="today">Hari Ini</option>
                            <option value="week">Minggu Ini</option>
                            <option value="month">Bulan Ini</option>
                        </select>
                    </div>
                    <div>
                        <x-primary-button class="w-fit self-end">Export</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
