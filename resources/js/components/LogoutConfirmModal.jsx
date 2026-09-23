import { LogOut, X } from 'lucide-react';
import { useEffect, useState } from 'react';

export function LogoutConfirmModal({ open, onClose }) {
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState('');

    useEffect(() => {
        if (!open) setError('');
    }, [open]);

    useEffect(() => {
        if (!open) return;
        const closeOnEscape = (event) => {
            if (event.key === 'Escape' && !busy) onClose();
        };
        window.addEventListener('keydown', closeOnEscape);
        return () => window.removeEventListener('keydown', closeOnEscape);
    }, [open, busy, onClose]);

    if (!open) return null;

    async function confirmLogout() {
        setBusy(true);
        setError('');

        try {
            const response = await fetch('/logout', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
            });

            if (!response.ok) throw new Error('Logout gagal');
            window.location.assign('/');
        } catch {
            setError('Belum berhasil keluar. Periksa koneksi lalu coba lagi.');
            setBusy(false);
        }
    }

    return <div className="fixed inset-0 z-[100] grid place-items-center bg-slate-950/60 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="logout-title">
        <button disabled={busy} aria-label="Batal keluar" onClick={onClose} className="absolute inset-0 cursor-default"/>
        <section className="card relative w-full max-w-md p-6 shadow-2xl sm:p-7">
            <button disabled={busy} onClick={onClose} aria-label="Tutup modal" className="absolute right-5 top-5 grid size-9 place-items-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800"><X size={18}/></button>
            <span className="grid size-12 place-items-center rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-300"><LogOut size={23}/></span>
            <h2 id="logout-title" className="mt-5 pr-10 text-2xl font-black">Keluar dari LajuNalar?</h2>
            <p className="mt-2 text-sm leading-6 text-slate-500">Sesi akunmu pada perangkat ini akan diakhiri. Kamu perlu masuk kembali dengan Google untuk melanjutkan.</p>
            {error && <p className="mt-4 rounded-xl bg-rose-50 p-3 text-sm font-semibold text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">{error}</p>}
            <div className="mt-7 flex justify-end gap-3 border-t border-slate-100 pt-5 dark:border-slate-800">
                <button autoFocus disabled={busy} onClick={onClose} className="btn-secondary">Batal</button>
                <button disabled={busy} onClick={confirmLogout} className="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-5 py-3 font-semibold text-white shadow-lg shadow-rose-600/20 transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"><LogOut size={18}/>{busy ? 'Sedang keluar…' : 'Ya, keluar'}</button>
            </div>
        </section>
    </div>;
}
