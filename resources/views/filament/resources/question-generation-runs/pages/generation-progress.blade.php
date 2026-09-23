<x-filament-panels::page>
    @php
        $run = $this->getRecord();
        $requested = max(1, $run->requested_count);
        $percentage = min(100, (int) round(($run->generated_count / $requested) * 100));
        $isFailed = $run->status === \App\GenerationStatus::Failed;
    @endphp

    <div class="lajunalar-generator-shell" wire:poll.1s="checkGenerationStatus" style="max-width: 760px; margin: 0 auto;">
        <x-filament::section>
            <div style="padding: 24px 16px; text-align: center;">
                <div @if (! $isFailed) class="lajunalar-generator-icon" @endif style="width: 84px; height: 84px; margin: 0 auto 24px; border-radius: 26px; display: grid; place-items: center; background: {{ $isFailed ? '#fee2e2' : '#eef2ff' }}; color: {{ $isFailed ? '#dc2626' : '#4f46e5' }};">
                    @if ($isFailed)
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" style="width: 40px; height: 40px;" />
                    @else
                        <x-filament::icon icon="heroicon-o-sparkles" style="width: 40px; height: 40px;" />
                    @endif
                </div>

                <p style="margin: 0; color: #6366f1; font-size: 12px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase;">
                    {{ $run->quiz->title }}
                </p>
                <h2 style="margin: 10px 0 8px; font-size: 28px; line-height: 1.2; font-weight: 800; color: rgb(17 24 39);">
                    {{ $isFailed ? 'Generator berhenti' : $run->status->label() }}
                </h2>
                <p style="max-width: 540px; margin: 0 auto; color: rgb(107 114 128); line-height: 1.7;">
                    @if ($isFailed)
                        Soal lama tetap aman. Periksa pesan kesalahan di bawah, lalu jalankan generator kembali.
                    @else
                        Sistem sedang membuat dan memvalidasi setiap pola soal. Halaman ini akan berpindah otomatis setelah semuanya siap.
                    @endif
                </p>

                @if (! $isFailed)
                    <div style="margin: 34px auto 0; max-width: 580px; text-align: left;">
                        <div style="display: flex; align-items: end; justify-content: space-between; gap: 16px; margin-bottom: 10px;">
                            <div>
                                <span style="display: block; color: rgb(107 114 128); font-size: 12px; font-weight: 700;">Soal tersimpan</span>
                                <strong style="font-size: 30px; color: rgb(17 24 39);">{{ number_format($run->generated_count) }}<span style="color: rgb(156 163 175); font-size: 18px;">/{{ number_format($run->requested_count) }}</span></strong>
                            </div>
                            <strong style="color: #4f46e5; font-size: 18px;">{{ $percentage }}%</strong>
                        </div>
                        <div style="height: 14px; overflow: hidden; border-radius: 999px; background: rgb(229 231 235);">
                            <div style="height: 100%; width: {{ $percentage }}%; border-radius: 999px; background: linear-gradient(90deg, #4f46e5, #0ea5e9); transition: width .5s ease;"></div>
                        </div>
                    </div>

                    <div class="lajunalar-progress-grid" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 28px auto 0; max-width: 580px; text-align: left;">
                        <div class="lajunalar-progress-stat"><span>Jenis paket</span><strong>{{ $run->quiz->type->label() }}</strong></div>
                        <div class="lajunalar-progress-stat"><span>Soal angka</span><strong>{{ number_format($run->number_count) }}</strong></div>
                        <div class="lajunalar-progress-stat"><span>Soal huruf</span><strong>{{ number_format($run->letter_count) }}</strong></div>
                    </div>

                    <p style="margin: 24px 0 0; color: rgb(156 163 175); font-size: 12px;">Kamu boleh meninggalkan halaman ini. Proses tetap berjalan di antrean.</p>
                @else
                    <div style="margin: 28px auto 0; max-width: 580px; padding: 16px; border: 1px solid #fecaca; border-radius: 16px; background: #fef2f2; color: #991b1b; text-align: left;">
                        <strong>Pesan kesalahan</strong>
                        <p style="margin: 6px 0 0; line-height: 1.6;">{{ $run->error_message ?: 'Generator gagal tanpa pesan tambahan.' }}</p>
                    </div>
                    <div style="margin-top: 24px; display: flex; justify-content: center;">
                        <x-filament::button tag="a" :href="\App\Filament\Resources\QuestionGenerationRuns\QuestionGenerationRunResource::getUrl('index')" color="gray" icon="heroicon-o-arrow-left">
                            Kembali ke generator
                        </x-filament::button>
                    </div>
                @endif
            </div>
        </x-filament::section>
    </div>

    <style>
        .lajunalar-generator-icon { animation: lajunalar-float 1.8s ease-in-out infinite; }
        .lajunalar-progress-stat { padding: 14px; border: 1px solid rgb(229 231 235); border-radius: 16px; background: rgb(249 250 251); }
        .lajunalar-progress-stat span { display: block; color: rgb(107 114 128); font-size: 11px; font-weight: 700; }
        .lajunalar-progress-stat strong { display: block; margin-top: 6px; color: rgb(17 24 39); font-size: 14px; }
        @keyframes lajunalar-float { 0%, 100% { transform: translateY(0) rotate(-3deg); } 50% { transform: translateY(-8px) rotate(3deg); } }
        .dark .lajunalar-progress-stat { border-color: rgb(55 65 81); background: rgb(31 41 55); }
        .dark .lajunalar-progress-stat strong, .dark .lajunalar-generator-shell h2 { color: rgb(243 244 246) !important; }
        @media (max-width: 640px) { .lajunalar-progress-grid { grid-template-columns: 1fr !important; } .lajunalar-progress-stat { text-align: center; } }
    </style>
</x-filament-panels::page>
