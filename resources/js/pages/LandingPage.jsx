import { ArrowRight, BadgeCheck, BarChart3, BrainCircuit, Clock3, Keyboard, ShieldCheck, Sparkles } from 'lucide-react';
import { Link } from 'react-router-dom';
import { Brand, Footer, Header } from '../components/Layout';
import { useMeQuery } from '../store/api';

export function LandingPage() {
    const { data: user } = useMeQuery();
    return <div className="min-h-screen overflow-hidden bg-white dark:bg-slate-950">
        <Header minimal/>
        <main>
            <section className="relative isolate overflow-hidden">
                <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_15%_20%,rgba(99,102,241,.14),transparent_28%),radial-gradient(circle_at_85%_35%,rgba(14,165,233,.12),transparent_26%)]"/>
                <div className="mx-auto grid max-w-7xl items-center gap-14 px-5 py-20 lg:grid-cols-[1.06fr_.94fr] lg:px-8 lg:py-28">
                    <div>
                        <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 dark:border-indigo-900 dark:bg-indigo-950/50 dark:text-indigo-300"><Sparkles size={16}/> Latihan Tahap 3 yang fokus dan terukur</div>
                        <h1 className="max-w-3xl text-5xl font-black leading-[1.05] tracking-[-.045em] text-slate-950 dark:text-white sm:text-6xl lg:text-7xl">Latih kecepatan.<br/><span className="bg-gradient-to-r from-indigo-600 to-sky-500 bg-clip-text text-transparent">Jaga ketepatan.</span></h1>
                        <p className="mt-6 max-w-2xl text-lg leading-8 text-slate-600 dark:text-slate-300">Simulasi speed test angka hilang dan huruf ganda dengan tempo nyata, paket bervariasi, serta pembahasan lengkap untuk melihat pola performamu.</p>
                        <div className="mt-9 flex flex-col gap-3 sm:flex-row">
                            {user ? <Link to="/dashboard" className="btn-primary">Buka dashboard <ArrowRight size={19}/></Link> : <a href="/auth/google/redirect" className="btn-primary"><GoogleMark/> Masuk dengan Google</a>}
                            <a href="#cara-kerja" className="btn-secondary">Lihat cara kerja</a>
                        </div>
                        <p className="mt-4 flex items-center gap-2 text-xs text-slate-500"><ShieldCheck size={15} className="text-emerald-500"/> Tanpa password lokal. Login aman melalui Google.</p>
                    </div>
                    <HeroMockup/>
                </div>
            </section>

            <section id="cara-kerja" className="border-y border-slate-100 bg-slate-50 py-20 dark:border-slate-800 dark:bg-slate-900/40">
                <div className="mx-auto max-w-7xl px-5 lg:px-8">
                    <div className="max-w-2xl"><p className="eyebrow">Satu alur sederhana</p><h2 className="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Dibuat untuk ritme tes, bukan distraksi</h2></div>
                    <div className="mt-10 grid gap-5 md:grid-cols-3">
                        <Feature icon={Keyboard} number="01" title="Jawab berurutan" text="Masukkan satu angka atau huruf, lalu tekan Enter. Tidak ada tombol kembali saat ujian berlangsung."/>
                        <Feature icon={Clock3} number="02" title="Waktu server" text="Countdown tetap akurat meskipun halaman dimuat ulang dan ujian otomatis selesai saat waktu habis."/>
                        <Feature icon={BarChart3} number="03" title="Bedah hasil" text="Lihat benar, salah, tidak dijawab, waktu respons, riwayat, dan pembahasan setiap nomor."/>
                    </div>
                </div>
            </section>
            <section className="mx-auto max-w-7xl px-5 py-20 lg:px-8">
                <div className="rounded-[2rem] bg-slate-950 px-7 py-12 text-white shadow-2xl dark:bg-indigo-950 sm:px-12 lg:flex lg:items-center lg:justify-between">
                    <div><p className="text-sm font-bold uppercase tracking-[.18em] text-indigo-300">Siap menguji fokus?</p><h2 className="mt-3 text-3xl font-extrabold">Mulai dari satu paket. Ulangi sampai ritmemu terbentuk.</h2></div>
                    {user ? <Link to="/paket" className="mt-6 inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 font-bold text-slate-950 lg:mt-0">Pilih paket <ArrowRight size={19}/></Link> : <a href="/auth/google/redirect" className="mt-6 inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 font-bold text-slate-950 lg:mt-0">Mulai latihan <ArrowRight size={19}/></a>}
                </div>
            </section>
        </main>
        <Footer/>
    </div>;
}

function GoogleMark() {
    return <span className="grid size-6 place-items-center rounded-full bg-white text-sm font-black text-blue-600">G</span>;
}

function Feature({ icon: Icon, number, title, text }) {
    return <article className="card p-7"><div className="flex items-center justify-between"><span className="grid size-12 place-items-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950"><Icon size={23}/></span><span className="text-3xl font-black text-slate-100 dark:text-slate-800">{number}</span></div><h3 className="mt-6 text-xl font-bold">{title}</h3><p className="mt-2 leading-7 text-slate-500 dark:text-slate-400">{text}</p></article>;
}

function HeroMockup() {
    return <div className="relative mx-auto w-full max-w-xl animate-floaty">
        <div className="absolute -inset-8 -z-10 rounded-full bg-indigo-300/20 blur-3xl"/>
        <div className="rounded-[2rem] border border-white/70 bg-white/90 p-5 shadow-2xl shadow-indigo-900/15 backdrop-blur dark:border-slate-700 dark:bg-slate-900/90">
            <div className="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800"><Brand/><span className="rounded-xl bg-slate-950 px-4 py-2 font-mono font-bold text-white">04:21</span></div>
            <div className="py-7"><div className="flex items-center justify-between"><span className="eyebrow">Soal 128 dari 500</span><BadgeCheck className="text-emerald-500"/></div><h3 className="mt-5 text-sm font-medium text-slate-500">Temukan angka yang hilang</h3><p className="mt-3 font-mono text-3xl font-bold tracking-[.25em] sm:text-4xl">3 0 9 7 4 1 5 2 6</p><div className="mt-7 flex items-center gap-3"><div className="flex h-14 flex-1 items-center rounded-2xl border-2 border-indigo-200 px-5 text-2xl font-bold text-indigo-700 dark:border-indigo-800">8</div><div className="grid size-14 place-items-center rounded-2xl bg-indigo-600 text-white"><ArrowRight/></div></div></div>
            <div className="grid grid-cols-10 gap-2">{Array.from({length: 30}, (_, i) => <span key={i} className={`aspect-square rounded-md ${i < 17 ? 'bg-emerald-400' : i === 17 ? 'bg-indigo-600' : 'border border-slate-200 dark:border-slate-700'}`}/>)}</div>
        </div>
        <div className="absolute -bottom-5 -left-5 flex items-center gap-3 rounded-2xl bg-white p-4 shadow-xl dark:bg-slate-800"><span className="grid size-10 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><BrainCircuit size={20}/></span><div><p className="text-xs text-slate-500">Sinkronisasi</p><p className="text-sm font-bold">Jawaban tersimpan</p></div></div>
    </div>;
}
