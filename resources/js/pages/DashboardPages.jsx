import { ArrowRight, BarChart3, CheckCircle2, Clock3, Hash, History, Layers3, Play, RotateCcw, Trophy, Type } from 'lucide-react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { AppShell, ErrorState, formatDate, PageLoader, typeTone } from '../components/Layout';
import { useDashboardQuery, useMeQuery, useQuizQuery, useQuizzesQuery, useStartAttemptMutation } from '../store/api';

function greeting() {
    const hour = new Date().getHours();
    if (hour < 11) return 'Selamat pagi';
    if (hour < 15) return 'Selamat siang';
    if (hour < 18) return 'Selamat sore';
    return 'Selamat malam';
}

export function DashboardPage() {
    const { data, isLoading, isError } = useDashboardQuery();
    const { data: quizzes } = useQuizzesQuery();
    if (isLoading) return <AppShell><PageLoader/></AppShell>;
    if (isError) return <AppShell><div className="mx-auto max-w-7xl px-5 py-20"><ErrorState/></div></AppShell>;
    const history = data.history?.data ?? data.history ?? [];
    return <AppShell><div className="mx-auto max-w-7xl px-5 py-10 lg:px-8">
        <section className="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-indigo-700 via-indigo-600 to-sky-500 px-7 py-9 text-white shadow-xl shadow-indigo-600/20 sm:px-10">
            <div className="absolute -right-16 -top-20 size-64 rounded-full border-[35px] border-white/10"/>
            <p className="text-sm font-semibold text-indigo-100">{greeting()},</p><h1 className="mt-1 text-3xl font-black tracking-tight sm:text-4xl">{data.user.name}</h1><p className="mt-2 max-w-xl text-indigo-100">Terus latih fokusmu. Setiap percobaan kecil membentuk respons yang lebih cepat dan tepat.</p>
        </section>
        <section className="mt-6 grid gap-4 sm:grid-cols-3">
            <Stat icon={Layers3} tone="indigo" label="Paket dikerjakan" value={data.summary.packages_completed}/>
            <Stat icon={BarChart3} tone="sky" label="Rata-rata nilai" value={data.summary.average_score}/>
            <Stat icon={History} tone="amber" label="Total pengerjaan" value={data.summary.total_attempts}/>
        </section>
        <section className="mt-4 grid gap-4 md:grid-cols-2">
            <TypePerformance icon={Hash} title="Performa angka" stats={data.performance_by_type?.number}/>
            <TypePerformance icon={Type} title="Performa huruf" stats={data.performance_by_type?.letter}/>
        </section>
        <div className="mt-10 grid gap-8 lg:grid-cols-[1.25fr_.75fr]">
            <section><div className="flex items-end justify-between"><div><p className="eyebrow">Latihan tersedia</p><h2 className="mt-1 text-2xl font-extrabold">Pilih paketmu</h2></div><Link to="/paket" className="text-sm font-bold text-indigo-600">Lihat semua</Link></div><div className="mt-5 grid gap-4 md:grid-cols-2">{(quizzes?.data ?? []).slice(0,4).map(quiz => <QuizCard key={quiz.uuid} quiz={quiz}/>)}</div></section>
            <section><div className="flex items-end justify-between gap-4"><div><p className="eyebrow">Ringkasan riwayat</p><h2 className="mt-1 text-2xl font-extrabold">Per paket</h2><p className="mt-1 text-xs text-slate-500">Maksimal 8 paket terbaru</p></div><Link to="/riwayat" className="shrink-0 text-sm font-bold text-indigo-600 hover:text-indigo-700">Lihat semua</Link></div><div className="card mt-5 divide-y divide-slate-100 overflow-hidden dark:divide-slate-800">{history.length ? history.map(item => <Link to={item.latest_attempt ? `/attempt/${item.latest_attempt.uuid}/hasil` : `/paket/${item.quiz.uuid}`} key={item.quiz.uuid} className="flex items-center gap-3 p-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/50"><span className="grid size-10 place-items-center rounded-xl bg-slate-100 font-bold text-indigo-600 dark:bg-slate-800">{item.attempts_count}×</span><div className="min-w-0 flex-1"><p className="truncate font-bold">{item.quiz.title}</p><p className="text-xs text-slate-500">Terbaik {item.best_score} · Rata-rata {item.average_score}{item.latest_attempt ? ` · Hasil terakhir ${item.latest_attempt.score}` : ''}</p></div><ArrowRight size={18} className="text-slate-400"/></Link>) : <EmptyHistory/>}</div></section>
        </div>
    </div></AppShell>;
}

export function PackagesPage() {
    const { data, isLoading, isError } = useQuizzesQuery();
    if (isLoading) return <AppShell><PageLoader/></AppShell>;
    if (isError) return <AppShell><div className="mx-auto max-w-7xl px-5 py-20"><ErrorState/></div></AppShell>;
    return <AppShell><div className="mx-auto max-w-7xl px-5 py-12 lg:px-8"><p className="eyebrow">Bank latihan</p><h1 className="mt-2 text-4xl font-black tracking-tight">Paket speed test</h1><p className="mt-3 max-w-2xl text-slate-500">Pilih latihan angka, huruf, atau campuran. Semua paket bisa dikerjakan berkali-kali.</p><div className="mt-9 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">{data.data.map(quiz => <QuizCard key={quiz.uuid} quiz={quiz}/>)}</div></div></AppShell>;
}

export function QuizDetailPage() {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const { data, isLoading, isError } = useQuizQuery(uuid);
    const [start, startState] = useStartAttemptMutation();
    if (isLoading) return <AppShell><PageLoader/></AppShell>;
    if (isError) return <AppShell><div className="mx-auto max-w-7xl px-5 py-20"><ErrorState title="Paket tidak ditemukan"/></div></AppShell>;
    const quiz = data.data;
    async function begin() {
        const response = await start(uuid).unwrap();
        navigate(`/attempt/${response.data.uuid}`);
    }
    return <AppShell><div className="mx-auto max-w-6xl px-5 py-12 lg:px-8"><Link to="/paket" className="text-sm font-bold text-indigo-600">← Kembali ke paket</Link><div className="mt-5 grid gap-7 lg:grid-cols-[1fr_360px]">
        <section className="card p-7 sm:p-9"><span className={`rounded-full px-3 py-1 text-xs font-bold ${typeTone[quiz.type]}`}>{quiz.type_label}</span><h1 className="mt-5 text-4xl font-black tracking-tight">{quiz.title}</h1><p className="mt-4 whitespace-pre-line leading-8 text-slate-600 dark:text-slate-300">{quiz.description || 'Latihan kecepatan untuk menemukan angka yang hilang atau huruf yang muncul ganda.'}</p><div className="mt-8 grid gap-3 sm:grid-cols-3"><MiniStat icon={Layers3} value={quiz.question_count} label={`soal dari bank ${quiz.available_questions ?? quiz.bank_size}`}/><MiniStat icon={Clock3} value={Math.ceil(quiz.duration_seconds/60)} label="menit"/><MiniStat icon={RotateCcw} value="∞" label="pengulangan acak"/></div><div className="mt-8 rounded-2xl bg-amber-50 p-5 text-sm leading-6 text-amber-900 dark:bg-amber-950/30 dark:text-amber-200"><strong>Mode simulasi:</strong> soal dikerjakan berurutan dan tidak dapat dibuka kembali. Pembahasan baru tersedia setelah attempt selesai atau waktu habis.</div></section>
        <aside><div className="card p-6"><h2 className="text-lg font-bold">Siap mulai?</h2><p className="mt-2 text-sm leading-6 text-slate-500">Timer dimulai setelah tombol di bawah ditekan. Pastikan koneksi stabil dan keyboard siap.</p><button onClick={begin} disabled={startState.isLoading} className="btn-primary mt-5 w-full"><Play size={18}/>{startState.isLoading ? 'Menyiapkan…' : 'Mulai latihan'}</button>{startState.error && <p className="mt-3 text-sm text-rose-600">Paket belum siap atau soal belum mencukupi.</p>}</div><AttemptHistory attempts={quiz.attempts ?? []}/></aside>
    </div></div></AppShell>;
}

function Stat({icon: Icon, label, value, tone}) { const colors = {indigo:'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50', sky:'bg-sky-50 text-sky-600 dark:bg-sky-950/50', amber:'bg-amber-50 text-amber-600 dark:bg-amber-950/50'}; return <div className="card flex items-center gap-4 p-5"><span className={`grid size-12 place-items-center rounded-2xl ${colors[tone]}`}><Icon size={22}/></span><div><p className="text-2xl font-black">{value}</p><p className="text-sm text-slate-500">{label}</p></div></div>; }
function MiniStat({icon: Icon, value, label}) { return <div className="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60"><Icon size={19} className="text-indigo-600"/><p className="mt-3 text-xl font-black">{value}</p><p className="text-xs text-slate-500">{label}</p></div>; }
function TypePerformance({icon: Icon, title, stats = {}}) { return <div className="card flex items-center gap-4 p-5"><span className="grid size-12 shrink-0 place-items-center rounded-2xl bg-slate-100 text-indigo-600 dark:bg-slate-800"><Icon size={22}/></span><div className="min-w-0 flex-1"><div className="flex items-center justify-between gap-3"><p className="font-black">{title}</p><span className="text-lg font-black text-indigo-600">{stats.accuracy ?? 0}%</span></div><div className="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500"><span>{stats.correct ?? 0} benar</span><span>{stats.wrong ?? 0} salah</span><span>{((stats.average_response_ms ?? 0)/1000).toFixed(2)} dtk/soal</span></div></div></div>; }
function QuizCard({quiz}) { return <Link to={`/paket/${quiz.uuid}`} className="card group p-6 transition hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg dark:hover:border-indigo-800"><div className="flex items-center justify-between"><span className={`rounded-full px-3 py-1 text-xs font-bold ${typeTone[quiz.type]}`}>{quiz.type_label}</span><ArrowRight size={18} className="text-slate-300 transition group-hover:translate-x-1 group-hover:text-indigo-600"/></div><h3 className="mt-5 text-xl font-extrabold">{quiz.title}</h3><p className="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{quiz.description || 'Latihan speed test dengan penilaian dan pembahasan lengkap.'}</p><div className="mt-5 flex items-center gap-4 text-xs font-semibold text-slate-500"><span className="flex items-center gap-1"><Layers3 size={14}/>{quiz.question_count} soal</span><span className="flex items-center gap-1"><Clock3 size={14}/>{Math.ceil(quiz.duration_seconds/60)} menit</span></div></Link>; }
function AttemptHistory({attempts}) { return <div className="card mt-5 p-6"><h2 className="flex items-center gap-2 font-bold"><History size={18} className="text-indigo-600"/>Riwayat paket</h2><div className="mt-4 space-y-3">{attempts.length ? attempts.map((attempt, i) => <Link key={attempt.uuid} to={attempt.status === 'in_progress' ? `/attempt/${attempt.uuid}` : `/attempt/${attempt.uuid}/hasil`} className="flex items-center justify-between rounded-xl border border-slate-100 p-3 transition hover:border-indigo-200 hover:bg-indigo-50/50 dark:border-slate-800 dark:hover:border-indigo-800 dark:hover:bg-indigo-950/20"><div><p className="text-sm font-bold">Pengerjaan {attempts.length-i}</p><p className="text-xs text-slate-500">{formatDate(attempt.started_at)}</p></div><span className="font-black text-indigo-600">{attempt.status === 'in_progress' ? 'Lanjut' : `Lihat hasil · ${attempt.score}`}</span></Link>) : <p className="py-5 text-center text-sm text-slate-500">Belum ada riwayat.</p>}</div></div>; }
function EmptyHistory() { return <div className="p-8 text-center"><Trophy size={28} className="mx-auto text-slate-300"/><p className="mt-2 text-sm text-slate-500">Riwayatmu akan diringkas per paket di sini.</p></div>; }
