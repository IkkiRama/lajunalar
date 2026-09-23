import { AlertTriangle, ArrowLeft, CheckCircle2, Clock3, Play, RefreshCw, Sparkles } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { AdminShell } from '../components/AdminLayout';
import { ErrorState, PageLoader } from '../components/Layout';
import { useGenerationQuery, useResumeGenerationMutation } from '../store/api';

export function GenerationProgressPage() {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const { data, isLoading, isError, refetch } = useGenerationQuery(uuid, { pollingInterval: 750, refetchOnFocus: true });
    const [resume, resumeState] = useResumeGenerationMutation();
    const resumed = useRef(false);
    const run = data?.data;

    useEffect(() => {
        if (run?.status === 'completed') {
            const timer = window.setTimeout(() => navigate(`/kelola/generator/${uuid}/sukses`, { replace: true }), 700);
            return () => window.clearTimeout(timer);
        }
    }, [run?.status, navigate, uuid]);

    useEffect(() => {
        if (run?.status !== 'queued' || resumed.current) return;
        const timer = window.setTimeout(async () => {
            resumed.current = true;
            await resume(uuid);
            refetch();
        }, 3500);
        return () => window.clearTimeout(timer);
    }, [run?.status, resume, refetch, uuid]);

    if (isLoading) return <AdminShell title="Generator soal"><PageLoader label="Membuka ruang generator…"/></AdminShell>;
    if (isError || !run) return <AdminShell title="Generator soal"><div className="p-8"><ErrorState title="Proses generator tidak ditemukan"/></div></AdminShell>;

    const failed = run.status === 'failed';
    return <AdminShell title="Generator soal" description={run.quiz.title}><div className="mx-auto max-w-4xl px-5 py-10 lg:px-8">
        <Link to="/kelola" className="inline-flex items-center gap-2 text-sm font-bold text-indigo-600"><ArrowLeft size={17}/>Kembali ke paket</Link>
        <section className="card relative mt-5 overflow-hidden p-7 sm:p-10">
            <div className="absolute inset-x-0 top-0 h-1 bg-slate-100"><div className={`h-full transition-all duration-500 ${failed ? 'bg-rose-500' : 'bg-indigo-600'}`} style={{ width: `${run.percentage}%` }}/></div>
            <div className="mx-auto max-w-2xl text-center"><span className={`mx-auto grid size-20 place-items-center rounded-[1.7rem] ${failed ? 'bg-rose-50 text-rose-600' : 'bg-indigo-50 text-indigo-600'}`}>{failed ? <AlertTriangle size={34}/> : run.status === 'completed' ? <CheckCircle2 size={34}/> : <Sparkles className="animate-pulse" size={34}/>}</span><p className="mt-6 text-xs font-black uppercase tracking-[.2em] text-indigo-600">{run.quiz.title}</p><h1 className="mt-2 text-3xl font-black tracking-tight">{failed ? 'Generator berhenti' : run.status_label}</h1><p className="mt-3 text-sm leading-6 text-slate-500">{failed ? 'Proses dapat dicoba lagi tanpa membuat paket baru.' : 'Sistem sedang membuat dan memvalidasi pola soal. Halaman akan berpindah otomatis setelah selesai.'}</p></div>
            <div className="mx-auto mt-9 max-w-2xl"><div className="flex items-end justify-between"><div><p className="text-xs font-bold text-slate-500">Soal tersimpan</p><p className="mt-1 text-4xl font-black">{run.generated_count}<span className="text-xl text-slate-400">/{run.requested_count}</span></p></div><p className={`text-2xl font-black ${failed ? 'text-rose-600' : 'text-indigo-600'}`}>{run.percentage}%</p></div><div className="mt-4 h-4 overflow-hidden rounded-full bg-slate-100"><div className={`h-full rounded-full transition-all duration-500 ${failed ? 'bg-rose-500' : 'bg-gradient-to-r from-indigo-600 to-sky-400'}`} style={{ width: `${run.percentage}%` }}/></div>
                <div className="mt-6 grid gap-3 sm:grid-cols-3"><ProgressDatum label="Jenis paket" value={run.quiz.type_label}/><ProgressDatum label="Soal angka" value={run.number_count}/><ProgressDatum label="Soal huruf" value={run.letter_count}/></div>
                {run.status === 'queued' && <div className="mt-6 flex items-center justify-center gap-2 rounded-2xl bg-amber-50 p-4 text-sm font-semibold text-amber-800"><Clock3 size={18}/>{resumeState.isLoading ? 'Menjalankan generator…' : 'Menyiapkan proses di latar belakang…'}</div>}
                {failed && <div className="mt-6 rounded-2xl bg-rose-50 p-4 text-sm text-rose-800"><p>{run.error_message || 'Terjadi kesalahan saat membuat soal.'}</p><button onClick={() => resume(uuid)} disabled={resumeState.isLoading} className="mt-4 inline-flex items-center gap-2 font-black text-rose-700"><RefreshCw size={17}/>{resumeState.isLoading ? 'Mencoba…' : 'Coba generate lagi'}</button></div>}
            </div>
        </section>
    </div></AdminShell>;
}

export function GenerationSuccessPage() {
    const { uuid } = useParams();
    const { data, isLoading, isError } = useGenerationQuery(uuid);
    const run = data?.data;

    if (isLoading) return <AdminShell title="Generator selesai"><PageLoader/></AdminShell>;
    if (isError || !run) return <AdminShell title="Generator selesai"><div className="p-8"><ErrorState/></div></AdminShell>;
    if (run.status !== 'completed') return <GenerationProgressPage/>;

    return <AdminShell title="Generator selesai" description={run.quiz.title}><div className="mx-auto max-w-4xl px-5 py-12 lg:px-8"><section className="card overflow-hidden"><div className="bg-gradient-to-br from-emerald-500 to-teal-600 px-7 py-10 text-center text-white sm:px-12"><span className="mx-auto grid size-20 place-items-center rounded-full bg-white/20 ring-8 ring-white/10"><CheckCircle2 size={42}/></span><p className="mt-6 text-xs font-black uppercase tracking-[.2em] text-emerald-100">Generator selesai</p><h1 className="mt-2 text-3xl font-black sm:text-4xl">{run.requested_count} soal siap digunakan</h1><p className="mx-auto mt-3 max-w-xl text-sm leading-6 text-emerald-50">Paket {run.quiz.title} sudah diperbarui dan seluruh soal berhasil divalidasi.</p></div><div className="p-7 sm:p-10"><div className="grid gap-4 sm:grid-cols-3"><ProgressDatum label="Total soal" value={run.requested_count}/><ProgressDatum label="Soal angka" value={run.number_count}/><ProgressDatum label="Soal huruf" value={run.letter_count}/></div><div className="mt-8 grid gap-3 sm:grid-cols-2"><a href={`/paket/${run.quiz.uuid}`} target="_blank" rel="noreferrer" className="btn-primary"><Play size={18}/>Coba paket sekarang</a><Link to="/kelola" className="btn-secondary">Kelola paket lain</Link><Link to={`/kelola?generate=${run.quiz.uuid}`} className="btn-secondary sm:col-span-2"><RefreshCw size={18}/>Generate ulang paket ini</Link></div></div></section></div></AdminShell>;
}

function ProgressDatum({ label, value }) {
    return <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800"><p className="text-xs font-bold text-slate-500">{label}</p><p className="mt-2 text-lg font-black">{value}</p></div>;
}
