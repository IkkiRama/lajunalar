import { useEffect, useState } from 'react';
import { Link, NavLink } from 'react-router-dom';
import { BrainCircuit, LayoutDashboard, LibraryBig, LogOut, Moon, ShieldCheck, Sun } from 'lucide-react';
import { useMeQuery } from '../store/api';
import { LogoutConfirmModal } from './LogoutConfirmModal';

export function Brand({ light = false }) {
    return <Link to="/" className={`flex items-center gap-2 font-extrabold tracking-tight ${light ? 'text-white' : 'text-slate-950 dark:text-white'}`}>
        <span className="grid size-10 place-items-center rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20"><BrainCircuit size={23}/></span>
        <span className="text-xl">Laju<span className="text-indigo-600 dark:text-indigo-400">Nalar</span></span>
    </Link>;
}

export function Header({ minimal = false }) {
    const { data: user } = useMeQuery();
    const [dark, setDark] = useState(() => localStorage.getItem('theme') === 'dark');
    const [logoutOpen, setLogoutOpen] = useState(false);
    useEffect(() => {
        document.documentElement.classList.toggle('dark', dark);
        localStorage.setItem('theme', dark ? 'dark' : 'light');
    }, [dark]);

    return <><header className="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90">
        <div className="mx-auto flex h-18 max-w-7xl items-center justify-between px-5 lg:px-8">
            <Brand/>
            <div className="flex items-center gap-2">
                {!minimal && user && <nav className="mr-2 hidden items-center gap-1 md:flex">
                    <NavLink to="/dashboard" className={({isActive}) => `flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold ${isActive ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-300'}`}><LayoutDashboard size={17}/>Dashboard</NavLink>
                    <NavLink to="/paket" className={({isActive}) => `flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold ${isActive ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-300'}`}><LibraryBig size={17}/>Paket</NavLink>
                    {user.role === 'admin' && <NavLink to="/kelola" className={({isActive}) => `flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold ${isActive ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-300'}`}><ShieldCheck size={17}/>Admin</NavLink>}
                </nav>}
                <button onClick={() => setDark(!dark)} aria-label="Ganti tema" className="grid size-10 place-items-center rounded-xl border border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-200">{dark ? <Sun size={19}/> : <Moon size={19}/>}</button>
                {!minimal && user && <button onClick={() => setLogoutOpen(true)} aria-label="Keluar" className="grid size-10 place-items-center rounded-xl border border-slate-200 text-rose-600 dark:border-slate-700"><LogOut size={18}/></button>}
            </div>
        </div>
    </header><LogoutConfirmModal open={logoutOpen} onClose={() => setLogoutOpen(false)}/></>;
}

export function Footer() {
    return <footer className="border-t border-slate-200 bg-white py-8 dark:border-slate-800 dark:bg-slate-950">
        <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-5 text-sm text-slate-500 sm:flex-row lg:px-8">
            <span>© {new Date().getFullYear()} LajuNalar · Platform latihan independen.</span>
            <span>Dibuat dengan <span className="text-rose-500">♥</span> oleh Rifki Romadhan · <a className="font-semibold text-indigo-600 hover:underline" href="https://instagram.com/george_ikki" target="_blank" rel="noreferrer">@george_ikki</a></span>
        </div>
    </footer>;
}

export function PageLoader({ label = 'Menyiapkan ruang latihan…' }) {
    return <div className="grid min-h-[50vh] place-items-center"><div className="text-center"><div className="mx-auto mb-4 size-10 animate-spin rounded-full border-4 border-indigo-100 border-t-indigo-600"/><p className="text-sm font-medium text-slate-500">{label}</p></div></div>;
}

export function ErrorState({ title = 'Ada yang belum beres', message = 'Coba muat ulang halaman ini.' }) {
    return <div className="card mx-auto max-w-lg p-8 text-center"><h2 className="text-xl font-bold">{title}</h2><p className="mt-2 text-slate-500">{message}</p><button onClick={() => window.location.reload()} className="btn-primary mt-5">Muat ulang</button></div>;
}

export function Protected({ children }) {
    const { data, isLoading, isError } = useMeQuery();
    if (isLoading) return <PageLoader label="Memeriksa akun Google…"/>;
    if (isError || !data) return <div className="min-h-screen bg-slate-50 dark:bg-slate-950"><Header minimal/><main className="mx-auto max-w-xl px-5 py-24"><div className="card p-8 text-center"><BrainCircuit className="mx-auto text-indigo-600" size={44}/><h1 className="mt-4 text-2xl font-bold">Masuk untuk mulai latihan</h1><p className="mt-2 text-slate-500">Akun Google menjaga proses masuk tetap ringkas tanpa formulir password.</p><a href="/auth/google/redirect" className="btn-primary mt-6">Lanjutkan dengan Google</a></div></main></div>;
    return children;
}

export function AppShell({ children, footer = true }) {
    return <div className="min-h-screen"><Header/><main>{children}</main>{footer && <Footer/>}</div>;
}

export const formatDate = (value) => value ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '-';
export const typeTone = { number: 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300', letter: 'bg-violet-50 text-violet-700 dark:bg-violet-950/40 dark:text-violet-300', mixed: 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' };
