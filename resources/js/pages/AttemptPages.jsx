import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { AlertTriangle, ArrowLeft, ArrowRight, Check, CheckCircle2, Clock3, Flag, Gauge, Hash, ListChecks, RotateCcw, Send, Trophy, Type, X, ZoomIn, ZoomOut } from 'lucide-react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { AppShell, ErrorState, formatDate, Header, PageLoader } from '../components/Layout';
import { useAttemptQuery, useFinishAttemptMutation, useReportQuestionMutation, useResultQuery, useSaveAnswersMutation, useStartAttemptMutation } from '../store/api';

const pendingKey = (uuid) => `lajunalar:pending:${uuid}`;

export function AttemptPage() {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const { data: response, isLoading, isError } = useAttemptQuery(uuid, { refetchOnMountOrArgChange: true });
    const [saveAnswers] = useSaveAnswersMutation();
    const [finishAttempt] = useFinishAttemptMutation();
    const [value, setValue] = useState('');
    const [position, setPosition] = useState(null);
    const [pending, setPending] = useState([]);
    const [seconds, setSeconds] = useState(0);
    const [validation, setValidation] = useState('');
    const [reportOpen, setReportOpen] = useState(false);
    const [syncing, setSyncing] = useState(false);
    const [isFinishing, setIsFinishing] = useState(false);
    const [finishError, setFinishError] = useState('');
    const questionZoom = useQuestionZoom();
    const shownAt = useRef(Date.now());
    const flushPromise = useRef(null);
    const finishing = useRef(false);
    const serverOffset = useRef(0);

    const attempt = response?.data;
    const questions = attempt?.questions ?? [];
    const current = position ? questions[position - 1] : null;

    useEffect(() => {
        if (!attempt) return;
        if (attempt.status !== 'in_progress') {
            navigate(`/attempt/${uuid}/hasil`, { replace: true });
            return;
        }
        const saved = JSON.parse(localStorage.getItem(pendingKey(uuid)) || '[]');
        const safePending = Array.isArray(saved) ? saved : [];
        setPending(safePending);
        const localNext = safePending.length ? Math.max(...safePending.map(item => item.position)) + 1 : 1;
        setPosition(Math.max(attempt.next_position, localNext));
        shownAt.current = Date.now();
    }, [attempt?.uuid]);

    useEffect(() => {
        if (!attempt) return;
        serverOffset.current = new Date(attempt.server_time).getTime() - Date.now();
        const tick = () => {
            const serverNow = Date.now() + serverOffset.current;
            setSeconds(Math.max(0, Math.ceil((new Date(attempt.expires_at).getTime() - serverNow) / 1000)));
        };
        tick();
        const timer = setInterval(tick, 500);
        return () => clearInterval(timer);
    }, [attempt?.expires_at, attempt?.server_time]);

    const flush = useCallback(async (items = pending) => {
        if (!items.length) return true;
        if (flushPromise.current) await flushPromise.current;

        const task = (async () => {
            setSyncing(true);
            try {
                let remaining = [...items].sort((a, b) => a.position - b.position);
                while (remaining.length) {
                    const chunk = remaining.slice(0, 25);
                    await saveAnswers({ attempt: uuid, answers: chunk }).unwrap();
                    const last = chunk.at(-1).position;
                    remaining = remaining.filter(item => item.position > last);
                    setPending(previous => {
                        const next = previous.filter(item => item.position > last);
                        localStorage.setItem(pendingKey(uuid), JSON.stringify(next));
                        return next;
                    });
                }
                return true;
            } catch {
                return false;
            } finally {
                setSyncing(false);
            }
        })();

        flushPromise.current = task;
        try {
            return await task;
        } finally {
            if (flushPromise.current === task) flushPromise.current = null;
        }
    }, [pending, saveAnswers, uuid]);

    const finish = useCallback(async (items = pending, expired = false) => {
        if (finishing.current) return false;
        finishing.current = true;
        setIsFinishing(true);
        setFinishError('');
        try {
            const synced = await flush(items);
            if (!synced && !expired) {
                setFinishError('Jawaban terakhir belum tersimpan. Periksa koneksi lalu coba akhiri lagi.');
                return false;
            }
            await finishAttempt(uuid).unwrap();
            localStorage.removeItem(pendingKey(uuid));
            navigate(`/attempt/${uuid}/hasil`, { replace: true });
            return true;
        } catch {
            setFinishError(expired
                ? 'Waktu selesai. Sistem sedang mencoba mengumpulkan jawaban dan hasil secara otomatis.'
                : 'Ujian belum berhasil diakhiri. Periksa koneksi lalu coba lagi.');
            return false;
        } finally {
            finishing.current = false;
            setIsFinishing(false);
        }
    }, [finishAttempt, flush, navigate, pending, uuid]);

    useEffect(() => {
        if (!attempt) return;
        const timer = setInterval(() => { if (pending.length) flush(); }, 4000);
        return () => clearInterval(timer);
    }, [attempt?.uuid, flush, pending.length]);

    useEffect(() => {
        if (!attempt || seconds > 0 || position === null) return;
        finish(pending, true);
        const retry = setInterval(() => finish(pending, true), 3000);
        return () => clearInterval(retry);
    }, [seconds, attempt?.uuid, position, finish]);

    useEffect(() => {
        const warn = (event) => { if (attempt?.status === 'in_progress') { event.preventDefault(); event.returnValue = ''; } };
        window.addEventListener('beforeunload', warn);
        return () => window.removeEventListener('beforeunload', warn);
    }, [attempt?.status]);

    if (isLoading || position === null) return <><Header minimal/><PageLoader/></>;
    if (isError || !attempt) return <><Header minimal/><div className="mx-auto max-w-5xl px-5 py-20"><ErrorState title="Pengerjaan tidak ditemukan"/></div></>;

    function normalize(raw) {
        return current?.type === 'number' ? raw.replace(/\D/g, '').slice(0, 1) : raw.replace(/[^a-zA-Z]/g, '').toUpperCase().slice(0, 1);
    }

    async function answer(event) {
        event?.preventDefault();
        if (!current || !value) {
            setValidation(current?.type === 'number' ? 'Masukkan satu angka 0–9.' : 'Masukkan satu huruf A–Z.');
            return;
        }
        const item = { position, question_uuid: current.uuid, answer: value, response_ms: Math.max(1, Date.now() - shownAt.current) };
        const nextQueue = [...pending.filter(row => row.position !== position), item];
        setPending(nextQueue);
        localStorage.setItem(pendingKey(uuid), JSON.stringify(nextQueue));
        setValue('');
        setValidation('');
        const nextPosition = position + 1;
        setPosition(nextPosition);
        shownAt.current = Date.now();
        if (nextPosition > questions.length) await finish(nextQueue);
        else if (nextQueue.length >= 10) flush(nextQueue);
    }

    return <div className="min-h-screen bg-slate-100 dark:bg-slate-950"><Header minimal/>
        <main className="mx-auto max-w-7xl px-4 py-6 lg:px-8">
            <div className="grid gap-5 lg:grid-cols-[1fr_310px]">
                <div className="space-y-5">
                    <section className="card flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between"><div><p className="eyebrow">Tahap 3 · Speed test</p><h1 className="mt-1 text-xl font-extrabold">{attempt.quiz.title}</h1><p className="mt-1 text-xs text-slate-500">Mulai {formatDate(attempt.started_at)}</p></div><SyncBadge syncing={syncing} count={pending.length}/></section>
                    <section className="card min-h-[520px] p-6 sm:p-9">
                        <div className="flex flex-col gap-3 border-b border-slate-100 pb-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between"><h2 className="text-2xl font-black">Soal nomor {Math.min(position, questions.length)}</h2><div className="flex items-center justify-between gap-3"><QuestionZoomControls {...questionZoom}/><span className="text-sm font-semibold text-slate-400">{Math.min(position, questions.length)} / {questions.length}</span></div></div>
                        {current ? <form onSubmit={answer} className="mx-auto max-w-2xl py-14 sm:py-20"><p className="text-sm font-semibold text-slate-500">{current.type === 'number' ? 'Temukan angka yang hilang' : 'Temukan huruf yang muncul dua kali'}</p><p style={questionTextStyle(questionZoom.value, 0.22)} className="mt-5 break-words font-mono font-black transition-[font-size,letter-spacing,word-spacing]">{current.question_text}</p><label className="mt-10 block text-sm font-bold">Jawaban kamu</label><div className={`mt-2 flex rounded-2xl border-2 bg-white p-1 transition dark:bg-slate-900 ${validation ? 'border-rose-300' : 'border-slate-200 focus-within:border-indigo-500 dark:border-slate-700'}`}><input autoFocus value={value} onChange={event => setValue(normalize(event.target.value))} inputMode={current.type === 'number' ? 'numeric' : 'text'} maxLength={1} autoComplete="off" className="h-14 min-w-0 flex-1 bg-transparent px-4 text-2xl font-bold outline-none" placeholder={current.type === 'number' ? '0–9' : 'A–Z'}/><button type="submit" className="grid size-14 place-items-center rounded-xl bg-indigo-600 text-white hover:bg-indigo-700" aria-label="Jawab dan lanjut"><ArrowRight/></button></div><div className="mt-3 flex items-start justify-between gap-4"><p className={`text-xs ${validation ? 'text-rose-600' : 'text-slate-400'}`}>{validation || 'Tekan Enter atau klik panah untuk menuju soal selanjutnya.'}</p><button type="button" onClick={() => setReportOpen(true)} className="flex shrink-0 items-center gap-1 text-xs font-bold text-rose-500"><Flag size={14}/>Laporkan</button></div></form> : <div className="py-24 text-center"><PageLoader label="Mengumpulkan jawaban…"/></div>}
                    </section>
                </div>
                <aside className="space-y-5 lg:sticky lg:top-24 lg:self-start"><Timer seconds={seconds}/><QuestionGrid total={questions.length} current={position}/>{finishError && <p className="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold leading-6 text-rose-700 dark:border-rose-900 dark:bg-rose-950/30 dark:text-rose-300">{finishError}</p>}<button disabled={isFinishing} onClick={() => { if (confirm('Akhiri ujian sekarang? Jawaban yang belum dikerjakan akan dianggap kosong.')) finish(pending, false); }} className="flex w-full items-center justify-center gap-2 rounded-2xl bg-orange-100 px-5 py-4 font-bold text-orange-700 disabled:opacity-60 dark:bg-orange-950/40 dark:text-orange-300"><Send size={18}/>{isFinishing ? 'Mengakhiri ujian…' : finishError ? 'Coba akhiri lagi' : 'Akhiri ujian'}</button></aside>
            </div>
        </main>
        {reportOpen && current && <ReportModal question={current.uuid} attempt={uuid} onClose={() => setReportOpen(false)}/>} 
    </div>;
}

export function ResultPage() {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const { data: response, isLoading, isError, error, refetch } = useResultQuery(uuid, { refetchOnMountOrArgChange: true });
    const [startAttempt, startState] = useStartAttemptMutation();
    const [selected, setSelected] = useState(1);
    const questionZoom = useQuestionZoom();
    useEffect(() => {
        if (error?.status !== 409) return;
        const timer = setInterval(refetch, 1000);
        return () => clearInterval(timer);
    }, [error?.status, refetch]);
    if (isLoading) return <AppShell footer={false}><PageLoader label="Menghitung hasil…"/></AppShell>;
    if (error?.status === 409) return <AppShell footer={false}><PageLoader label="Menyelesaikan dan menghitung hasil…"/></AppShell>;
    if (isError) return <AppShell><div className="mx-auto max-w-6xl px-5 py-20"><ErrorState title="Hasil belum tersedia" message="Pengerjaan mungkin masih berlangsung."/></div></AppShell>;
    const result = response.data;
    const answers = result.answers ?? [];
    const answer = answers.find(item => item.position === selected) ?? answers[selected - 1];
    const pageStart = Math.floor((selected - 1) / 25) * 25;
    const visible = answers.slice(pageStart, pageStart + 25);
    async function repeat() {
        const next = await startAttempt(result.quiz.uuid).unwrap();
        navigate(`/attempt/${next.data.uuid}`);
    }
    return <AppShell><div className="mx-auto max-w-7xl px-5 py-10 lg:px-8">
        <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><p className="eyebrow">Hasil pengerjaan</p><h1 className="mt-2 text-3xl font-black tracking-tight">{result.quiz.title}</h1><p className="mt-1 text-sm text-slate-500">Selesai {formatDate(result.finished_at)}</p></div><div className="flex flex-wrap gap-2"><Link to={`/paket/${result.quiz.uuid}`} className="btn-secondary"><ArrowLeft size={18}/>Kembali</Link><button onClick={repeat} disabled={startState.isLoading} className="btn-primary"><RotateCcw size={18}/>{startState.isLoading ? 'Menyiapkan…' : 'Kerjakan lagi'}</button></div></div>
        {result.is_personal_best && <div className="mt-6 flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200"><Trophy className="shrink-0 text-amber-500"/><div><p className="font-black">Rekor pribadi</p><p className="text-sm">Ini adalah skor terbaikmu pada paket ini: {result.personal_best_score}.</p></div></div>}
        <section className="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><ResultStat icon={Trophy} label="Skor" value={`${result.score}/${result.quiz.question_count}`} tone="indigo"/><ResultStat icon={Gauge} label="Akurasi" value={`${result.accuracy}%`} tone="sky"/><ResultStat icon={CheckCircle2} label="Benar" value={result.correct_count} tone="emerald"/><ResultStat icon={Clock3} label="Respons rata-rata" value={`${(result.average_response_ms/1000).toFixed(2)} dtk`} tone="amber"/></section>
        <section className="mt-4 grid gap-4 md:grid-cols-2"><TypeResult icon={Hash} title="Angka hilang" stats={result.performance_by_type.number}/><TypeResult icon={Type} title="Huruf ganda" stats={result.performance_by_type.letter}/></section>
        <div className="mt-7 grid gap-6 lg:grid-cols-[1fr_330px]">
            <section className="card overflow-hidden"><div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-6 dark:border-slate-800"><h2 className="text-xl font-extrabold">Pembahasan soal {selected}</h2><QuestionZoomControls {...questionZoom}/></div>{answer ? <Review answer={answer} zoom={questionZoom.value}/> : <div className="p-8 text-slate-500">Data soal tidak tersedia.</div>}<div className="flex items-center justify-between border-t border-slate-100 p-4 dark:border-slate-800"><button disabled={selected <= 1} onClick={() => setSelected(selected-1)} className="btn-secondary disabled:opacity-40"><ArrowLeft size={17}/>Sebelumnya</button><span className="text-sm font-semibold text-slate-500">{selected} dari {answers.length}</span><button disabled={selected >= answers.length} onClick={() => setSelected(selected+1)} className="btn-primary">Selanjutnya<ArrowRight size={17}/></button></div></section>
            <aside className="card p-5 lg:sticky lg:top-24 lg:self-start"><div className="flex items-center justify-between"><h2 className="font-extrabold">Daftar soal</h2><span className="text-xs text-slate-400">{pageStart+1}–{Math.min(pageStart+25, answers.length)}</span></div><div className="mt-4 grid grid-cols-5 gap-2">{visible.map(item => <button key={item.position} onClick={() => setSelected(item.position)} className={`aspect-square rounded-xl text-sm font-bold text-white transition ${selected === item.position ? 'ring-2 ring-indigo-600 ring-offset-2' : ''} ${item.answer_text === null ? 'bg-slate-400' : item.is_correct ? 'bg-emerald-500' : 'bg-rose-500'}`}>{item.position}</button>)}</div><div className="mt-5 flex justify-between gap-2"><button disabled={pageStart === 0} onClick={() => setSelected(Math.max(1,pageStart-24))} className="btn-secondary flex-1 px-2 py-2 text-xs">25 sebelumnya</button><button disabled={pageStart+25 >= answers.length} onClick={() => setSelected(pageStart+26)} className="btn-secondary flex-1 px-2 py-2 text-xs">25 berikutnya</button></div><div className="mt-5 space-y-2 text-xs text-slate-500"><Legend color="bg-emerald-500" label="Benar"/><Legend color="bg-rose-500" label="Salah"/><Legend color="bg-slate-400" label="Tidak dijawab"/></div></aside>
        </div>
    </div></AppShell>;
}

function Timer({seconds}) { const minutes = String(Math.floor(seconds/60)).padStart(2,'0'); const secs = String(seconds%60).padStart(2,'0'); return <div className={`rounded-3xl p-5 text-white shadow-lg ${seconds < 30 ? 'bg-rose-600' : 'bg-slate-950 dark:bg-indigo-950'}`}><p className="flex items-center gap-2 text-xs font-bold uppercase tracking-[.15em] text-white/70"><Clock3 size={15}/>Sisa waktu</p><p className="mt-2 font-mono text-4xl font-black tracking-wider">{minutes}:{secs}</p></div>; }
function SyncBadge({syncing,count}) { return <div className="flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-500 dark:bg-slate-800">{syncing ? <span className="size-2 animate-pulse rounded-full bg-amber-500"/> : <span className="size-2 rounded-full bg-emerald-500"/>}{syncing ? 'Menyimpan…' : count ? `${count} menunggu sinkronisasi` : 'Tersimpan'}</div>; }
function QuestionGrid({total,current}) {
    const pageSize = 25;
    const pageCount = Math.max(1, Math.ceil(total / pageSize));
    const activePage = Math.min(pageCount - 1, Math.floor((Math.max(1, Math.min(current, total)) - 1) / pageSize));
    const [page, setPage] = useState(activePage);
    useEffect(() => setPage(activePage), [activePage, current]);
    const start = page * pageSize;
    const end = Math.min(total, start + pageSize);

    return <div className="card p-5">
        <div className="flex items-start justify-between gap-3"><div><h2 className="font-extrabold">Daftar soal</h2><p className="mt-1 text-xs text-slate-500">Nomor hanya sebagai indikator.</p></div><span className="rounded-lg bg-slate-100 px-2 py-1 text-xs font-bold text-slate-500 dark:bg-slate-800">{start + 1}–{end}</span></div>
        <div className="mt-4 grid grid-cols-5 gap-2">{Array.from({length: end-start},(_,i)=>start+i+1).map(number => <span key={number} className={`grid aspect-square place-items-center rounded-lg text-xs font-bold ${number < current ? 'bg-emerald-500 text-white' : number === current ? 'bg-indigo-600 text-white ring-2 ring-indigo-200' : 'border border-slate-200 text-slate-400 dark:border-slate-700'}`}>{number}</span>)}</div>
        <div className="mt-4 flex items-center justify-between gap-2"><button type="button" disabled={page === 0} onClick={() => setPage(value => Math.max(0, value-1))} aria-label="Halaman soal sebelumnya" className="grid size-9 place-items-center rounded-xl border border-slate-200 text-slate-600 disabled:opacity-35 dark:border-slate-700 dark:text-slate-300"><ArrowLeft size={16}/></button><span className="text-xs font-bold text-slate-500">Halaman {page+1} dari {pageCount}</span><button type="button" disabled={page >= pageCount-1} onClick={() => setPage(value => Math.min(pageCount-1, value+1))} aria-label="Halaman soal berikutnya" className="grid size-9 place-items-center rounded-xl border border-slate-200 text-slate-600 disabled:opacity-35 dark:border-slate-700 dark:text-slate-300"><ArrowRight size={16}/></button></div>
        <div className="mt-4 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div className="h-full rounded-full bg-indigo-600 transition-all" style={{width:`${Math.min(100,((current-1)/total)*100)}%`}}/></div><p className="mt-2 text-right text-xs font-semibold text-slate-400">{Math.max(0,current-1)} / {total} dijawab</p>
    </div>;
}
function ReportModal({question,attempt,onClose}) { const [reason,setReason]=useState(''); const [report,state]=useReportQuestionMutation(); async function submit(e){e.preventDefault();await report({question,attempt_uuid:attempt,reason}).unwrap();onClose();} return <div className="fixed inset-0 z-50 grid place-items-center bg-slate-950/65 p-4 backdrop-blur-sm"><form onSubmit={submit} className="card w-full max-w-lg p-6"><div className="flex items-center justify-between"><h2 className="text-xl font-black">Laporkan soal</h2><button type="button" onClick={onClose} className="grid size-9 place-items-center rounded-lg bg-slate-100 dark:bg-slate-800"><X size={18}/></button></div><p className="mt-2 text-sm text-slate-500">Jelaskan bagian yang menurutmu perlu diperiksa admin.</p><textarea value={reason} onChange={e=>setReason(e.target.value)} required minLength={10} maxLength={1000} rows={5} className="mt-5 w-full rounded-2xl border border-slate-200 bg-transparent p-4 outline-none focus:border-indigo-500 dark:border-slate-700" placeholder="Contoh: pola angka tampaknya memiliki lebih dari satu jawaban…"/><div className="mt-5 flex justify-end gap-2"><button type="button" onClick={onClose} className="btn-secondary">Batal</button><button disabled={state.isLoading} className="btn-primary"><Flag size={17}/>Kirim laporan</button></div></form></div>; }
function ResultStat({icon:Icon,label,value,tone}) { const colors={indigo:'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50',sky:'bg-sky-50 text-sky-600 dark:bg-sky-950/50',emerald:'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50',amber:'bg-amber-50 text-amber-600 dark:bg-amber-950/50'}; return <div className="card flex items-center gap-4 p-5"><span className={`grid size-11 place-items-center rounded-xl ${colors[tone]}`}><Icon size={20}/></span><div><p className="text-xl font-black">{value}</p><p className="text-xs text-slate-500">{label}</p></div></div>; }
function TypeResult({icon:Icon,title,stats}) { return <div className="card flex items-center gap-4 p-5"><span className="grid size-12 shrink-0 place-items-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40"><Icon size={22}/></span><div className="min-w-0 flex-1"><div className="flex items-center justify-between"><p className="font-black">{title}</p><span className="font-black text-indigo-600">{stats.accuracy}%</span></div><p className="mt-1 text-xs text-slate-500">{stats.correct} benar · {stats.wrong} salah · {(stats.average_response_ms/1000).toFixed(2)} dtk/soal</p></div></div>; }
function Review({answer,zoom}) { const unanswered=answer.answer_text===null; return <div className="p-6 sm:p-8"><p className="text-sm font-semibold text-slate-500">{answer.question_type==='number'?'Temukan angka yang hilang':'Temukan huruf yang muncul dua kali'}</p><p style={questionTextStyle(zoom, 0.18)} className="mt-4 break-words font-mono font-black transition-[font-size,letter-spacing,word-spacing]">{answer.question_text}</p><div className={`mt-7 rounded-2xl border p-5 ${unanswered?'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50':answer.is_correct?'border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/30':'border-rose-200 bg-rose-50 dark:border-rose-900 dark:bg-rose-950/30'}`}><p className="flex items-center gap-2 font-extrabold">{unanswered?<AlertTriangle className="text-slate-500"/>:answer.is_correct?<Check className="text-emerald-600"/>:<X className="text-rose-600"/>}{unanswered?'Tidak dijawab':answer.is_correct?'Jawaban benar':'Jawaban salah'}</p><div className="mt-4 grid gap-3 sm:grid-cols-2"><div><p className="text-xs text-slate-500">Jawaban kamu</p><p className="mt-1 text-xl font-black">{answer.answer_text ?? '—'}</p></div><div><p className="text-xs text-slate-500">Jawaban yang benar</p><p className="mt-1 text-xl font-black text-emerald-600">{answer.correct_answer}</p></div></div>{answer.explanation && <p className="mt-4 border-t border-current/10 pt-4 text-sm leading-6">{answer.explanation}</p>}<p className="mt-4 flex items-center gap-2 text-xs font-semibold text-slate-500"><Clock3 size={14}/>{answer.response_ms ? `${(answer.response_ms/1000).toFixed(2)} detik` : 'Tidak dibuka'}</p></div></div>; }
function Legend({color,label}) { return <div className="flex items-center gap-2"><span className={`size-3 rounded-full ${color}`}/>{label}</div>; }

const questionTextStyle = (zoom, defaultTracking) => ({
    fontSize: `clamp(${1.875 * zoom}rem, ${4 * zoom}vw, ${3 * zoom}rem)`,
    letterSpacing: `${zoom <= 0.5 ? 0.07 : zoom <= 0.75 ? 0.12 : defaultTracking}em`,
    wordSpacing: zoom <= 0.5 ? '-0.08em' : zoom <= 0.75 ? '-0.04em' : 'normal',
});

function useQuestionZoom() {
    const [value, setValue] = useState(() => {
        const saved = Number(localStorage.getItem('lajunalar:question-zoom')) || 1;
        return Math.max(0.5, Math.min(1.5, saved));
    });
    const update = useCallback((next) => {
        setValue(current => {
            const value = Math.max(0.5, Math.min(1.5, typeof next === 'function' ? next(current) : next));
            localStorage.setItem('lajunalar:question-zoom', String(value));
            return value;
        });
    }, []);

    return {
        value,
        decrease: () => update(current => current - 0.25),
        increase: () => update(current => current + 0.25),
        reset: () => update(1),
    };
}

function QuestionZoomControls({value,decrease,increase,reset}) {
    return <div className="inline-flex items-center rounded-xl border border-slate-200 bg-white p-1 dark:border-slate-700 dark:bg-slate-900" aria-label="Ukuran teks soal"><button type="button" disabled={value <= 0.5} onClick={decrease} aria-label="Perkecil teks soal" title="Perkecil" className="grid size-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 disabled:opacity-35 dark:hover:bg-slate-800"><ZoomOut size={16}/></button><button type="button" onClick={reset} title="Kembalikan ukuran" className="min-w-12 px-1 text-xs font-black text-slate-500">{Math.round(value*100)}%</button><button type="button" disabled={value >= 1.5} onClick={increase} aria-label="Perbesar teks soal" title="Perbesar" className="grid size-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 disabled:opacity-35 dark:hover:bg-slate-800"><ZoomIn size={16}/></button></div>;
}
