import { AlertTriangle, ArrowRight, Check, CheckCircle2, ExternalLink, FilePlus2, Hash, Layers3, LoaderCircle, Pencil, Plus, Sparkles, Trash2, Type, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { AdminShell } from '../components/AdminLayout';
import { GenerationModal, QuizModal } from '../components/AdminModals';
import { ErrorState, PageLoader, typeTone } from '../components/Layout';
import { useAdminQuizzesQuery, useDeleteAdminQuizMutation, useGenerationRunsQuery } from '../store/api';

const statusTone = {
    draft: 'bg-slate-100 text-slate-600',
    published: 'bg-emerald-50 text-emerald-700',
    closed: 'bg-rose-50 text-rose-700',
    queued: 'bg-slate-100 text-slate-600',
    generating: 'bg-amber-50 text-amber-700',
    validating: 'bg-sky-50 text-sky-700',
    completed: 'bg-emerald-50 text-emerald-700',
    failed: 'bg-rose-50 text-rose-700',
};

export function AdminPackagesPage() {
    const { data, isLoading, isError } = useAdminQuizzesQuery();
    const { data: runs } = useGenerationRunsQuery(undefined, { pollingInterval: 5000 });
    const [editing, setEditing] = useState(null);
    const [generating, setGenerating] = useState(null);
    const [searchParams, setSearchParams] = useSearchParams();

    useEffect(() => {
        const requested = searchParams.get('generate');
        const quiz = data?.data?.find(item => item.uuid === requested);
        if (quiz) {
            setGenerating(quiz);
            const next = new URLSearchParams(searchParams);
            next.delete('generate');
            setSearchParams(next, { replace: true });
        }
    }, [data, searchParams, setSearchParams]);

    if (isLoading) return <AdminShell><PageLoader label="Memuat paket…"/></AdminShell>;
    if (isError) return <AdminShell><div className="p-8"><ErrorState/></div></AdminShell>;

    const quizzes = data?.data ?? [];
    const activeRuns = (runs?.data ?? []).filter(run => ['queued', 'generating', 'validating'].includes(run.status));

    return <AdminShell><div className="mx-auto max-w-7xl px-5 py-8 lg:px-8 lg:py-10">
        <section className="relative overflow-hidden rounded-[2rem] bg-slate-950 p-7 text-white shadow-xl sm:p-9">
            <div className="absolute -right-14 -top-16 size-64 rounded-full bg-indigo-600/40 blur-3xl"/>
            <div className="relative flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <div><p className="text-xs font-black uppercase tracking-[.2em] text-indigo-300">LajuNalar Studio</p><h1 className="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Paket & generator soal</h1><p className="mt-3 max-w-2xl text-sm leading-6 text-slate-300">Atur paket latihan, lalu buat hingga 500 soal angka, huruf, atau campuran hanya dalam beberapa detik.</p></div>
                <button onClick={() => setEditing({})} className="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 font-bold text-slate-950 transition hover:bg-indigo-50"><Plus size={19}/>Paket baru</button>
            </div>
        </section>

        <section className="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <AdminStat icon={Layers3} label="Total paket" value={data.meta.total} tone="indigo"/>
            <AdminStat icon={CheckCircle2} label="Dipublikasikan" value={data.meta.published} tone="emerald"/>
            <AdminStat icon={Hash} label="Soal tersimpan" value={quizzes.reduce((sum, quiz) => sum + quiz.available_questions, 0).toLocaleString('id-ID')} tone="sky"/>
            <AdminStat icon={LoaderCircle} label="Generator aktif" value={activeRuns.length} tone="amber" spinning={activeRuns.length > 0}/>
        </section>

        <div className="mt-9 grid gap-8 xl:grid-cols-[1fr_350px]">
            <section>
                <div className="flex items-end justify-between"><div><p className="eyebrow">Daftar paket</p><h2 className="mt-1 text-2xl font-black">Siap dikelola</h2></div><span className="text-sm text-slate-500">{quizzes.length} paket</span></div>
                <div className="mt-5 space-y-4">{quizzes.length ? quizzes.map(quiz => <PackageRow key={quiz.uuid} quiz={quiz} onEdit={() => setEditing(quiz)} onGenerate={() => setGenerating(quiz)}/>) : <EmptyPackages onCreate={() => setEditing({})}/>}</div>
            </section>
            <GenerationHistory runs={runs?.data ?? []}/>
        </div>
    </div>
    {editing && <QuizModal quiz={editing.uuid ? editing : null} onClose={() => setEditing(null)}/>} 
    {generating && <GenerationModal quiz={generating} onClose={() => setGenerating(null)}/>} 
    </AdminShell>;
}

function PackageRow({ quiz, onEdit, onGenerate }) {
    const [remove, removeState] = useDeleteAdminQuizMutation();
    const activeRun = quiz.generation_active ? quiz.latest_generation : null;

    async function deleteQuiz() {
        if (window.confirm(`Hapus paket “${quiz.title}”? Riwayat pengerjaan lama tetap tersimpan.`)) await remove(quiz.uuid);
    }

    return <article className="card p-5 sm:p-6">
        <div className="flex flex-col gap-5 lg:flex-row lg:items-center">
            <div className={`grid size-13 shrink-0 place-items-center rounded-2xl ${quiz.type === 'number' ? 'bg-cyan-50 text-cyan-600' : quiz.type === 'letter' ? 'bg-violet-50 text-violet-600' : 'bg-amber-50 text-amber-600'}`}>{quiz.type === 'number' ? <Hash size={24}/> : quiz.type === 'letter' ? <Type size={24}/> : <Sparkles size={24}/>}</div>
            <div className="min-w-0 flex-1"><div className="flex flex-wrap items-center gap-2"><h3 className="truncate text-lg font-black">{quiz.title}</h3><span className={`rounded-full px-2.5 py-1 text-[11px] font-black ${statusTone[quiz.status]}`}>{quiz.status_label}</span><span className={`rounded-full px-2.5 py-1 text-[11px] font-black ${typeTone[quiz.type]}`}>{quiz.type_label}</span></div><p className="mt-1 line-clamp-1 text-sm text-slate-500">{quiz.description || 'Belum ada deskripsi paket.'}</p><div className="mt-3 flex flex-wrap gap-4 text-xs font-bold text-slate-500"><span>{quiz.available_questions}/{quiz.question_count} soal siap</span><span>{quiz.duration_minutes} menit</span>{activeRun && <Link to={`/kelola/generator/${activeRun.uuid}`} className="text-amber-600">Generator {activeRun.generated_count}/{activeRun.requested_count} →</Link>}</div></div>
            <div className="flex flex-wrap items-center gap-2 lg:justify-end"><button onClick={onGenerate} disabled={Boolean(activeRun)} className="btn-primary px-4 py-2.5 text-sm"><Sparkles size={17}/>{activeRun ? 'Sedang diproses' : 'Generate soal'}</button><button onClick={onEdit} className="btn-secondary px-3 py-2.5" title="Edit paket"><Pencil size={17}/></button><a href={`/paket/${quiz.uuid}`} target="_blank" rel="noreferrer" className="btn-secondary px-3 py-2.5" title="Lihat paket"><ExternalLink size={17}/></a><button onClick={deleteQuiz} disabled={removeState.isLoading} className="grid size-10 place-items-center rounded-xl border border-rose-100 text-rose-500 transition hover:bg-rose-50" title="Hapus paket"><Trash2 size={17}/></button></div>
        </div>
        {quiz.available_questions !== quiz.question_count && !activeRun && <div className="mt-4 flex items-center gap-2 rounded-xl bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800"><AlertTriangle size={16}/>Jumlah soal siap belum sesuai target. Jalankan generator agar paket dapat dikerjakan.</div>}
    </article>;
}

function GenerationHistory({ runs }) {
    return <aside><p className="eyebrow">Aktivitas terakhir</p><h2 className="mt-1 text-2xl font-black">Riwayat generator</h2><div className="card mt-5 overflow-hidden"><div className="divide-y divide-slate-100 dark:divide-slate-800">{runs.length ? runs.slice(0, 8).map(run => <Link key={run.uuid} to={run.status === 'completed' ? `/kelola/generator/${run.uuid}/sukses` : `/kelola/generator/${run.uuid}`} className="flex items-center gap-3 p-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/50"><span className={`grid size-10 shrink-0 place-items-center rounded-xl ${statusTone[run.status]}`}>{run.status === 'completed' ? <Check size={18}/> : run.status === 'failed' ? <X size={18}/> : <LoaderCircle className={run.status !== 'queued' ? 'animate-spin' : ''} size={18}/>}</span><div className="min-w-0 flex-1"><p className="truncate text-sm font-bold">{run.quiz.title}</p><p className="text-xs text-slate-500">{run.status_label} · {run.generated_count}/{run.requested_count}</p></div><ArrowRight size={16} className="text-slate-300"/></Link>) : <p className="p-8 text-center text-sm text-slate-500">Belum ada proses generator.</p>}</div></div></aside>;
}

function AdminStat({ icon: Icon, label, value, tone, spinning }) {
    const colors = { indigo: 'bg-indigo-50 text-indigo-600', emerald: 'bg-emerald-50 text-emerald-600', sky: 'bg-sky-50 text-sky-600', amber: 'bg-amber-50 text-amber-600' };
    return <div className="card flex items-center gap-4 p-5"><span className={`grid size-12 place-items-center rounded-2xl ${colors[tone]}`}><Icon className={spinning ? 'animate-spin' : ''} size={22}/></span><div><p className="text-2xl font-black">{value}</p><p className="text-sm text-slate-500">{label}</p></div></div>;
}

function EmptyPackages({ onCreate }) {
    return <div className="card p-10 text-center"><FilePlus2 size={42} className="mx-auto text-slate-300"/><h3 className="mt-4 text-xl font-black">Belum ada paket</h3><p className="mt-2 text-sm text-slate-500">Buat paket pertama lalu isi otomatis dengan generator.</p><button onClick={onCreate} className="btn-primary mt-5"><Plus size={18}/>Buat paket</button></div>;
}
