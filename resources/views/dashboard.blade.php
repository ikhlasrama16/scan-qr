<x-app-layout>
    <x-slot name="header">
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex flex-col items-center justify-center text-center">
                    <p class="mb-4">Selamat datang, {{ Auth::user()->name }}!, {{ Auth::user()->kantor }}</p>

                    <div class="w-full max-w-md">
                        <h3 class="text-lg font-bold mb-4">Scan QR Code</h3>

                        <div id="reader" class="mx-auto" style="width: 300px;"></div>

                        <!-- Modal -->
                        <div id="qrModal"
                            class="hidden fixed top-0 left-0 w-full h-full bg-black bg-opacity-50 flex items-center justify-center">
                            <div class="bg-white p-6 rounded shadow w-11/12 max-w-md">
                                <h3 class="text-lg font-semibold mb-4">Konfirmasi Data Scan</h3>
                                <p class="mb-2 text-gray-800"><strong>Lokasi Scan:</strong> <span
                                        id="qr-preview"></span></p>
                                <input type="hidden" id="lokasi_scan">

                                <label class="block mb-1 text-gray-700">Keterangan:</label>
                                <textarea id="keterangan" class="w-full border rounded p-2 mb-4 text-black" rows="3"></textarea>

                                <div class="flex justify-end gap-2">
                                    <x-primary-button onclick="closeModal()"
                                        class="px-4 py-2 bg-gray-400 text-white rounded">Batal</x-primary-button>
                                    <x-primary-button onclick="submitQrLog()"
                                        class="px-4 py-2 bg-green-600 text-white rounded">Kirim</x-primary-button>
                                </div>
                            </div>
                        </div>



                        <div class="mt-4 text-left">
                            <div class="mt-6">
                                <h3 class="text-lg font-semibold mb-2">History Scan
                                    ({{ \Carbon\Carbon::now()->format('d M Y') }})
                                </h3>

                                @if ($todayLogs->isEmpty())
                                    <p class="text-sm text-gray-500">Belum ada scan hari ini.</p>
                                @else
                                    <ul class="text-sm text-gray-700 dark:text-gray-200 space-y-2">
                                        @foreach ($todayLogs as $log)
                                            <li class="border-b pb-1">
                                                <strong>{{ $log->lokasi_scan }}</strong><br>
                                                <span class="text-xs text-gray-500">
                                                    {{ $log->created_at->format('H:i:s') }}
                                                </span>
                                                @if ($log->keterangan)
                                                    <div class="text-xs mt-1">Keterangan: {{ $log->keterangan }}</div>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Library dan Script Scanner --}}
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrCode;

        function onScanSuccess(decodedText, decodedResult) {
            // Tampilkan hasil ke input dan modal
            document.getElementById('lokasi_scan').value = decodedText;
            document.getElementById('qr-preview').innerText = decodedText;
            document.getElementById('qrModal').classList.remove('hidden');

            // Stop scanner setelah berhasil scan
            if (html5QrCode) {
                html5QrCode.stop().catch(err => console.warn("Gagal stop scanner:", err));
            }
        }

        function startQrScanner() {
            html5QrCode = new Html5Qrcode("reader");

            html5QrCode.start({
                    facingMode: "environment"
                }, // kamera belakang
                {
                    fps: 10,
                    qrbox: 250
                },
                onScanSuccess
            ).catch(err => {
                console.error("QR Scan error:", err);
            });
        }

        function closeModal() {
            document.getElementById('qrModal').classList.add('hidden');

            // Restart scanner saat modal ditutup
            if (html5QrCode) {
                html5QrCode.start({
                        facingMode: "environment"
                    }, {
                        fps: 10,
                        qrbox: 250
                    },
                    onScanSuccess
                ).catch(err => console.error("Gagal restart scanner:", err));
            }
        }

        function submitQrLog() {
            const lokasiScan = document.getElementById('lokasi_scan').value;
            const keterangan = document.getElementById('keterangan').value;

            fetch("{{ url('/log-qr') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        lokasi_scan: lokasiScan,
                        keterangan: keterangan
                    })
                })
                .then(async response => {
                    const text = await response.text();
                    if (!response.ok) throw new Error(text);
                    return JSON.parse(text);
                })
                .then(data => {
                    alert("Data QR berhasil dikirim!");
                    closeModal();
                    location.reload();
                })
                .catch(error => {
                    alert("Gagal mengirim data QR: " + error.message);
                });
        }

        // Jalankan scanner saat halaman dimuat
        document.addEventListener("DOMContentLoaded", startQrScanner);
    </script>



</x-app-layout>
