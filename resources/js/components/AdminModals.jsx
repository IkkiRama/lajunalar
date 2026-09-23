import { AlertTriangle, Rocket, Save, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    useCreateAdminQuizMutation,
    useStartGenerationMutation,
    useUpdateAdminQuizMutation,
} from '../store/api';

export function QuizModal({ quiz, onClose }) {
    const initial = useMemo(() => ({
        title: quiz?.title ?? '',
        description: quiz?.description ?? '',
        type: quiz?.type ?? 'mixed',
        status: quiz?.status ?? 'published',
        question_count: quiz?.question_count ?? 500,
        bank_size: quiz?.bank_size ?? 1500,
        number_percentage: quiz?.number_percentage ?? 50,
        duration_minutes: quiz?.duration_minutes ?? 5,
        passing_score: quiz?.passing_score ?? '',
    }), [quiz]);
    const [form, setForm] = useState(initial);
    const [create, createState] = useCreateAdminQuizMutation();
    const [update, updateState] = useUpdateAdminQuizMutation();
    const busy = createState.isLoading || updateState.isLoading;
    const error = createState.error || updateState.error;

    function change(event) {
        setForm(current => ({ ...current, [event.target.name]: event.target.value }));
    }

    async function submit(event) {
        event.preventDefault();
        const payload = {
            ...form,
            question_count: Number(form.question_count),
            bank_size: Number(form.bank_size),
            number_percentage: Number(form.number_percentage),
            duration_minutes: Number(form.duration_minutes),
            passing_score: form.passing_score === '' ? null : Number(form.passing_score),
            starts_at: quiz?.starts_at ?? null,
            ends_at: quiz?.ends_at ?? null,
        };

        if (quiz) await update({ uuid: quiz.uuid, ...payload }).unwrap();
        else await create(payload).unwrap();
        onClose();
    }

    return <Modal title={quiz ? 'Edit paket' : 'Buat paket baru'} subtitle="Tentukan jenis latihan, jumlah soal, dan durasinya." onClose={onClose}>
        <form onSubmit={submit} className="space-y-5">
            <Field label="Nama paket"><input autoFocus required name="title" value={form.title} onChange={change} className="admin-input" placeholder="Contoh: Booster Speed Test 1"/></Field>
            <Field label="Deskripsi"><textarea name="description" value={form.description} onChange={change} rows="3" className="admin-input resize-none" placeholder="Jelaskan isi dan tujuan paket…"/></Field>
            <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Jenis paket"><select name="type" value={form.type} onChange={event => setForm(current => ({ ...current, type: event.target.value, number_percentage: event.target.value === 'number' ? 100 : event.target.value === 'letter' ? 0 : current.number_percentage || 50 }))} className="admin-input"><option value="mixed">Campuran angka & huruf</option><option value="number">Angka hilang</option><option value="letter">Huruf ganda</option></select></Field>
                <Field label="Status"><select name="status" value={form.status} onChange={change} className="admin-input"><option value="published">Dipublikasikan</option><option value="draft">Draft</option><option value="closed">Ditutup</option></select></Field>
            </div>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Field label="Soal per attempt"><input required min="1" max="2000" type="number" name="question_count" value={form.question_count} onChange={change} className="admin-input"/></Field>
                <Field label="Target bank soal"><input required min={form.question_count || 1} max="5000" type="number" name="bank_size" value={form.bank_size} onChange={change} className="admin-input"/></Field>
                <Field label="Durasi (menit)"><input required min="1" max="180" step="0.5" type="number" name="duration_minutes" value={form.duration_minutes} onChange={change} className="admin-input"/></Field>
                <Field label="Nilai lulus"><input min="0" max="100" type="number" name="passing_score" value={form.passing_score} onChange={change} className="admin-input" placeholder="Opsional"/></Field>
            </div>
            {form.type === 'mixed' && <Field label={`Komposisi attempt: ${form.number_percentage}% angka · ${100 - Number(form.number_percentage || 0)}% huruf`}><input type="range" min="0" max="100" step="5" name="number_percentage" value={form.number_percentage} onChange={change} className="w-full accent-indigo-600"/></Field>}
            <ApiError error={error}/>
            <div className="flex justify-end gap-3 border-t border-slate-100 pt-5"><button type="button" onClick={onClose} className="btn-secondary">Batal</button><button disabled={busy} className="btn-primary"><Save size={18}/>{busy ? 'Menyimpan…' : 'Simpan paket'}</button></div>
        </form>
    </Modal>;
}

export function GenerationModal({ quiz, onClose }) {
    const navigate = useNavigate();
    const [count, setCount] = useState(quiz.bank_size || Math.max(quiz.question_count || 500, 1500));
    const [numberCount, setNumberCount] = useState(Math.round((quiz.bank_size || 1500) * (quiz.number_percentage ?? 50) / 100));
    const [start, startState] = useStartGenerationMutation();

    useEffect(() => {
        if (quiz.type !== 'mixed') setNumberCount(quiz.type === 'number' ? Number(count) : 0);
    }, [count, quiz.type]);

    async function submit(event) {
        event.preventDefault();
        const response = await start({
            quiz: quiz.uuid,
            count: Number(count),
            number_count: quiz.type === 'mixed' ? Number(numberCount) : null,
        }).unwrap();
        navigate(`/kelola/generator/${response.data.uuid}`);
    }

    const total = Number(count || 0);
    const numbers = quiz.type === 'letter' ? 0 : quiz.type === 'number' ? total : Number(numberCount || 0);
    const letters = total - numbers;

    return <Modal title="Generate soal" subtitle={quiz.title} onClose={onClose}>
        <form onSubmit={submit} className="space-y-5">
            <div className="rounded-2xl bg-indigo-50 p-4 text-sm leading-6 text-indigo-900"><strong>Aman untuk riwayat:</strong> soal aktif lama akan diarsipkan, bukan ditimpa. Nilai, pembahasan, dan attempt yang sedang berjalan tetap memakai snapshot soal sebelumnya.</div>
            <Field label="Ukuran bank soal"><input autoFocus required min={quiz.question_count} max="5000" type="number" value={count} onChange={event => { const value = event.target.value; setCount(value); if (quiz.type === 'mixed') setNumberCount(Math.round(Number(value || 0) * (quiz.number_percentage ?? 50) / 100)); }} className="admin-input text-lg font-black"/></Field>
            <p className="-mt-3 text-xs leading-5 text-slate-500">Setiap attempt mengambil {quiz.question_count} soal dari bank ini dan memprioritaskan soal yang paling jarang dilihat user.</p>
            {quiz.type === 'mixed' && <div className="grid gap-4 sm:grid-cols-2"><Field label="Soal angka"><input required min="0" max={count} type="number" value={numberCount} onChange={event => setNumberCount(event.target.value)} className="admin-input"/></Field><Field label="Soal huruf"><div className="admin-input bg-slate-50 font-bold text-slate-500">{Math.max(0, letters)}</div></Field></div>}
            <div className="grid grid-cols-3 gap-3 rounded-2xl border border-slate-200 p-4 text-center"><div><p className="text-xl font-black">{total}</p><p className="text-xs text-slate-500">Total</p></div><div><p className="text-xl font-black text-cyan-600">{numbers}</p><p className="text-xs text-slate-500">Angka</p></div><div><p className="text-xl font-black text-violet-600">{Math.max(0, letters)}</p><p className="text-xs text-slate-500">Huruf</p></div></div>
            <ApiError error={startState.error}/>
            <div className="flex justify-end gap-3 border-t border-slate-100 pt-5"><button type="button" onClick={onClose} className="btn-secondary">Batal</button><button disabled={startState.isLoading} className="btn-primary"><Rocket size={18}/>{startState.isLoading ? 'Menyiapkan…' : `Generate ${total} soal`}</button></div>
        </form>
    </Modal>;
}

function Field({ label, children }) {
    return <label className="block"><span className="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{label}</span>{children}</label>;
}

function ApiError({ error }) {
    if (!error) return null;
    const errors = error?.data?.errors;
    const message = errors ? Object.values(errors).flat()[0] : error?.data?.message || 'Permintaan belum berhasil. Coba periksa kembali datanya.';
    return <div className="flex items-start gap-2 rounded-xl bg-rose-50 p-3 text-sm font-semibold text-rose-700"><AlertTriangle className="mt-0.5 shrink-0" size={17}/>{message}</div>;
}

function Modal({ title, subtitle, onClose, children }) {
    return <div className="fixed inset-0 z-[70] grid place-items-center overflow-y-auto bg-slate-950/55 p-4 backdrop-blur-sm"><button aria-label="Tutup" onClick={onClose} className="absolute inset-0"/><section className="relative my-8 w-full max-w-2xl rounded-[2rem] bg-white p-6 shadow-2xl dark:bg-slate-900 sm:p-8"><button onClick={onClose} className="absolute right-5 top-5 grid size-9 place-items-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800"><X size={18}/></button><h2 className="pr-12 text-2xl font-black">{title}</h2><p className="mb-7 mt-1 text-sm text-slate-500">{subtitle}</p>{children}</section></div>;
}
