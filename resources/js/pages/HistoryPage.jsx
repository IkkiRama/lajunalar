import { ArrowLeft, ArrowRight, CheckCircle2, Clock3, History, Layers3, Play, Trophy, XCircle } from 'lucide-react';
import { useState } from 'react';
import { Link } from 'react-router-dom';
import { AppShell, ErrorState, formatDate, PageLoader, typeTone } from '../components/Layout';
import { useAttemptHistoryQuery } from '../store/api';

const statusTone = {
    in_progress: 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
    submitted: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
    expired: 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300',
};

export function HistoryPage() {
    const [page, setPage] = useState(1);
    const { data, isLoading, isFetching, isError } = useAttemptHistoryQuery({ page });

    if (isLoading) return <AppShell><PageLoader label="Mengumpulkan seluruh riwayatmu…"/></AppShell>;
    if (isError) return <AppShell><div className="mx-auto max-w-7xl px-5 py-20"><ErrorState title="Riwayat belum dapat dimuat"/></div></AppShell>;

    const attempts = data?.data ?? [];
    const meta = data?.meta ?? { current_page: 1, last_page: 1, total: 0, from: 0, to: 0 };

    function changePage(nextPage) {
        setPage(nextPage);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    return <AppShell><div className="mx-auto max-w-6xl px-5 py-12 lg:px-8">
        <div className="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div><p className="eyebrow">Aktivitas latihan</p><h1 className="mt-2 text-4xl font-black tracking-tight">Semua riwayat</h1><p className="mt-3 max-w-2xl text-slate-500">Setiap pengerjaan dari seluruh paket, diurutkan dari yang paling baru.</p></div>
            <div className="card flex items-center gap-4 px-5 py-4"><span className="grid size-11 place-items-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50"><History size={21}/></span><div><p className="text-2xl font-black">{meta.total}</p><p className="text-xs text-slate-500">total pengerjaan</p></div></div>
        </div>

        {attempts.length ? <div className={`mt-8 space-y-4 transition-opacity ${isFetching ? 'opacity-50' : 'opacity-100'}`}>
            {attempts.map((attempt) => <AttemptHistoryCard key={attempt.uuid} attempt={attempt}/>) }
        </div> : <EmptyHistory/>}

        {meta.last_page > 1 && <nav aria-label="Pagination riwayat" className="card mt-7 flex flex-col items-center justify-between gap-4 p-4 sm:flex-row">
            <p className="text-sm text-slate-500">Menampilkan <strong className="text-slate-800 dark:text-slate-200">{meta.from}–{meta.to}</strong> dari <strong className="text-slate-800 dark:text-slate-200">{meta.total}</strong> pengerjaan</p>
            <div className="flex items-center gap-2"><button type="button" onClick={() => changePage(page - 1)} disabled={page <= 1 || isFetching} className="inline-flex cursor-pointer items-center gap-1 rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold transition hover:border-indigo-300 hover:text-indigo-600 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700"><ArrowLeft size={16}/>Sebelumnya</button><span className="px-2 text-sm font-bold">{meta.current_page} / {meta.last_page}</span><button type="button" onClick={() => changePage(page + 1)} disabled={page >= meta.last_page || isFetching} className="inline-flex cursor-pointer items-center gap-1 rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold transition hover:border-indigo-300 hover:text-indigo-600 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700">Berikutnya<ArrowRight size={16}/></button></div>
        </nav>}
    </div></AppShell>;
}

function AttemptHistoryCard({ attempt }) {
    const inProgress = attempt.status === 'in_progress';
    const destination = inProgress ? `/attempt/${attempt.uuid}` : `/attempt/${attempt.uuid}/hasil`;

    return <article className="card overflow-hidden p-5 sm:p-6"><div className="flex flex-col gap-5 sm:flex-row sm:items-center">
        <span className={`grid size-12 shrink-0 place-items-center rounded-2xl ${inProgress ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/40' : 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50'}`}>{inProgress ? <Play size={21}/> : <Trophy size={21}/>}</span>
        <div className="min-w-0 flex-1"><div className="flex flex-wrap items-center gap-2"><h2 className="truncate text-lg font-black">{attempt.quiz.title}</h2><span className={`rounded-full px-2.5 py-1 text-[11px] font-bold ${typeTone[attempt.quiz.type]}`}>{attempt.quiz.type_label}</span><span className={`rounded-full px-2.5 py-1 text-[11px] font-bold ${statusTone[attempt.status]}`}>{attempt.status_label}</span></div><p className="mt-1 flex items-center gap-1.5 text-sm text-slate-500"><Clock3 size={14}/>{formatDate(attempt.started_at)}</p></div>
        <div className="grid grid-cols-2 gap-x-6 gap-y-2 text-sm sm:grid-cols-4">
            {inProgress ? <>
                <Metric icon={Layers3} label="Dijawab" value={`${attempt.progress_count}/${attempt.question_count}`}/>
                <Metric icon={Clock3} label="Tersisa" value={Math.max(0, attempt.question_count - attempt.progress_count)}/>
            </> : <>
                <Metric icon={Layers3} label="Skor" value={`${attempt.score}/${attempt.question_count}`}/>
                <Metric icon={CheckCircle2} label="Benar" value={attempt.correct_count} tone="text-emerald-600"/>
                <Metric icon={XCircle} label="Salah" value={attempt.wrong_count} tone="text-rose-600"/>
                <Metric icon={Trophy} label="Akurasi" value={`${attempt.accuracy}%`} tone="text-indigo-600"/>
            </>}
        </div>
        <Link to={destination} className="btn-primary shrink-0">{inProgress ? 'Lanjutkan' : 'Lihat hasil'}<ArrowRight size={17}/></Link>
    </div></article>;
}

function Metric({ icon: Icon, label, value, tone = 'text-slate-900 dark:text-white' }) {
    return <div><p className="flex items-center gap-1 text-xs text-slate-500"><Icon size={13}/>{label}</p><p className={`mt-0.5 font-black ${tone}`}>{value}</p></div>;
}

function EmptyHistory() {
    return <div className="card mt-8 p-10 text-center"><History size={36} className="mx-auto text-slate-300"/><h2 className="mt-4 text-xl font-black">Belum ada riwayat</h2><p className="mt-2 text-sm text-slate-500">Mulai satu paket latihan dan hasilnya akan tersimpan di sini.</p><Link to="/paket" className="btn-primary mt-6">Pilih paket<ArrowRight size={17}/></Link></div>;
}
