<x-filament-panels::page>
    @php($run = $this->getRecord())

    <div class="lajunalar-success-shell" style="max-width: 760px; margin: 0 auto;">
        <x-filament::section>
            <div style="padding: 28px 16px; text-align: center;">
                <div class="lajunalar-success-icon" style="width: 92px; height: 92px; margin: 0 auto 24px; border-radius: 999px; display: grid; place-items: center; background: #dcfce7; color: #16a34a;">
                    <x-filament::icon icon="heroicon-o-check" style="width: 46px; height: 46px;" />
                </div>
                <p style="margin: 0; color: #16a34a; font-size: 12px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase;">Generasi berhasil</p>
                <h2 style="margin: 10px 0 8px; font-size: 30px; line-height: 1.2; font-weight: 800; color: rgb(17 24 39);">{{ number_format($run->generated_count) }} soal siap digunakan</h2>
                <p style="max-width: 560px; margin: 0 auto; color: rgb(107 114 128); line-height: 1.7;">Bank soal <strong>{{ $run->quiz->title }}</strong> sudah dibuat dan tervalidasi. Riwayat pengerjaan lama tetap tersimpan aman.</p>

                <div class="lajunalar-success-grid" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 30px auto; max-width: 580px; text-align: left;">
                    <div class="lajunalar-success-stat"><span>Total soal</span><strong>{{ number_format($run->generated_count) }}</strong></div>
                    <div class="lajunalar-success-stat"><span>Soal angka</span><strong>{{ number_format($run->number_count) }}</strong></div>
                    <div class="lajunalar-success-stat"><span>Soal huruf</span><strong>{{ number_format($run->letter_count) }}</strong></div>
                </div>

                <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 10px;">
                    <x-filament::button tag="a" :href="$this->getTryQuizUrl()" target="_blank" icon="heroicon-o-play">
                        Coba kuis
                    </x-filament::button>
                    <x-filament::button tag="a" :href="$this->getEditQuizUrl()" color="gray" icon="heroicon-o-pencil-square">
                        Kelola paket
                    </x-filament::button>
                    <x-filament::button tag="a" :href="$this->getGenerateAgainUrl()" color="gray" outlined icon="heroicon-o-sparkles">
                        Generate lagi
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>
    </div>

    <style>
        .lajunalar-success-icon { animation: lajunalar-pop .55s cubic-bezier(.2,.9,.35,1.3) both; }
        .lajunalar-success-stat { padding: 16px; border: 1px solid rgb(229 231 235); border-radius: 16px; background: rgb(249 250 251); }
        .lajunalar-success-stat span { display: block; color: rgb(107 114 128); font-size: 11px; font-weight: 700; }
        .lajunalar-success-stat strong { display: block; margin-top: 6px; color: rgb(17 24 39); font-size: 22px; }
        @keyframes lajunalar-pop { from { opacity: 0; transform: scale(.55) rotate(-15deg); } to { opacity: 1; transform: scale(1) rotate(0); } }
        .dark .lajunalar-success-stat { border-color: rgb(55 65 81); background: rgb(31 41 55); }
        .dark .lajunalar-success-stat strong, .dark .lajunalar-success-shell h2 { color: rgb(243 244 246) !important; }
        @media (max-width: 640px) { .lajunalar-success-grid { grid-template-columns: 1fr !important; } .lajunalar-success-stat { text-align: center; } }
    </style>
</x-filament-panels::page>
